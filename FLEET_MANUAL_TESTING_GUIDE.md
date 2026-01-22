# Fleet Module - Manual Testing Guide

## Quick Setup

### 1. Create Test Users with Fleet Roles

**Via Database Seeder (Recommended):**
```bash
php artisan db:seed --class=CompleteSystemSeeder
```

**Or Manually via UI:**
1. Login as **HRD** user
2. Go to **User Management → Role Assignments**
3. Create users and assign Fleet roles:

| Role | Email Example | Command Assignment |
|------|---------------|---------------------|
| CD | `cd@ncs.gov.ng` | Assign to a Command (e.g., "APAPA") |
| O/C T&L | `octl@ncs.gov.ng` | Assign to same Command |
| Transport Store/Receiver | `store@ncs.gov.ng` | Assign to same Command |
| Area Controller | `area@ncs.gov.ng` | Assign to same Command |
| CC T&L | `cctl@ncs.gov.ng` | System-wide (no command) |
| DCG FATS | `dcgfats@ncs.gov.ng` | System-wide |
| ACG TS | `acgts@ncs.gov.ng` | System-wide |
| CGC | `cgc@ncs.gov.ng` | System-wide |

---

## Test Flow 1: CD Requisition Workflow (Complete End-to-End)

### Step 1: CD Creates a Requisition

**Login as:** `cd@ncs.gov.ng` (CD role)

1. Navigate to **Fleet → Requests**
2. Click **"Create Request"** or go to `/fleet/requests/create`
3. Fill the form:
   - **Vehicle Type:** SALOON (or SUV, BUS)
   - **Make:** Toyota (optional)
   - **Model:** Corolla (optional)
   - **Year:** 2020 (optional)
   - **Quantity:** 2
4. Click **"Save as Draft"**

**Expected:** Request saved with status **DRAFT**. You see it in "My Requests" list.

---

### Step 2: CD Submits the Requisition

**Still logged in as:** CD

1. Go to **Fleet → Requests**
2. Find your draft request and click **"View"**
3. Click **"Submit Request"**

**Expected:** 
- Status changes to **SUBMITTED**
- Request moves to **Step 1** (Area Controller)
- Request appears in **Area Controller's inbox**

---

### Step 3: Area Controller Forwards

**Login as:** `area@ncs.gov.ng` (Area Controller role)

1. Go to **Fleet → Requests**
2. Check **"Inbox"** tab - you should see the request
3. Click the request to view details
4. Click **"Forward"** (or action button)

**Expected:**
- Request moves to **Step 2** (CGC)
- Status: **IN_REVIEW**

---

### Step 4: CGC Forwards

**Login as:** `cgc@ncs.gov.ng` (CGC role)

1. Go to **Fleet → Requests** (or your dashboard)
2. Check **"Inbox"** - request should be there
3. Click request → **"Forward"**

**Expected:** Moves to **Step 3** (DCG FATS)

---

### Step 5: DCG FATS Forwards

**Login as:** `dcgfats@ncs.gov.ng` (DCG FATS role)

1. Go to **Fleet → Requests**
2. Inbox → Click request → **"Forward"**

**Expected:** Moves to **Step 4** (ACG TS)

---

### Step 6: ACG TS Forwards

**Login as:** `acgts@ncs.gov.ng` (ACG TS role)

1. Go to **Fleet → Requests**
2. Inbox → Click request → **"Forward"**

**Expected:** Moves to **Step 5** (CC T&L - Inventory Check)

---

### Step 7: CC T&L Checks Inventory & Proposes Vehicles

**Login as:** `cctl@ncs.gov.ng` (CC T&L role)

1. Go to **Fleet → Requests**
2. Inbox → Click the request
3. You should see:
   - Request details
   - **"Available Vehicles"** section (matching your criteria)
   - List of vehicles in stock

**Scenario A: Vehicles Available**
1. Select vehicles from the list (checkboxes)
2. Click **"Propose & Reserve"**
3. Add optional comment: "2 Toyota Corolla available"

**Expected:**
- Selected vehicles are **reserved** (marked as reserved)
- Request moves to **Step 6** (back to ACG TS for approval)
- Status: **PENDING_CGC_APPROVAL**

**Scenario B: No Vehicles Available (KIV)**
1. Don't select any vehicles
2. Click **"Mark as KIV"** (or leave empty and propose)

**Expected:**
- Status: **KIV** (Keep In View)
- Request **stays at Step 5** (doesn't advance)
- Vehicles remain unreserved
- Request can be resumed later when vehicles become available

---

### Step 8: Approval Chain (Back Up)

**Login as:** `acgts@ncs.gov.ng` (ACG TS)

1. Inbox → Request → **"Forward"**

**Expected:** Moves to **Step 7** (DCG FATS)

---

**Login as:** `dcgfats@ncs.gov.ng` (DCG FATS)

1. Inbox → Request → **"Forward"**

**Expected:** Moves to **Step 8** (CGC - Final Approver)

---

**Login as:** `cgc@ncs.gov.ng` (CGC)

1. Inbox → Request → Click request
2. Click **"Approve"** (or **"Reject"** if needed)

**Expected:**
- If **Approve:** Status → **APPROVED**, moves to **Step 9**
- If **Reject:** Status → **REJECTED**, workflow stops

---

### Step 9: Release Chain (Back Down)

**Login as:** `dcgfats@ncs.gov.ng` (DCG FATS)

1. Inbox → Request → **"Forward"**

**Expected:** Moves to **Step 10** (ACG TS)

---

**Login as:** `acgts@ncs.gov.ng` (ACG TS)

1. Inbox → Request → **"Forward"**

**Expected:** Moves to **Step 11** (CC T&L - Release Step)

---

### Step 10: CC T&L Releases Vehicles

**Login as:** `cctl@ncs.gov.ng` (CC T&L)

1. Inbox → Request → Click request
2. Click **"Release Vehicles"**
3. Add comment: "Released to APAPA Command"

**Expected:**
- Reserved vehicles are **released** to the requesting command
- Vehicles status: **AT_COMMAND_POOL**
- Vehicles `current_command_id` = requesting command
- Request status: **RELEASED**
- Request workflow complete

---

### Step 11: Area Controller Receives Vehicles

**Login as:** `area@ncs.gov.ng` (Area Controller)

1. Go to **Fleet → Vehicles**
2. Filter by your command
3. You should see the released vehicles in the command pool
4. Click a vehicle → **"Acknowledge Receipt"** (if available)

**Expected:** Vehicle receipt is acknowledged

---

### Step 12: CD Issues Vehicle to Officer

**Login as:** `cd@ncs.gov.ng` (CD)

1. Go to **Fleet → Vehicles**
2. Find a vehicle in **AT_COMMAND_POOL** status
3. Click vehicle → **"Issue to Officer"**
4. Select an officer from dropdown
5. Add notes (optional)
6. Click **"Issue"**

**Expected:**
- Vehicle status: **ASSIGNED_TO_OFFICER**
- Vehicle `current_officer_id` = selected officer
- Assignment record created

---

### Step 13: Officer Returns Vehicle

**Login as:** Officer who received the vehicle

1. Go to **Fleet → Vehicles** (or Officer dashboard if available)
2. Find your assigned vehicle
3. Click **"Return Vehicle"**
4. Add return notes (optional)
5. Click **"Return"**

**Expected:**
- Vehicle status: **AT_COMMAND_POOL**
- Vehicle `current_officer_id` = null
- Return record created

---

## Test Flow 2: Vehicle Intake (New Vehicle Delivery)

### Step 1: Transport Store/Receiver Intakes Vehicle

**Login as:** `store@ncs.gov.ng` (Transport Store/Receiver)

1. Go to **Fleet → Vehicles**
2. Click **"Intake New Vehicle"** or go to `/fleet/vehicles/intake`
3. Fill the form:
   - **Make:** Toyota
   - **Model:** Camry
   - **Vehicle Type:** SALOON
   - **Chassis Number:** CH-12345
   - **Engine Number:** EN-67890
   - **Year of Manufacture:** 2024
   - **Allocation Date:** Today
   - **Area:** Lagos
   - **Service Status:** SERVICEABLE
4. Click **"Save"**

**Expected:**
- Vehicle created with status: **IN_STOCK**
- Vehicle appears in vehicle list
- Available for requisitions

---

## Test Flow 3: Vehicle Identifier Updates (Audit Trail)

### Step 1: CC T&L Updates Reg/Engine Numbers

**Login as:** `cctl@ncs.gov.ng` (CC T&L)

1. Go to **Fleet → Vehicles**
2. Find a vehicle (e.g., one without reg_no)
3. Click vehicle → **"Edit Identifiers"**
4. Update:
   - **Registration Number:** ABC-123-XY
   - **Engine Number:** ENG-99999
5. Click **"Update"**

**Expected:**
- Vehicle reg_no and engine_number updated
- **Audit trail created** in `fleet_vehicle_audits` table
- Audit visible to CC T&L, DCG FATS, ACG TS, CD, O/C T&L

---

## Test Flow 4: Returns Report

### Step 1: CC T&L or CD Views Returns Report

**Login as:** `cctl@ncs.gov.ng` (CC T&L) or `cd@ncs.gov.ng` (CD)

1. Go to **Fleet → Returns Report** or `/fleet/reports/returns`
2. Set date range (default: last 30 days)
3. Click **"Generate Report"**

**Expected:**
- List of vehicle assignments/returns in date range
- Shows vehicle details, command, assignment date
- CD sees only their command's data
- CC T&L sees all commands

---

## Test Flow 5: CD Updates Vehicle Service Status

### Step 1: CD Marks Vehicle as Unserviceable

**Login as:** `cd@ncs.gov.ng` (CD)

1. Go to **Fleet → Vehicles**
2. Find a vehicle in your command
3. Click vehicle → **"Update Service Status"**
4. Change status: **SERVICEABLE** → **UNSERVICEABLE**
5. Add reason: "Engine fault"
6. Click **"Update"**

**Expected:**
- Vehicle `service_status` updated
- Vehicle cannot be issued while unserviceable

---

## Dashboard Access by Role

| Role | Dashboard URL | What You See |
|------|--------------|--------------|
| CD | `/fleet/dashboard/cd` | Command-specific vehicles, requests, stats |
| O/C T&L | `/fleet/dashboard/oc-tl` | Command-specific vehicles, requests |
| Transport Store/Receiver | `/fleet/dashboard/store-receiver` | Command vehicles, intake form |
| CC T&L | `/fleet/dashboard/cc-tl` | All vehicles, all requests, reports |
| DCG FATS | `/fleet/dashboard/dcg-fats` | All requests in approval chain |
| ACG TS | `/fleet/dashboard/acg-ts` | All requests in approval chain |
| Area Controller | `/area-controller/dashboard` | Receipt acknowledgements |

---

## Menu Items by Role

After login, check the sidebar menu. You should see:

**CD, O/C T&L, Transport Store/Receiver:**
- Dashboard
- Vehicles
- Requests
- Returns Report

**CC T&L, DCG FATS, ACG TS:**
- Dashboard
- Vehicles (all commands)
- Requests (all commands)
- Returns Report

---

## Common Issues & Solutions

### Issue: "Request not in my inbox"
**Solution:** 
- Check that your role matches the current step's required role
- Verify role is active: `user_roles.is_active = true`
- For command-level roles, ensure `command_id` matches request's origin command

### Issue: "Cannot create request"
**Solution:**
- Ensure you have **CD** role assigned to a command
- Check `user_roles.command_id` is set for your CD role

### Issue: "No vehicles available in inventory check"
**Solution:**
- First, intake vehicles via Transport Store/Receiver
- Ensure vehicles have `lifecycle_status = IN_STOCK`
- Check vehicle type/make/model matches request criteria

### Issue: "Cannot issue vehicle"
**Solution:**
- Vehicle must be in **AT_COMMAND_POOL** status
- Vehicle must not be reserved (`reserved_fleet_request_id = null`)
- Vehicle must be in your command (for CD)

---

## Quick Test Checklist

- [ ] CD can create draft requisition
- [ ] CD can submit requisition
- [ ] Area Controller sees request in inbox
- [ ] Forwarding chain works (Area → CGC → DCG → ACG → CC T&L)
- [ ] CC T&L can see available vehicles
- [ ] CC T&L can reserve vehicles (or mark KIV)
- [ ] Approval chain works (ACG → DCG → CGC)
- [ ] CGC can approve/reject
- [ ] Release chain works (DCG → ACG → CC T&L)
- [ ] CC T&L can release vehicles
- [ ] Vehicles appear in command pool
- [ ] CD can issue vehicle to officer
- [ ] Officer can return vehicle
- [ ] Transport Store/Receiver can intake new vehicle
- [ ] CC T&L can update vehicle identifiers (audit trail works)
- [ ] CD can update vehicle service status
- [ ] Returns report generates correctly
- [ ] All roles see correct dashboards

---

## Database Verification Queries

Run these in TablePlus/DBngin to verify data:

```sql
-- Check request workflow steps
SELECT fr.id, fr.status, fr.current_step_order, frs.step_order, frs.role_name, frs.decision
FROM fleet_requests fr
LEFT JOIN fleet_request_steps frs ON fr.id = frs.fleet_request_id
ORDER BY fr.id, frs.step_order;

-- Check vehicle reservations
SELECT v.id, v.make, v.model, v.reserved_fleet_request_id, fr.id as request_id
FROM fleet_vehicles v
LEFT JOIN fleet_requests fr ON v.reserved_fleet_request_id = fr.id
WHERE v.reserved_fleet_request_id IS NOT NULL;

-- Check vehicle assignments
SELECT va.*, v.make, v.model, c.name as command_name
FROM fleet_vehicle_assignments va
JOIN fleet_vehicles v ON va.fleet_vehicle_id = v.id
JOIN commands c ON va.assigned_to_command_id = c.id
ORDER BY va.assigned_at DESC;

-- Check audit trail
SELECT * FROM fleet_vehicle_audits
ORDER BY created_at DESC
LIMIT 20;
```

---

## Notes

- **KIV Status:** When CC T&L finds no vehicles, request stays at Step 5. Resume by proposing vehicles later.
- **Partial Fulfillment:** If CC T&L reserves fewer vehicles than requested, request status becomes **PARTIALLY_FULFILLED**.
- **Command Scoping:** CD, O/C T&L, Transport Store/Receiver only see vehicles/requests for their assigned command.
- **System-Wide Roles:** CC T&L, DCG FATS, ACG TS, CGC see all vehicles and requests across all commands.
