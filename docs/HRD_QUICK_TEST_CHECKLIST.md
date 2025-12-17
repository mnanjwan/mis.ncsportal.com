# HRD Quick Test Checklist

## Login
- **Email**: `hrd@ncs.gov.ng`
- **Password**: `password123`

---

## Quick Test Flow (15-20 minutes)

### ✅ 1. Dashboard (2 min)
- [ ] Navigate to `/hrd/dashboard`
- [ ] Verify statistics display (Total Officers, Pending Emoluments, etc.)
- [ ] Check Recent Officers section
- [ ] Verify no "Loading..." messages

### ✅ 2. Officer Onboarding (5 min)
- [ ] Navigate to `/hrd/onboarding`
- [ ] Verify "Officers Needing Onboarding" section shows officers
- [ ] Click "Initiate" on an officer
- [ ] Enter email address
- [ ] Submit form
- [ ] Verify onboarding link is generated and displayed
- [ ] Test "Copy" button for link
- [ ] Check "Onboarded Officers" section shows officers with accounts
- [ ] Test "Resend Link" for an onboarded officer

### ✅ 3. Promotion Criteria (3 min)
- [ ] Navigate to `/hrd/promotion-criteria`
- [ ] Click "Add Criteria"
- [ ] Select rank, enter years required
- [ ] Submit form
- [ ] Verify criteria appears in list
- [ ] Try creating duplicate active criteria (should fail)

### ✅ 4. Leave Types (3 min)
- [ ] Navigate to `/hrd/leave-types`
- [ ] Click "Create Leave Type"
- [ ] Fill form (name, code, duration, etc.)
- [ ] Submit form
- [ ] Verify leave type appears in list
- [ ] Test edit functionality

### ✅ 5. Manning Requests (5 min)
- [ ] Navigate to `/hrd/manning-requests`
- [ ] Verify approved requests are visible
- [ ] Click on a request to view details
- [ ] Click "Find Matches" for a requirement item
- [ ] Verify matched officers display
- [ ] Select officers (up to quantity needed)
- [ ] Click "Generate Movement Order"
- [ ] Verify movement order is created

### ✅ 6. Course Nominations (3 min)
- [ ] Navigate to `/hrd/courses`
- [ ] Click "Nominate Officer"
- [ ] Select officer, enter course details
- [ ] Submit form
- [ ] Verify course appears in list
- [ ] Click on course to view details
- [ ] Mark course as completed
- [ ] Verify status changes to "Completed"

### ✅ 7. System Settings (2 min)
- [ ] Navigate to `/hrd/system-settings`
- [ ] Modify a setting (e.g., retirement age)
- [ ] Click "Save Settings"
- [ ] Verify success message
- [ ] Refresh page and verify change persisted

### ✅ 8. Staff Orders (3 min)
- [ ] Navigate to `/hrd/staff-orders`
- [ ] Click "Create Staff Order"
- [ ] Verify order number auto-generates
- [ ] Test searchable officer select
- [ ] Select officer (verify from command auto-fills)
- [ ] Submit form
- [ ] Verify order appears in list

### ✅ 9. Movement Orders (2 min)
- [ ] Navigate to `/hrd/movement-orders`
- [ ] Click "Create Movement Order"
- [ ] Select manning request or enter criteria
- [ ] Submit form
- [ ] Verify order appears in list

### ✅ 10. Promotion Eligibility (2 min)
- [ ] Navigate to `/hrd/promotion-eligibility`
- [ ] Click "Generate Eligibility List"
- [ ] Enter year
- [ ] Submit form
- [ ] Verify list is created with officers
- [ ] Click on list to view details

### ✅ 11. Retirement List (2 min)
- [ ] Navigate to `/hrd/retirement-list`
- [ ] Click "Generate Retirement List"
- [ ] Enter year
- [ ] Submit form
- [ ] Verify list is created with officers

### ✅ 12. Reports (2 min)
- [ ] Navigate to `/hrd/reports`
- [ ] Select report type
- [ ] Select date range
- [ ] Click "Generate Report"
- [ ] Verify report downloads

---

## Critical Integration Tests

### Test 1: Promotion Criteria → Eligibility List
1. Set criteria for a rank (e.g., 3 years)
2. Generate eligibility list
3. Verify only officers with 3+ years in that rank are included

### Test 2: Manning Request → Movement Order
1. View approved manning request
2. Trigger matching
3. Select officers
4. Generate movement order
5. Verify order is created

### Test 3: System Settings → Calculations
1. Change retirement age to 55
2. Generate retirement list
3. Verify officers reaching 55 are included

---

## Expected Issues to Watch For

- [ ] Forms not submitting
- [ ] Validation errors not displaying
- [ ] Success/error messages not showing
- [ ] Pagination not working
- [ ] Filters not working
- [ ] Mobile responsiveness issues
- [ ] Breadcrumbs missing
- [ ] Links broken

---

## Success Criteria

✅ All pages load without errors  
✅ All forms submit successfully  
✅ All workflows complete end-to-end  
✅ Data persists correctly  
✅ UI is responsive  
✅ Error handling works  
✅ Success messages display  

---

## Report Issues

If you find any issues, note:
1. **Feature**: Which feature/function
2. **Steps**: What you did
3. **Expected**: What should happen
4. **Actual**: What actually happened
5. **Error**: Any error messages

