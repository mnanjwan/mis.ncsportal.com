# Fleet: Area Controller (Head of Unit) Workflow

## Role

**Area Controller** at the command level does **not** appear as a workflow step for **New Vehicle** requests in the current system. When CD submits a New Vehicle request, it goes **directly to CC T&L** (Step 1). Area Controller’s role in fleet is:

- **Receive** released vehicle(s) for their command (Unit Head) after CC T&L releases at Step 5.
- **Create / submit** their own fleet requests if they have the role (same as CD for creating).
- **Act** on other request types only if those workflows include Area Controller as a step (current New Vehicle flow does not).

---

## Workflow from Area Controller (current)

### New Vehicle requests – no step for Area Controller

- CD submits New Vehicle → request goes to **CC T&L (Step 1)** → CGC → DCG FATS → ACG TS → **CC T&L (Step 5)** release.
- Area Controller does **not** see these in their Fleet Inbox as an action step.
- When CC T&L **releases** at Step 5, vehicles are assigned to the **origin command**. Area Controller (Unit Head) then **receives** them.

### What Area Controller does

1. **Receive vehicle(s)**  
   After a New Vehicle request is **RELEASED**, open Fleet → Requests (or the assignment) and **Receive** / **Acknowledge receipt** for the vehicle(s) assigned to your command.

2. **Create / submit requests**  
   Area Controller can create and submit fleet requests (same create flow as CD) if they have the role.

3. **Other request types**  
   For Re-allocation, Requisition, Repair/OPE/Use, only the roles defined in those workflows see Inbox items. New Vehicle currently has no Area Controller step.

---

## Summary

| For New Vehicle | Area Controller |
|-----------------|------------------|
| **Step 1–5 in workflow?** | No (request goes CC T&L → CGC → DCG FATS → ACG TS → CC T&L). |
| **Inbox items for New Vehicle?** | No. |
| **After release** | **Receive** vehicle(s) for their command (Unit Head). |

The CD / New Vehicle flow may change later (e.g. Head of Unit as first step); Area Controller workflow will be updated if that is implemented.
