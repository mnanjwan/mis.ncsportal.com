# Building Unit - Specification Comparison & Updates

## Current System Specification vs New Requirements

### Current System Specification (Lines 68-83)

**Core Functions:**
1. Quarters the Officers (But the status on the PE form will indicate whether the Officers is quartered or not)
2. At the Command level, enters the Quartered status Yes or No option on the drop down
3. Allocate quarters to eligible officers
4. Update quartered status (Yes/No) in the system
5. Maintain quarters occupancy database
6. Process quarter allocation requests
7. Deallocate quarters when officers are posted
8. Track quarters maintenance requirements
9. Generate accommodation reports
10. Manage waiting lists for quarters

### New Requirements

1. **The officer will create the Block No** (Quarter Number)
2. **The officer can search for an officer on the command level and allocate a room to an officer**
3. **The officer can reject once and state reason only once** (like smartclass)
4. **The status of Quartered yes or no will change on his emolument form to Yes**

**Note:** This also applies to Headquarters as building is also a unit under HQ.

---

## Comparison Analysis

### ✅ IMPLEMENTED Functions

| Requirement | Status | Implementation |
|------------|--------|----------------|
| Create Block No (Quarter Number) | ✅ Implemented | `POST /api/v1/quarters` - Create quarter form |
| Search officers at command level | ✅ Implemented | Officers list with search functionality |
| Allocate room to officer | ✅ Implemented | `POST /api/v1/quarters/allocate` |
| Update Quartered status Yes/No | ✅ Implemented | Individual and bulk update endpoints |
| Status syncs with emolument form | ✅ Implemented | `quartered` field updates automatically |
| Deallocate quarters | ✅ Implemented | `POST /api/v1/quarters/{id}/deallocate` |
| Command-level access | ✅ Implemented | All endpoints filter by command |

### ❌ MISSING Functions

| Requirement | Status | Needed |
|------------|--------|--------|
| **Officer quarter request system** | ❌ Not Implemented | Officers need to REQUEST quarters |
| **Rejection functionality** | ❌ Not Implemented | Building Unit can reject requests with reason (one-time only) |
| **Request status tracking** | ❌ Not Implemented | PENDING, APPROVED, REJECTED statuses |
| **Rejection reason field** | ❌ Not Implemented | Store rejection reason |

---

## Updated System Specification

### Building Unit - Accommodation Manager
**Primary Role:** Officer quarters allocation and management

**Access Level:** Command-level quarters and accommodation records (also applies to Headquarters)

**Core Functions:**

1. **Create Quarters (Block Numbers)**
   - Create new quarters/blocks with unique quarter numbers
   - Assign quarter types (Single Room, One Bedroom, Two Bedroom, etc.)
   - Link quarters to specific commands

2. **Search and Allocate Quarters**
   - Search for officers within assigned command
   - View available quarters in command
   - Allocate quarters (rooms) to eligible officers
   - Set allocation date

3. **Process Quarter Requests**
   - View quarter requests from officers
   - Approve quarter requests
   - **Reject quarter requests with reason (one-time rejection only)**
   - Track request status (PENDING, APPROVED, REJECTED)

4. **Manage Quartered Status**
   - At Command level, enter Quartered status Yes/No option on dropdown
   - Update individual officer quartered status
   - Bulk update quartered status for multiple officers
   - Status automatically reflects on PE (Personal Emolument) form

5. **Deallocate Quarters**
   - Deallocate quarters when officers are posted
   - Update officer's quartered status automatically
   - Make quarters available for reallocation

6. **Maintain Quarters Database**
   - Track quarters occupancy
   - Monitor available vs occupied quarters
   - Generate quarters statistics (Total, Occupied, Available)

7. **Generate Reports**
   - Accommodation reports
   - Quarters occupancy reports
   - Allocation history reports

8. **Manage Waiting Lists**
   - Track officers waiting for quarters
   - Prioritize allocation based on criteria

---

## Missing Implementation Requirements

### 1. Quarter Request System

**Officer Side:**
- Officers can request quarters through the system
- Request includes preferred quarter type (optional)
- Request status: PENDING

**Building Unit Side:**
- View all pending quarter requests
- Approve or reject requests
- Can only reject once per request with reason
- After rejection, request cannot be rejected again

### 2. Database Changes Needed

**New Table: `quarter_requests`**
```sql
- id
- officer_id (foreign key)
- quarter_id (nullable - if specific quarter requested)
- preferred_quarter_type (nullable)
- status (PENDING, APPROVED, REJECTED)
- rejection_reason (nullable - one-time only)
- rejected_at (nullable)
- rejected_by (nullable)
- approved_at (nullable)
- approved_by (nullable)
- created_at
- updated_at
```

**Update `officer_quarters` table:**
- Add `request_id` (nullable) to link allocation to request

### 3. API Endpoints Needed

**Officer Endpoints:**
- `POST /api/v1/quarters/request` - Request quarter
- `GET /api/v1/quarters/my-requests` - View own requests
- `GET /api/v1/quarters/my-requests/{id}` - View request details

**Building Unit Endpoints:**
- `GET /api/v1/quarters/requests` - List all requests (command-filtered)
- `POST /api/v1/quarters/requests/{id}/approve` - Approve request
- `POST /api/v1/quarters/requests/{id}/reject` - Reject request (one-time only)

### 4. Workflow

**Quarter Request Workflow:**
1. Officer requests quarter → Status: PENDING
2. Building Unit reviews request
3. Building Unit can:
   - **Approve** → Allocate quarter → Status: APPROVED → Officer's quartered status = Yes
   - **Reject with reason** → Status: REJECTED → Cannot reject again
4. System updates officer's quartered status based on approval
5. Status reflects on PE form

---

## Complete Building Unit Functions List

### Quarter Management
1. ✅ Create quarters (Block Numbers)
2. ✅ View all quarters in command
3. ✅ Filter quarters by availability
4. ✅ View quarter details and occupancy

### Officer Management
5. ✅ Search officers in command
6. ✅ View officers list with quartered status
7. ✅ Filter officers by quartered status
8. ✅ Update individual officer quartered status
9. ✅ Bulk update quartered status

### Allocation Management
10. ✅ Allocate quarters to officers
11. ✅ Deallocate quarters from officers
12. ✅ View allocation history
13. ❌ **Process quarter requests from officers** (MISSING)
14. ❌ **Approve quarter requests** (MISSING)
15. ❌ **Reject quarter requests with reason (one-time)** (MISSING)

### Status Management
16. ✅ Update quartered status Yes/No at command level
17. ✅ Status syncs with PE (Personal Emolument) form
18. ✅ Automatic status update on allocation/deallocation

### Reporting & Statistics
19. ✅ View quarters statistics (Total, Occupied, Available)
20. ✅ Generate accommodation reports
21. ✅ Track quarters occupancy

### Notifications
22. ✅ Notify officers on quarter allocation
23. ✅ Notify officers on quarter deallocation
24. ✅ Notify officers on quartered status update
25. ✅ Notify other Building Unit users on new quarter creation

---

## Implementation Priority

### Phase 1: Critical Missing Features
1. **Quarter Request System** - Officers can request quarters
2. **Rejection Functionality** - Building Unit can reject with reason (one-time)
3. **Request Management Interface** - View and process requests

### Phase 2: Enhancements
1. Waiting list management
2. Advanced reporting
3. Quarter maintenance tracking







