# Feature 07: Transport & Fleet

> **Source studied:** `FleetRequest.php` model (78 lines), `FleetVehicle.php` model (108 lines), `FleetVehicleAssignment.php`, `FleetVehicleReturn.php`, `FleetVehicleReceipt.php`, `FleetVehicleAudit.php`, `FleetRequestStep.php`, `FleetRequestFulfillment.php`, fleet-related routes, `NotificationService.php` (8 fleet notifications)

---

## 1. Feature Overview

The **Transport & Fleet** module handles vehicle management for NCS. Access and permissions perfectly mirror the web backend:
- **`T&L Officer` / `O/C T&L` (Command-Level Originators)**: They manage the fleet lifecycle at a specific command. They can view assigned command vehicles and **submit** fleet requests for their command.
- **`Staff Officer T&L` / `CC T&L` (Central Approvers)**: They act as reviewers and approvers on pending fleet requests. `CC T&L` holds headquarters-level global clearance to manipulate master inventory records.
- **`Regular Officers`**: Can only **view vehicles directly assigned** to themselves.

There are **4 request types**:
1. **New Vehicle** — Request a brand new vehicle for the command
2. **Re-allocation** — Transfer a vehicle between commands
3. **Requisition** — Request an existing vehicle from the fleet pool
4. **Repair** — Request vehicle repair

---

## 2. Data Models

### `fleet_requests` Table
```
┌───────────────────────┬────────────┬──────────────────────────────┐
│ Column                │ Type       │ Notes                        │
├───────────────────────┼────────────┼──────────────────────────────┤
│ id                    │ bigint PK  │                              │
│ request_type          │ string     │ new_vehicle/reallocation/    │
│                       │            │ requisition/repair           │
│ status                │ string     │ Step-based workflow          │
│ origin_command_id     │ bigint FK  │ Requesting command           │
│ target_command_id     │ bigint FK  │ Destination command          │
│ requested_vehicle_type│ string     │ Sedan/SUV/Bus/Pickup etc.    │
│ requested_make        │ string     │ Toyota/Hilux etc.            │
│ requested_model       │ string     │ Model name                   │
│ requested_year        │ integer    │ Year of manufacture          │
│ requested_quantity    │ integer    │ How many vehicles             │
│ amount                │ decimal    │ Budget amount (for new/repair)│
│ fleet_vehicle_id      │ bigint FK  │ Specific vehicle (if repair) │
│ document_path         │ string     │ Supporting document           │
│ notes                 │ text       │ Additional notes              │
│ current_step_order    │ integer    │ Current workflow step         │
│ created_by            │ bigint FK  │ → users.id                   │
│ submitted_at          │ timestamp  │                              │
└───────────────────────┴────────────┴──────────────────────────────┘
```

### `fleet_vehicles` Table
```
┌────────────────────────┬────────────┬─────────────────────────────┐
│ Column                 │ Type       │ Notes                       │
├────────────────────────┼────────────┼─────────────────────────────┤
│ id                     │ bigint PK  │                             │
│ vehicle_model_id       │ bigint FK  │ → fleet_vehicle_models.id   │
│ make                   │ string     │ Vehicle manufacturer        │
│ model                  │ string     │ Vehicle model               │
│ year_of_manufacture    │ integer    │                             │
│ vehicle_type           │ string     │ Sedan/SUV/Bus/Pickup        │
│ reg_no                 │ string     │ Registration number         │
│ chassis_number         │ string     │                             │
│ engine_number          │ string     │                             │
│ service_status         │ string     │ active/maintenance/retired  │
│ lifecycle_status       │ string     │ new/assigned/returned       │
│ current_command_id     │ bigint FK  │ Where vehicle is currently  │
│ current_officer_id     │ bigint FK  │ Who has it now              │
└────────────────────────┴────────────┴─────────────────────────────┘
```

---

## 3. Workflow — Step-Based Approval

Fleet requests use a **dynamic step-based workflow** via `fleet_request_steps`:

```
┌─────────────┐     ┌─────────────┐     ┌────────────────┐     ┌──────────────┐
│ T&L Officer │────▶│ CGC Reviews │────▶│ DCG FATS       │────▶│ ACG TS       │
│ Submits     │     │ (Step 1)    │     │ Recommends     │     │ Approves     │
│             │     │             │     │ (Step 2)       │     │ (Step 3)     │
└─────────────┘     └─────────────┘     └────────────────┘     └──────────────┘
```

Each request type may have different step configurations:
- **New Vehicle**: T&L Officer → CGC → DCG FATS → ACG TS (budget approval)
- **Reallocation**: T&L Officer → CGC → DCG FATS
- **Requisition**: T&L Officer → CGC
- **Repair**: T&L Officer → CGC → Workshop assignment

---

## 4. API Endpoints

```
# Officer (view only)
GET    /api/v1/fleet/my-vehicles                   → Vehicles assigned to me

# T&L Officer
GET    /api/v1/fleet/requests                       → List fleet requests (by command)
POST   /api/v1/fleet/requests                       → Create fleet request
GET    /api/v1/fleet/requests/{id}                  → Request detail + steps
GET    /api/v1/fleet/vehicles                       → List vehicles in command
GET    /api/v1/fleet/vehicles/{id}                  → Vehicle detail

# Approvers (CGC, DCG FATS, ACG TS)
GET    /api/v1/fleet/pending-approvals              → Requests pending my approval
POST   /api/v1/fleet/requests/{id}/approve          → Approve current step
POST   /api/v1/fleet/requests/{id}/reject           → Reject with reason

# Vehicle operations
POST   /api/v1/fleet/vehicles/{id}/assign           → Assign to officer
POST   /api/v1/fleet/vehicles/{id}/return           → Return vehicle
POST   /api/v1/fleet/vehicles/{id}/audit            → Record audit
```

---

## 5. Notifications (8 Types)

| Event | Method | Recipients |
|-------|--------|-----------|
| Fleet request submitted | `notifyFleetRequestSubmitted()` | CGC |
| Fleet request approved (step) | `notifyFleetRequestStepApproved()` | Creator + next approver |
| Fleet request rejected | `notifyFleetRequestRejected()` | Creator |
| Fleet request fulfilled | `notifyFleetRequestFulfilled()` | Creator |
| Vehicle assigned | `notifyFleetVehicleAssigned()` | Officer |
| Vehicle returned | `notifyFleetVehicleReturned()` | T&L Officer |
| Vehicle maintenance due | `notifyFleetVehicleMaintenanceDue()` | T&L Officer |
| Workshop assignment | `notifyFleetWorkshopAssignment()` | Workshop team |

---

## 6. Mobile Screens

- **My Vehicles** — Officer views assigned vehicles
- **Fleet Dashboard** — T&L Officer overview (vehicle counts, pending requests)
- **Create Request** — Form with request type selector
- **Request Detail** — Step-based approval timeline
- **Vehicle Detail** — Full vehicle info + history
- **Approve/Reject** — Approver action sheet

---

## 7. Dashboard & Notification Integration (UI/UX Flow)

To match the site concept and premium UI patterns previously established, the Transport module tightly integrates with the user's dashboard and notification system.

### Role-Based Dashboard Quick Actions
- **Regular Officers**: A sleek "My Vehicle" quick-glance card on the main dashboard showing current assigned vehicle, plate number, and maintenance status. Tap to view full details.
- **T&L Officers**: A "Fleet Management" quick-action widget to instantly "Request New Vehicle" or "Log Maintenance" directly from the home dashboard.
- **Approvers (CGC, DCG FATS, ACG TS)**: A high-priority **"Pending Approvals"** badge/card on their dashboard. They can tap to perform **Quick Actions (Approve/Reject)** directly from the dashboard without navigating deep into the app.

### Actionable Notifications (Deep Linking)
- Notifications are tied directly to action requirements. When a fleet request needs approval (e.g., `notifyFleetRequestSubmitted`), the push notification will **navigate directly to the Request Detail Screen** with the Approve/Reject bottom sheet ready.
- When a vehicle is assigned or returned, tapping the notification opens the relevant **Vehicle Detail Screen** instantly.
- All actions taken via notifications or quick-actions are instantly logged to `fleet_vehicle_audits` and request steps for a perfect paper trail.
- Seamless and efficient transitions ensure officers spend minimal time navigating menus.

---

## 8. React Native Structure

```
src/features/transport/
├── screens/
│   ├── MyVehiclesScreen.tsx
│   ├── FleetDashboardScreen.tsx
│   ├── CreateRequestScreen.tsx
│   ├── RequestDetailScreen.tsx
│   ├── VehicleDetailScreen.tsx
│   └── PendingApprovalsScreen.tsx
├── components/
│   ├── VehicleCard.tsx
│   ├── RequestTypeSelector.tsx
│   ├── StepTimeline.tsx
│   └── ApprovalActionSheet.tsx
├── api/
│   └── fleetApi.ts
└── types/
    └── fleet.ts
```

---

## 9. Testing Checklist

- [ ] Officer views assigned vehicles
- [ ] T&L Officer creates New Vehicle request
- [ ] T&L Officer creates Reallocation request
- [ ] T&L Officer creates Repair request
- [ ] Step-based approval flow works correctly
- [ ] CGC approves/rejects step
- [ ] DCG FATS approves/rejects step
- [ ] ACG TS final approval
- [ ] Vehicle assigned to officer → notification
- [ ] Vehicle returned → status updated
- [ ] Notifications at each approval step

---

## 10. User Guide (Fleet & Transport)

### For Regular Officers
1. **Dashboard Overview**: Open the app and view your home dashboard. If a vehicle is officially assigned to you, you will see a "**My Vehicle**" card right on the dashboard showing the Make, Model, and Registration Number.
2. **Vehicle Details**: Tap the "My Vehicle" card to view full details including Engine Number, Chassis Number, and real-time Service Status (e.g., Active, Maintenance).
3. **Notifications**: You will receive instant push notifications if a new vehicle is assigned to you or if your vehicle is recalled/returned.

### For T&L Officers and O/C T&L (Command-Level Originators)
1. **Fleet Hub**: Tap the "**Fleet**" shortcut on your dashboard grid to open the **Fleet Dashboard**. 
2. **Metrics & My Requests**: View high-level metrics of all command vehicles (Total, Active, Repair) at the top. You will see a list of your submitted requests ("My Requests").
3. **Creating Requests**: Tap the `+` action button at the bottom right of the Fleet Dashboard to create a new request on behalf of your command.
    - **Step 1**: Select the Request Type (`New Vehicle`, `Re-Allocation`, `Requisition`, `Repair`).
    - **Step 2**: Fill in the vehicle details, required quantities, estimated budget, and any supporting justification/notes.
    - **Submit**: Once submitted, it automatically routes to the central HQ approvers.

### For Approvers (Staff Officer T&L, CC T&L, CGC, DCG FATS, ACG TS)
1. **Pending Approvals**: Your dashboard immediately flags if you have pending requests with a red alert badge on the "**Pending Fleet Approvals**" widget.
2. **Actionable Alerts**: If you receive a push notification for a Fleet Request, tapping it instantly opens the specific request, bypassing manual navigation.
3. **Review & Act**: 
    - Open any pending request to view the full details and the complete **Action Timeline** of who has approved it so far.
    - At the bottom of the screen, you will see a **"YOUR ACTION REQUIRED"** section. 
    - Enter your remarks/justification and tap **Approve** or **Reject**. The system instantly records your decision, updates the timeline, and notifies the next actor or the creator.
