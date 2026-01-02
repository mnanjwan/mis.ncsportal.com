# Building Unit - Complete Functions Summary

## What Building Unit Does

Building Unit manages officer quarters (accommodation) at the command level. This includes creating quarters, allocating them to officers, managing requests, and tracking occupancy.

**Access:** Command-level (also applies to Headquarters)

---

## ✅ ALL CURRENT BUILDING UNIT FUNCTIONS

### 1. CREATE QUARTERS (Block Numbers)
- Create new quarters with unique block/quarter numbers
- Assign quarter types (Single Room, One Bedroom, Two Bedroom, Three Bedroom, Four Bedroom, Duplex, Bungalow, Other)
- Link quarters to specific commands
- Set quarters as active or inactive

### 2. VIEW QUARTERS
- See all quarters in assigned command
- View quarter details (number, type, if occupied or available)
- See who is currently occupying each quarter
- Filter quarters by availability (Available/Occupied)

### 3. QUARTERS STATISTICS
- View total number of quarters
- View how many are occupied
- View how many are available
- See real-time statistics on dashboard

### 4. SEARCH OFFICERS
- Search for officers within assigned command
- Search by service number, name, or email
- Filter officers by whether they are quartered or not

### 5. VIEW OFFICERS LIST
- See all officers in assigned command
- See each officer's quartered status (Yes/No)
- Search and filter officers

### 6. UPDATE QUARTERED STATUS (Individual)
- Change single officer's quartered status to Yes or No
- Status automatically appears on officer's emolument form
- Only affects officers in assigned command

### 7. UPDATE QUARTERED STATUS (Bulk)
- Update quartered status for multiple officers at once
- Select several officers and update all at once
- Faster than updating one by one

### 8. ALLOCATE QUARTERS TO OFFICERS
- Search and select an available quarter
- Search and select an officer in command
- Assign the quarter to the officer
- Set the allocation date
- Automatically mark officer as quartered (Yes)
- Mark quarter as occupied
- Send notification to officer

### 9. DEALLOCATE QUARTERS
- Remove quarter assignment from officer
- Set deallocation date
- Automatically mark officer as not quartered (No)
- Mark quarter as available again
- Send notification to officer

### 10. VIEW ALLOCATION HISTORY
- See all quarter allocations that have happened
- See when quarters were allocated
- See when quarters were deallocated
- View history per officer or per quarter

### 11. NOTIFICATIONS
- Notify officers when quarter is allocated
- Notify officers when quarter is deallocated
- Notify officers when quartered status changes
- Notify other Building Unit users when new quarter is created
- All notifications sent via email and in-app

### 12. COMMAND-LEVEL ACCESS CONTROL
- Can only see and manage quarters in assigned command
- Cannot access other commands' data
- Command assignment comes from HRD role assignment
- Also works for Headquarters

---

## ❌ MISSING FUNCTIONS (Need to Implement)

### 13. OFFICERS REQUEST QUARTERS
- **NOT YET IMPLEMENTED**
- Officers should be able to request quarters through the system
- Request should include preferred quarter type (optional)
- Request status starts as PENDING

### 14. VIEW QUARTER REQUESTS
- **NOT YET IMPLEMENTED**
- Building Unit should see all pending requests from officers
- Filter requests by status (PENDING, APPROVED, REJECTED)
- See request details

### 15. APPROVE QUARTER REQUESTS
- **NOT YET IMPLEMENTED**
- Building Unit can approve officer requests
- Approval automatically allocates quarter to officer
- Updates officer's quartered status to Yes
- Changes request status to APPROVED

### 16. REJECT QUARTER REQUESTS
- **NOT YET IMPLEMENTED**
- Building Unit can reject requests with a reason
- **IMPORTANT:** Can only reject once per request
- After first rejection, cannot reject again
- Must provide rejection reason
- Changes request status to REJECTED

### 17. TRACK REQUEST STATUS
- **NOT YET IMPLEMENTED**
- See request status: PENDING, APPROVED, or REJECTED
- View rejection reasons
- Track request history

---

## COMPLETE LIST (Simple Terms)

### What Building Unit CAN Do Now:
1. ✅ Create quarters (Block Numbers)
2. ✅ View all quarters in command
3. ✅ See which quarters are available or occupied
4. ✅ See quarters statistics (total, occupied, available)
5. ✅ Search for officers in command
6. ✅ View list of officers with their quartered status
7. ✅ Change single officer's quartered status (Yes/No)
8. ✅ Change multiple officers' quartered status at once
9. ✅ Allocate a quarter to an officer
10. ✅ Remove quarter from an officer (deallocate)
11. ✅ See allocation history
12. ✅ Send notifications to officers
13. ✅ Only access data for assigned command

### What Building Unit CANNOT Do Yet:
14. ❌ Officers cannot request quarters
15. ❌ Cannot see quarter requests from officers
16. ❌ Cannot approve requests
17. ❌ Cannot reject requests with reason (one-time)

---

## KEY DIFFERENCES FROM REQUIREMENTS

### ✅ Already Working:
- ✅ Building Unit creates Block Numbers (quarters)
- ✅ Building Unit searches officers at command level
- ✅ Building Unit allocates rooms to officers
- ✅ Quartered status Yes/No changes on emolument form

### ❌ Still Missing:
- ❌ Officers cannot request quarters
- ❌ Building Unit cannot reject requests with reason (one-time only)

---

## WHAT NEEDS TO BE BUILT

### 1. Quarter Request System
- Officers submit requests for quarters
- Requests have status: PENDING, APPROVED, REJECTED
- Building Unit reviews and processes requests

### 2. Rejection Functionality
- Building Unit can reject request with reason
- **Critical Rule:** Can only reject once - after first rejection, cannot reject again
- Rejection reason is required
- Status becomes REJECTED (final)

### 3. Request Management Interface
- Building Unit sees all requests
- Can approve or reject requests
- Can see request history and reasons

---

## SUMMARY

**Total Functions:** 17  
**Implemented:** 13 ✅  
**Missing:** 4 ❌

**Main Gap:** The quarter request and rejection system is not yet implemented. Currently, Building Unit can only directly allocate quarters, but officers cannot request them, and Building Unit cannot reject requests with reasons.








