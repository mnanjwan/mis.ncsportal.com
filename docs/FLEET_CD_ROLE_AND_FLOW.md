# Fleet: CD Role and Flow

## Overview

The **CD (Chief Driver)** role manages vehicle requests and fleet operations at the **command level**. CD users create and submit requests for their command, track them through the workflow, and manage vehicles (issue, return, service status) for their command. CD is **not** a workflow step—they do not approve or forward requests; other roles do that.

---

## Direct flow (CD) — plain steps (full test coverage)

1. **Log in** as Chief Driver (your user must be assigned to a command).
2. **Dashboard:** Open the Fleet dashboard. You see counts: my drafts, submitted, KIV, released, command pool vehicles, active assignments, pending returns. Links: Create Request, View Requests, Command Vehicles, Returns Report, **Roster Approvals**, **Internal Staff Orders**. **Inbox** has no items (expected for CD).
3. **Create a request:** Go to **Create Request**. You can create any of the six types: **New Vehicle**, **Re-allocation**, **OPE**, **Repair**, **Request for Use**, **Maintenance Requisition**. Fill the form, then **Save Draft**. The request appears in **My Requests** as a draft.
4. **Submit:** In **My Requests**, open your draft, then **Submit Request**. The request goes to other roles for approval (CD does not approve requests).
5. **View status:** In **My Requests**, click **View** on any request (draft, submitted, KIV, or released). You see the workflow steps and who acted; you have no action buttons. Drafts show **Submit**; others show **View** only.
6. **Vehicles:** Go to **Command Vehicles** (only your command’s vehicles). Open a vehicle. You can **update service status**, **Issue** to an officer, or **Process Return** when an officer returns it. Where applicable, you can edit vehicle identifiers.
7. **Returns report:** Open **Returns Report**. It shows returns for your command.
8. **Serviceability report:** Open **Serviceability Report** (if linked from dashboard). It shows serviceability for your command.
9. **Roster approvals:** Go to **Roster Approvals** (Fleet). You see rosters that need your approval (rosters involving Transport officers). Open one, then **Approve** or reject. Only rosters for your command.
10. **Internal staff orders (Transport):** Go to **Internal Staff Orders**. Create an internal posting for a **Transport** officer (select officer, target unit, target role). Submit for DC Admin approval. Later, DC Admin approves or rejects; you can view status. This is only for Transport officers in your command; Staff Officer handles other units.
11. **Notifications:** Check the notifications bell. You get notified when a request you created is **released** or **rejected**, and when vehicles in your command are updated, issued, or returned.

**Test checklist:** Login → Dashboard (all cards and links) → Create draft (try at least one request type) → Submit → View request (no action panel) → Vehicles (list, service status, issue, return) → Returns report → Serviceability report → Roster approvals (list and approve if any) → Internal staff orders (create if needed) → Notifications. Confirm Inbox is empty and all data is for your command only.

---

## Gap vs described “Requisition for Vehicle from CD” process

Compared with the process you described (CD → Head of Unit → CGC → … → CC T&L → release → Unit Head receives), the following apply **from the CD’s point of view**:

### What CD cannot do in the system (functionality not there or different)

1. **“Send request through the Head of Unit”**  
   There is **no Head of Unit step** in the workflow. CD creates and submits the request **directly**; it does not go to a “Head of Unit” in the system. So CD cannot route the request via Head of Unit in the app.  
   **Workaround:** Head of Unit approval can be done offline; once approved, CD submits in the system.

2. **Request path order**  
   Your process: CD → Unit Head → CGC → DCG FATS → ACG TS → CC T&L (then inventory check, then approval back down).  
   System: CD submits → **CC T&L first** (inventory check/propose) → CGC → DCG FATS → ACG TS → CC T&L (release). So the **order is different**: the system sends the request to CC T&L first, then up the chain, then back to CC T&L. CD cannot change this; it is fixed in the workflow.

3. **“Unit Head receives” the vehicle**  
   In the system, **receipt is done by Area Controller** (Unit Head), not by CD. So CD **cannot** perform “receive” — and that matches your process (Unit Head receives). No missing CD action here.

### What CD can do (and that matches the process)

- Create and submit the vehicle requisition (all six request types, including New Vehicle).
- See status: draft, submitted, KIV, released.
- Get notified when the request is released or rejected.
- Manage vehicles at command level (issue, return, service status) **after** they are at the command; CD does not “receive” from HQ — that is Area Controller (Unit Head).

### Summary (after update)

- **Implemented:** Request now goes **through** Area Controller (Head of Unit) at Step 1; CD submits, then Area Controller forwards.
- **Implemented:** Workflow order matches: HoU → CGC → DCG FATS → ACG TS → CC T&L (propose) → back up → CGC approve → CC T&L (release).
- **By design:** CD does not receive the vehicle; Unit Head (Area Controller) receives.

---

## New Vehicle workflow (implemented)

The system has been updated to match the described process. **Head of Unit** is mapped to **Area Controller** (same role that receives the vehicle at command level).

### Implemented New Vehicle flow (9 steps)

| Step | Role             | Action   | Purpose                              |
|------|------------------|----------|--------------------------------------|
| 1    | Area Controller  | FORWARD  | Head of Unit; CD sends through HoU  |
| 2    | CGC              | FORWARD  | To DCG FATS                         |
| 3    | DCG FATS         | FORWARD  | To ACG TS                            |
| 4    | ACG TS           | FORWARD  | To CC T&L                            |
| 5    | CC T&L           | REVIEW   | Inventory check; propose or KIV      |
| 6    | ACG TS           | FORWARD  | Send back up for approval            |
| 7    | DCG FATS         | FORWARD  | To CGC                               |
| 8    | CGC              | APPROVE  | Approve for release                  |
| 9    | CC T&L           | REVIEW   | Release to Unit Head (Area Controller receives) |

### Notifications (dashboard, email, notification panel)

All fleet notifications are sent so they land on **every relevant user’s**:

- **In-app notification panel** (bell icon)
- **Email** (when the user has an email and email is enabled)
- **Fleet dashboard / Requests Inbox** (requests at the user’s current step appear in Fleet → Requests → Inbox)

Who gets notified:

- **On submit:** Creator (submitted); users at **Step 1 (Area Controller)** for the origin command (awaiting action).
- **On each step action:** Users at the **next step** (awaiting action).
- **On REJECTED:** Creator.
- **On RELEASED:** Creator; **CD users** at the origin command.
- **On KIV:** Creator; **CD users** at the origin command.

All of these use the same notification mechanism: create in-app notification and send email (`sendEmail = true`), so they appear in the notification panel and in email as expected.

---

## CD Access

| Function | Route | Method |
|----------|--------|--------|
| Fleet Dashboard | `/fleet/dashboard/cd` | GET |
| Create Request | `/fleet/requests/create` | GET |
| Store Request (Save Draft) | `/fleet/requests` | POST |
| View Requests | `/fleet/requests` | GET |
| View Request Details | `/fleet/requests/{id}` | GET |
| Submit Request | `/fleet/requests/{id}/submit` | POST |
| View Vehicles | `/fleet/vehicles` | GET |
| View Vehicle Details | `/fleet/vehicles/{id}` | GET |
| Update Vehicle Service Status | `/fleet/vehicles/{id}/service-status` | PUT |
| Issue Vehicle to Officer | `/fleet/vehicles/{id}/issue` | GET / POST |
| Process Vehicle Return | `/fleet/vehicles/{id}/return` | GET / POST |
| Returns Report | `/fleet/reports/returns` | GET |
| Serviceability Report | `/fleet/reports/serviceability` | GET |
| Roster Approvals (Transport officers) | `/fleet/roster/cd` | GET |
| Internal Staff Orders (Transport) | `/fleet/internal-staff-orders` | GET / POST |

---

## Internal Staff Orders (Transport)

CD handles **internal posting** for Transport officers within the command, in the same model as Staff Officer does for normal officers:

- **Staff Officer:** Creates internal staff orders for officers where unit ≠ Transport (Revenue, Admin, SS, etc.).
- **CD:** Creates internal staff orders for officers where unit = Transport.

Flow is identical:
1. Create order (select Transport officer, target unit, target role).
2. Submit for DC Admin approval.
3. DC Admin approves or rejects.
4. On approval, roster assignments are updated.

---

## Dashboard

**Route:** `/fleet/dashboard/cd`

- **Cards:** My Draft Requests, Submitted, KIV, Released, Command Pool Vehicles, Active Assignments, Pending Returns
- **Quick links:** Create Request, View Requests, Command Vehicles, Returns Report, Roster Approvals, Internal Staff Orders (Transport)
- **Requests Inbox:** Empty for CD (no CD step in the workflow)

---

## Requests

### Create Request

- **Route:** `/fleet/requests/create`
- **Request types:** New Vehicle, Re-allocation, OPE, Repair, Request for Use, Maintenance Requisition
- **Actions:** Save as **Draft** (POST to `/fleet/requests`), then **Submit** (POST to `/fleet/requests/{id}/submit`) when ready. Only the creator can submit.

### View Requests

- **Inbox:** Requests awaiting *your* action. For CD this is empty (CD is not a step role).
- **My Requests:** All requests you created (drafts, submitted, KIV, released). Drafts show **Submit**; others show **View**.

---

## Workflow Flows (CD as Creator)

### 1. New Vehicle Request (`FLEET_NEW_VEHICLE`)

1. CD creates → **DRAFT**
2. CD submits → **Step 1:** Area Controller (Head of Unit) forwards
3. **Step 2:** CGC → **Step 3:** DCG FATS → **Step 4:** ACG TS (forward to CC T&L)
4. **Step 5:** CC T&L (inventory check; propose vehicles or KIV)
5. **Step 6:** ACG TS → **Step 7:** DCG FATS → **Step 8:** CGC (approve)
6. **Step 9:** CC T&L releases to command → **RELEASED**
7. Area Controller (Unit Head) receives; CD and origin command notified

### 2. Re-allocation (`FLEET_RE_ALLOCATION`)

1. CD creates → **DRAFT**
2. CD submits → **Step 1:** CC T&L (approve & release) → **RELEASED**
3. CD notified

### 3. Maintenance Requisition (`FLEET_REQUISITION`)

1. CD (or OC Workshop) creates → **DRAFT**
2. Submit → **Step 1:** ACG TS (≤₦300k: approve and done; >₦300k: forward)
3. **Step 2:** DCG FATS (≤₦500k: approve; >₦500k: forward)
4. **Step 3:** CGC (approve) → **RELEASED**
5. CD notified

### 4. Repair / OPE / Use (`FLEET_REPAIR`, `FLEET_OPE`, `FLEET_USE`)

1. CD (or Staff Officer T&L) creates → **DRAFT**
2. Submit → **Step 1:** Staff Officer T&L (approve) → **RELEASED**
3. CD notified

---

## Vehicle Management (CD)

- **View vehicles:** Command-scoped (vehicles for your command).
- **Update service status:** Only CD can update service/maintenance status for vehicles in their command.
- **Issue vehicle:** From command pool to an officer (creates assignment).
- **Process return:** When an officer returns a vehicle; CD processes the return for their command.

---

## Notifications (CD)

CD receives notifications when:

- A request they created is **rejected** or **released**
- A request from their **origin command** is **released**
- Vehicle **identifiers** or **service status** are updated (for their command)
- A vehicle is **issued** or **returned** (for their command)

---

## Summary

| Aspect | CD |
|--------|----|
| **Level** | Command (one command) |
| **Workflow step?** | No |
| **Creates requests** | Yes (all 6 types) |
| **Submits requests** | Yes (creator only) |
| **Acts on steps** | No |
| **Vehicle operations** | Issue, return, update service status (command-scoped) |
| **Reports** | Returns Report, Serviceability Report |
| **Roster approvals** | For rosters with Transport officers |
| **Internal posting** | Transport officers only (Staff Officer does non-Transport) |
