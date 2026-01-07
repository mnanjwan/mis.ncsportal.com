# Building Unit - Complete Functions List

## Overview
**Role:** Building Unit - Accommodation Manager  
**Access Level:** Command-level (also applies to Headquarters)  
**Primary Responsibility:** Officer quarters allocation and management

---

## ✅ IMPLEMENTED Functions

### 1. Quarter Creation & Management
- ✅ **Create Quarters (Block Numbers)**
  - Create new quarters with unique quarter numbers
  - Assign quarter types (Single Room, One Bedroom, Two Bedroom, Three Bedroom, Four Bedroom, Duplex, Bungalow, Other)
  - Link quarters to specific commands
  - Set quarter as active/inactive

- ✅ **View Quarters**
  - List all quarters in assigned command
  - View quarter details (number, type, occupancy status)
  - Filter quarters by availability (Available/Occupied)
  - View current occupant information

- ✅ **Quarters Statistics**
  - View total quarters count
  - View occupied quarters count
  - View available quarters count
  - Real-time statistics dashboard

### 2. Officer Management
- ✅ **Search Officers**
  - Search officers within assigned command
  - Search by service number, name, or email
  - Filter officers by quartered status (Yes/No)
  - View officer details

- ✅ **View Officers List**
  - List all officers in assigned command
  - Display quartered status for each officer
  - Pagination support
  - Real-time search functionality

### 3. Quartered Status Management
- ✅ **Update Individual Status**
  - Update single officer's quartered status (Yes/No)
  - Status automatically syncs with PE (Personal Emolument) form
  - Command-level access only

- ✅ **Bulk Update Status**
  - Update quartered status for multiple officers at once
  - Select multiple officers and update status
  - Efficient batch processing

- ✅ **Status Sync**
  - Quartered status automatically reflects on PE form
  - When status is Yes, emolument form shows Yes
  - Real-time synchronization

### 4. Quarter Allocation
- ✅ **Allocate Quarters**
  - Search and select available quarters
  - Search and select officers in command
  - Allocate quarter to officer
  - Set allocation date
  - Automatically update officer's quartered status to Yes
  - Mark quarter as occupied
  - Send notifications to officer and relevant parties

- ✅ **Deallocate Quarters**
  - Deallocate quarters from officers
  - Set deallocation date
  - Automatically update officer's quartered status to No
  - Mark quarter as available
  - Send notifications to officer and relevant parties

- ✅ **View Allocation History**
  - View all quarter allocations
  - Track allocation dates
  - Track deallocation dates
  - View allocation history per officer
  - View allocation history per quarter

### 5. Notifications
- ✅ **Quarter Created Notifications**
  - Notify other Building Unit users when new quarter is created
  - Email and in-app notifications

- ✅ **Quarter Allocated Notifications**
  - Notify officer when quarter is allocated
  - Email and in-app notifications
  - Include quarter details and allocation date

- ✅ **Quarter Deallocated Notifications**
  - Notify officer when quarter is deallocated
  - Email and in-app notifications
  - Include deallocation date

- ✅ **Quartered Status Updated Notifications**
  - Notify officer when quartered status is updated
  - Email and in-app notifications
  - Include new status (Yes/No)

### 6. Access Control & Security
- ✅ **Command-Based Access**
  - All operations restricted to assigned command
  - Cannot access other commands' data
  - Command ID retrieved from user_roles pivot table (HRD-assigned)
  - Headquarters also supported

- ✅ **Role-Based Permissions**
  - Only Building Unit role can perform quarter operations
  - Middleware protection on all routes
  - API endpoint authorization checks

---

## ❌ MISSING Functions (To Be Implemented)

### 7. Quarter Request System
- ❌ **Officer Quarter Requests**
  - Officers cannot currently request quarters through the system
  - Need officer-facing request interface
  - Need request submission endpoint

- ❌ **View Quarter Requests**
  - Building Unit cannot view pending requests
  - Need request management interface
  - Need to filter requests by status

- ❌ **Approve Requests**
  - Cannot approve officer requests
  - Need approval workflow
  - Approval should automatically allocate quarter

- ❌ **Reject Requests**
  - Cannot reject requests with reason
  - Need rejection functionality
  - One-time rejection only (cannot reject again after first rejection)
  - Must include rejection reason

### 8. Request Status Tracking
- ❌ **Request Statuses**
  - PENDING - Request submitted, awaiting review
  - APPROVED - Request approved, quarter allocated
  - REJECTED - Request rejected with reason (final)

- ❌ **Request History**
  - Cannot view request history
  - Cannot track request lifecycle
  - Cannot view rejection reasons

---

## Complete Function Summary

### Implemented (25 functions)
1. Create quarters (Block Numbers)
2. View quarters list
3. Filter quarters by availability
4. View quarters statistics
5. Search officers in command
6. View officers list
7. Filter officers by quartered status
8. Update individual quartered status
9. Bulk update quartered status
10. Status syncs with PE form
11. Allocate quarters to officers
12. Deallocate quarters from officers
13. Set allocation dates
14. Set deallocation dates
15. View allocation history
16. View current occupants
17. Notify on quarter creation
18. Notify on quarter allocation
19. Notify on quarter deallocation
20. Notify on status update
21. Command-based access control
22. Role-based permissions
23. Searchable quarter selection
24. Searchable officer selection
25. Real-time statistics dashboard

### Missing (6 functions)
26. Officers can request quarters
27. View quarter requests
28. Approve quarter requests
29. Reject quarter requests with reason (one-time)
30. Track request status
31. View request history

---

## Implementation Priority

### High Priority (Critical Missing Features)
1. **Quarter Request System** - Allow officers to request quarters
2. **Request Management** - Building Unit can view and process requests
3. **Rejection Functionality** - Reject with reason (one-time only)

### Medium Priority (Enhancements)
1. Waiting list management
2. Request history tracking
3. Advanced filtering options

### Low Priority (Nice to Have)
1. Quarter maintenance tracking
2. Advanced reporting
3. Bulk operations on requests

---

## Technical Requirements for Missing Features

### Database Changes
- Create `quarter_requests` table
- Add `request_id` to `officer_quarters` table
- Add `rejection_reason` field
- Add `rejected_at`, `rejected_by`, `approved_at`, `approved_by` fields

### API Endpoints Needed
- `POST /api/v1/quarters/request` - Submit request
- `GET /api/v1/quarters/requests` - List requests (Building Unit)
- `GET /api/v1/quarters/my-requests` - List own requests (Officer)
- `POST /api/v1/quarters/requests/{id}/approve` - Approve request
- `POST /api/v1/quarters/requests/{id}/reject` - Reject request

### Frontend Views Needed
- Officer: Request quarter form
- Officer: My requests list
- Building Unit: Requests management page
- Building Unit: Request approval/rejection modal

### Business Rules
- One request per officer at a time (or allow multiple?)
- Rejection is final (one-time only)
- Approval automatically allocates quarter
- Rejection reason is required
- All requests filtered by command










