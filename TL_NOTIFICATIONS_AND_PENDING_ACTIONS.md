# T&L: All Notifications and Pending-Action Places by Role

## Where things appear

- **Notifications (in-app + email)**: Appear in the **global notifications drawer** (bell/dropdown in the app layout) and, when enabled, as **email**.
- **Pending actions (workflow inbox)**: Appear in **Fleet Dashboard → Requests Inbox** and in **Fleet → Requests → Inbox** table. Each item links to the request detail page (**Open**).
- **My Requests / drafts**: **Fleet → Requests → My Requests** (creator’s requests; drafts show **Submit**).

---

## 1. Notifications that drop (by type and recipient)

| # | Notification type        | Title / trigger                    | Recipients                    | In-app | Email |
|---|---------------------------|------------------------------------|-------------------------------|--------|--------|
| 1 | `fleet_request_pending`   | Fleet Request #X awaiting action   | Users at **current step role** (command-scoped for CD, O/C T&L, Transport Store/Receiver, Area Controller, Staff Officer T&L, OC Workshop) | ✅ | ✅ |
| 2 | `fleet_request_rejected`  | Fleet Request #X Rejected         | **Creator** (who created the request) | ✅ | ✅ |
| 3 | `fleet_request_released`  | Fleet Request #X Released         | **Creator**                   | ✅ | ✅ |
| 4 | `fleet_request_update`    | Fleet Request Released             | **CD(s) at origin command** (when request is RELEASED) | ✅ | ✅ |
| 5 | `fleet_vehicle_received`  | New Vehicle Added to Inventory    | **CC T&L** (all)              | ✅ | ❌ |
| 6 | `fleet_vehicle_update`    | Vehicle identifiers updated       | **CD(s) at vehicle’s command** | ✅ | ✅ |
| 7 | `fleet_vehicle_update`    | Service status updated             | **CD(s) at vehicle’s command** | ✅ | ✅ |
| 8 | `fleet_vehicle_service_status` | Service status updated        | **User who updated** (CD)     | ✅ | ✅ |
| 9 | `fleet_vehicle_issued`    | Vehicle issued                    | **User who issued** + **CD(s) at command** | ✅ | ✅ |
|10 | `fleet_vehicle_returned`  | Vehicle returned                  | **User who processed return** + **CD(s) at command** | ✅ | ✅ |
|11 | `fleet_vehicle_update`    | Vehicle issued / returned         | **CD(s) at command** (from notifyCd) | ✅ | ✅ |

---

## 2. Notifications by role (who gets what)

| Role | Notifications received |
|------|------------------------|
| **CD** | Request released (origin command); vehicle identifiers updated; service status updated; vehicle issued; vehicle returned (all for their command). |
| **Area Controller** | Request pending (only if they were ever made “current step” – not in current workflows); request rejected/released (if creator). |
| **OC Workshop** | Request rejected/released (if creator). |
| **Staff Officer T&L** | Request pending at step 1 (Repair/OPE/Use, same command); request rejected/released (if creator). |
| **CC T&L** | Request pending at step 1 (New Vehicle propose) and step 5 (release); New vehicle added to inventory (in-app only). |
| **ACG TS** | Request pending at step 4 (New Vehicle forward) and step 1 (Requisition). |
| **DCG FATS** | Request pending at step 3 (New Vehicle forward) and step 2 (Requisition). |
| **CGC** | Request pending at step 2 (New Vehicle) and step 3 (Requisition). |
| **O/C T&L** | Request pending only if a workflow step used this role (none in current seed); no other T&L notifications. |
| **Transport Store/Receiver** | No T&L request/vehicle notifications (they do intake; CC T&L is notified for new vehicle). |

---

## 3. Pending actions: where they drop (by role)

### CD

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Fleet Dashboard** (`/fleet/dashboard/cd`) | Banner: Inbox count (0 for CD – no CD step). Cards: My Draft Requests, Submitted, KIV, Released, Command Pool, Active Assignments, Pending Returns. Quick: Create Request, View Requests, Command Vehicles, Returns Report. **Requests Inbox** section: items only if CD were ever current step (currently none). | **View Requests** → Fleet Requests; **Open** on any inbox item → request show. |
| **Fleet → Requests** | **Inbox**: Empty (no CD step). **My Requests**: All requests created by this user (drafts + submitted + KIV + released). | **Submit** on draft; **View** → request show. |
| **Notifications drawer** | Request released (origin command); vehicle identifiers/service status/issued/returned for their command. | Click notification → entity (request/vehicle). |

### Area Controller

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Area Controller dashboard** | (Fleet inbox not on this dashboard by default.) | Use **Fleet** menu → Fleet Requests. |
| **Fleet → Requests** | **Inbox**: Empty (Area Controller is not a step role). **My Requests**: Requests they created. | **Submit** on draft; **View** → request show. |
| **Notifications drawer** | Request rejected/released (if creator). | Click → request. |

### OC Workshop

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Landing** | Goes to **Fleet → Requests** (no fleet dashboard). | **My Requests** for their requisitions. |
| **Fleet → Requests** | **Inbox**: Empty. **My Requests**: Their requisitions (drafts + rest). | **Submit** on draft; **View** → request show. |
| **Notifications drawer** | Request rejected/released (if creator). | Click → request. |

### Staff Officer T&L

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Staff Officer dashboard** | (Fleet inbox may be via Fleet menu.) | **Fleet → Requests** for inbox. |
| **Fleet → Requests** | **Inbox**: Requests at step 1 (Repair/OPE/Use) from their command. **My Requests**: Requests they created. | **Open** on inbox item → request show (action panel); **Submit** on draft. |
| **Notifications drawer** | Request pending at step 1 (same command); rejected/released (if creator). | Click → request. |

### CC T&L

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Fleet Dashboard** (`/fleet/dashboard/cc-tl`) | Banner: Inbox count. Cards: In Stock, Reserved, KIV, Inventory Checks (step 1 New Vehicle), Release Pending (step 5). Quick: Fleet Requests, Fleet Vehicles, Returns Report. **Requests Inbox**: Up to 8 requests at CC T&L step (1 or 5). | **View All** or **Fleet Requests** → full Inbox; **Open** on item → request show (propose or release). |
| **Fleet → Requests** | **Inbox**: All requests at CC T&L current step (propose or release). **My Requests**: If they created any. | **Open** → request show → propose vehicles or release. |
| **Notifications drawer** | Request pending at step 1/5; New vehicle added to inventory. | Click → request or vehicle. |

### ACG TS

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Fleet Dashboard** (`/fleet/dashboard/acg-ts`) | Banner: Inbox count. Cards: Inbox Requests, Pending Approval, KIV. Quick: Fleet Requests. **Requests Inbox**: Requests at ACG TS step (New Vehicle step 4 or Requisition step 1). | **View All** or **Fleet Requests** → Inbox; **Open** → request show (approve/forward/reject/KIV). |
| **Fleet → Requests** | **Inbox**: Requests at ACG TS step. | **Open** → request show. |
| **Notifications drawer** | Request pending at their step. | Click → request. |

### DCG FATS

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Fleet Dashboard** (`/fleet/dashboard/dcg-fats`) | Same shape as ACG TS. **Requests Inbox**: Requests at DCG FATS step (New Vehicle step 3 or Requisition step 2). | **View All** / **Fleet Requests** → **Open** → request show. |
| **Fleet → Requests** | **Inbox**: Requests at DCG FATS step. | **Open** → request show. |
| **Notifications drawer** | Request pending at their step. | Click → request. |

### CGC

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **CGC dashboard** (main) | Fleet inbox may be via Fleet menu/link. | **Fleet → Requests** for inbox. |
| **Fleet → Requests** | **Inbox**: Requests at CGC step (New Vehicle step 2 or Requisition step 3). | **Open** → request show (approve/reject/KIV). |
| **Notifications drawer** | Request pending at CGC step. | Click → request. |

### O/C T&L

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Fleet Dashboard** (`/fleet/dashboard/oc-tl`) | Banner: Inbox count (0 – no O/C T&L step). Cards: In Stock, Command Pool, Reserved, Inbox Requests. Quick: Fleet Requests, Fleet Vehicles. **Requests Inbox**: Empty (no step). | **Fleet Requests** / **Fleet Vehicles** for viewing. |
| **Fleet → Requests** | **Inbox**: Empty. **My Requests**: If they created any. | **View** as needed. |
| **Notifications drawer** | No T&L request notifications in current workflows. | — |

### Transport Store/Receiver

| Place | What shows | Link to handle |
|-------|------------|----------------|
| **Fleet Dashboard** (`/fleet/dashboard/store-receiver`) | Same cards as O/C T&L; Quick includes **Intake Vehicle**. **Requests Inbox**: Empty. | **Fleet Requests**, **Fleet Vehicles**, **Intake Vehicle**. |
| **Fleet → Requests** | **Inbox**: Empty. **My Requests**: If any. | **View**; create via other flows. |
| **Notifications drawer** | No T&L request/vehicle notifications. | — |

---

## 4. Summary table: notifications and pending-action places

| Role              | Notifications (what drops)                    | Pending actions (where they drop)                    |
|-------------------|-----------------------------------------------|--------------------------------------------------------|
| **CD**            | Released (origin); vehicle updates/issued/returned (command) | Dashboard cards + View Requests; My Requests (drafts to submit). |
| **Area Controller** | Rejected/Released (if creator)              | Fleet → Requests → My Requests (drafts to submit).    |
| **OC Workshop**   | Rejected/Released (if creator)                | Fleet → Requests → My Requests (drafts to submit).     |
| **Staff Officer T&L** | Pending step 1 (same command); Rejected/Released (if creator) | Fleet → Requests → Inbox + My Requests.               |
| **CC T&L**        | Pending step 1 & 5; New vehicle in inventory  | Dashboard Requests Inbox + Fleet → Requests → Inbox.  |
| **ACG TS**        | Pending at their step                         | Dashboard Requests Inbox + Fleet → Requests → Inbox.  |
| **DCG FATS**      | Pending at their step                         | Dashboard Requests Inbox + Fleet → Requests → Inbox.  |
| **CGC**           | Pending at their step                         | Fleet → Requests → Inbox.                              |
| **O/C T&L**       | None in current workflows                     | Dashboard counts; Fleet Requests/Vehicles (view).     |
| **Transport Store/Receiver** | None listed above                        | Dashboard + Fleet Requests/Vehicles; Intake Vehicle.  |

---

## 5. Workflow steps (who gets “pending” at which step)

| Request type        | Step 1     | Step 2   | Step 3    | Step 4   | Step 5   |
|---------------------|------------|----------|-----------|----------|----------|
| **New Vehicle**     | CC T&L     | CGC      | DCG FATS  | ACG TS   | CC T&L   |
| **Re-allocation**   | CC T&L     | —        | —         | —        | —        |
| **Requisition**     | ACG TS     | DCG FATS | CGC       | —        | —        |
| **Repair / OPE / Use** | Staff Officer T&L | —    | —         | —        | —        |

Inbox and “Fleet Request #X awaiting action” notifications go to the role(s) for the **current step** above (command-scoped where applicable).
