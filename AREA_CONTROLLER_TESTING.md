# Area Controller Testing Guide

## Overview
Area Controller is responsible for approving:
1. **Manning Level Requests** - Submitted by Staff Officers
2. **Duty Rosters** - Submitted by Staff Officers
3. **Emoluments** - Validation (existing functionality)
4. **Leave & Pass** - Viewing (existing functionality)

## Testing Checklist

### 1. Dashboard Access
**Route:** `/area-controller/dashboard`

**Test Steps:**
1. Login as Area Controller
2. Verify dashboard loads with statistics:
   - Pending Emoluments count
   - Pending Manning Requests count
   - Pending Duty Rosters count
3. Verify "Recent Manning Requests" section shows submitted requests
4. Verify "Recent Duty Rosters" section shows submitted rosters
5. Verify Quick Actions links work:
   - Emoluments
   - Leave & Pass
   - Manning Requests
   - Duty Rosters

**Expected Results:**
- Dashboard displays correct counts
- Recent items are clickable and link to detail pages
- All quick action buttons work

---

### 2. Manning Requests - List View
**Route:** `/area-controller/manning-level`

**Test Steps:**
1. Navigate to "Manning Requests" from sidebar or dashboard
2. Verify only SUBMITTED requests are shown
3. Verify table displays:
   - Request ID
   - Command name
   - Zone name
   - Requested By
   - Submitted Date
   - Number of items
   - Review button
4. Click "Review" on a request

**Expected Results:**
- Only SUBMITTED status requests appear
- All columns display correctly
- Review button navigates to detail page

---

### 3. Manning Requests - Detail & Approval
**Route:** `/area-controller/manning-level/{id}`

**Test Steps:**
1. View a submitted manning request
2. Verify all details display:
   - Command and Zone
   - Requested By
   - Submitted Date
   - Notes (if any)
   - All requirement items (rank, quantity, sex, qualification)
3. Test **Approve**:
   - Click "Approve" button
   - Confirm action
   - Verify redirect to list page
   - Verify success message
   - Verify request status changes to APPROVED
4. Test **Reject**:
   - View another submitted request
   - Click "Reject" button
   - Enter rejection reason (required)
   - Submit rejection
   - Verify redirect to list page
   - Verify success message
   - Verify request status changes to REJECTED

**Expected Results:**
- All request details display correctly
- Approve action works and updates status
- Reject action requires reason and updates status
- Approved requests no longer appear in list (only SUBMITTED)

---

### 4. Duty Rosters - List View
**Route:** `/area-controller/roster`

**Test Steps:**
1. Navigate to "Duty Rosters" from sidebar or dashboard
2. Verify only SUBMITTED rosters are shown
3. Verify table displays:
   - Command name
   - Period (start - end dates)
   - Prepared By
   - Number of assignments
   - Review button
4. Click "Review" on a roster

**Expected Results:**
- Only SUBMITTED status rosters appear
- All columns display correctly
- Review button navigates to detail page

---

### 5. Duty Rosters - Detail & Approval
**Route:** `/area-controller/roster/{id}`

**Test Steps:**
1. View a submitted duty roster
2. Verify all details display:
   - Command name
   - Period dates
   - Prepared By
   - Total assignments count
   - All officer assignments (officer name, duty date, shift, notes)
3. Test **Approve**:
   - Click "Approve" button
   - Confirm action
   - Verify redirect to list page
   - Verify success message
   - Verify roster status changes to APPROVED
4. Test **Reject**:
   - View another submitted roster
   - Click "Reject" button
   - Enter rejection reason (required)
   - Submit rejection
   - Verify redirect to list page
   - Verify success message
   - Verify roster status changes to REJECTED

**Expected Results:**
- All roster details display correctly
- All assignments are visible
- Approve action works and updates status
- Reject action requires reason and updates status
- Approved rosters no longer appear in list (only SUBMITTED)

---

### 6. Sidebar Navigation
**Test Steps:**
1. Verify sidebar shows all Area Controller menu items:
   - Dashboard
   - Emoluments
   - Leave & Pass
   - Manning Requests (NEW)
   - Duty Rosters (NEW)
2. Click each menu item
3. Verify correct pages load

**Expected Results:**
- All menu items visible and clickable
- Correct routes are accessed

---

### 7. Access Control
**Test Steps:**
1. Try to access Area Controller routes as different roles:
   - Staff Officer
   - DC Admin
   - HRD
   - Officer
2. Verify access is denied (403 error)

**Expected Results:**
- Only Area Controller role can access these routes
- Other roles get 403 Forbidden

---

## Test Data Requirements

Before testing, ensure you have:
1. **Manning Requests** with status = SUBMITTED
   - Created by Staff Officers
   - Submitted to Area Controller
2. **Duty Rosters** with status = SUBMITTED
   - Created by Staff Officers
   - Submitted for approval
3. **Area Controller user** with proper role assignment

---

## Common Issues to Check

1. **Empty Lists**: If no requests/rosters appear, verify:
   - Staff Officers have submitted items
   - Items have status = SUBMITTED (not DRAFT)
   - Area Controller user has correct role

2. **Approval Not Working**: Verify:
   - Request/Roster status is SUBMITTED
   - User has Area Controller role
   - Database migrations are run

3. **Rejection Not Working**: Verify:
   - Rejection reason is provided (required field)
   - Form validation is working

---

## Success Criteria

✅ Dashboard shows correct statistics
✅ Manning Requests list shows only SUBMITTED requests
✅ Manning Request approval/rejection works
✅ Duty Rosters list shows only SUBMITTED rosters
✅ Duty Roster approval/rejection works
✅ Sidebar navigation works
✅ Access control prevents unauthorized access
✅ Status updates correctly after approval/rejection

