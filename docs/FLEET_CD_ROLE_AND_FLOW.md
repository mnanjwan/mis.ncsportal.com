# Fleet: CD Role and Flow

## Overview

The **CD (Chief Driver)** role manages vehicle requests and fleet operations at the **command level**. CD users create and submit requests for their command, track them through the workflow, and manage vehicles (issue, return, service status) for their command. CD is **not** a workflow step—they do not approve or forward requests; other roles do that.

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

---

## Dashboard

**Route:** `/fleet/dashboard/cd`

- **Cards:** My Draft Requests, Submitted, KIV, Released, Command Pool Vehicles, Active Assignments, Pending Returns
- **Quick links:** Create Request, View Requests, Command Vehicles, Returns Report
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
2. CD submits → **Step 1:** CC T&L (propose vehicles)
3. **Step 2:** CGC (approve)
4. **Step 3:** DCG FATS (forward)
5. **Step 4:** ACG TS (forward)
6. **Step 5:** CC T&L (release) → **RELEASED**
7. CD (and origin command) notified

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
| **Reports** | Returns Report |
