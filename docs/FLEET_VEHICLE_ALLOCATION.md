# How Vehicles Are Allocated (Fleet)

## Lifecycle statuses

A vehicle moves through these states:

- **IN_STOCK** – In central inventory (not yet allocated to a command).
- **AT_COMMAND_POOL** – Allocated to a command; available for the CD to issue to officers.
- **IN_OFFICER_CUSTODY** (or similar) – Issued to an officer.

---

## 1. Into the system (intake)

- **Who:** **Transport Store/Receiver**
- **Where:** Fleet → Vehicles → Intake (receive new vehicle into inventory)
- **What:** A new vehicle is added to the fleet. It is created with:
  - **lifecycle_status = IN_STOCK**
  - **current_command_id** = receiver’s command (as stored)
- **Result:** Vehicle appears in **central inventory** and can be proposed by CC T&L for New Vehicle requests.

---

## 2. Reserved for a request (proposal)

- **Who:** **CC T&L** at Step 1 (New Vehicle request)
- **Where:** Fleet → Requests → open request → CC T&L proposal panel
- **What:** CC T&L selects vehicles from **IN_STOCK** (and not already reserved). Selected vehicles get:
  - **reserved_fleet_request_id** = that request
  - They stay **IN_STOCK** but are locked for that request
- **Result:** Vehicles are **reserved** for that request until CC T&L releases or the request is rejected/KIV.

---

## 3. Released to a command (allocation to command)

- **Who:** **CC T&L** at the release step (Step 5 for New Vehicle; Step 1 for Re-allocation)
- **Where:** Fleet → Requests → open request → Release action
- **What:**
  - A **FleetVehicleAssignment** is created:
    - **assigned_to_command_id** = request’s **origin_command_id**
    - **released_by_user_id** / **released_at** set
  - Each released vehicle is updated:
    - **current_command_id** = origin command
    - **lifecycle_status = AT_COMMAND_POOL**
    - **reserved_fleet_request_id** (and related fields) cleared
- **Result:** Vehicles are **allocated to the command’s pool**. They appear in that command’s “Command Pool” and can be issued to officers by the CD.

---

## 4. Receipt by Unit Head (optional)

- **Who:** **Area Controller** (Unit Head) for that command
- **Where:** Fleet → after release → Receive / Acknowledge receipt on the assignment
- **What:** The assignment is updated with **received_by_user_id** and **received_at**
- **Result:** System records that the Unit Head has received the vehicle(s) for the command. The vehicle remains **AT_COMMAND_POOL** until issued to an officer.

---

## 5. Issued to an officer (allocation to officer)

- **Who:** **CD (Chief Driver)** for that command
- **Where:** Fleet → Vehicles → open vehicle (in command pool) → Issue vehicle
- **What:**
  - Vehicle must be **AT_COMMAND_POOL** and not reserved
  - A **FleetVehicleAssignment** is created with **assigned_to_officer_id**
  - Vehicle is updated:
    - **current_officer_id** = that officer
    - **lifecycle_status** = in officer custody (e.g. **IN_OFFICER_CUSTODY**)
- **Result:** Vehicle is **allocated to an officer** and no longer in the command pool until returned.

---

## 6. Return from officer

- **Who:** **CD** or the **Officer** (depending on implementation)
- **Where:** Fleet → Vehicles → open vehicle → Process return
- **What:** Return is recorded; vehicle goes back to **AT_COMMAND_POOL** for that command (assignment ended or return record created)
- **Result:** Vehicle is back in the command pool and can be issued again.

---

## Summary

| Stage              | Who                    | Vehicle status / result                    |
|--------------------|------------------------|--------------------------------------------|
| Intake             | Transport Store/Receiver | **IN_STOCK** (in inventory)              |
| Proposal           | CC T&L                 | **Reserved** for request (still IN_STOCK)  |
| Release to command | CC T&L                 | **AT_COMMAND_POOL** (allocated to command)|
| Receive            | Area Controller        | Recorded as received by Unit Head          |
| Issue to officer   | CD                     | **In officer custody** (allocated to officer) |
| Return             | CD / Officer           | Back **AT_COMMAND_POOL**                   |

**Re-allocation:** For **FLEET_RE_ALLOCATION**, CC T&L selects an existing vehicle (already in the system) and releases it to the requesting command in one step; that vehicle then moves to **AT_COMMAND_POOL** for that command (same as “Released to command” above).
