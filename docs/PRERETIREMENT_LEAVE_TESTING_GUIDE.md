# Preretirement Leave Testing Guide

## Overview
This guide explains how to test the preretirement leave functionality, including automatic placement and CGC approval features.

## Test Data Created

The `TestPreretirementLeaveSeeder` has created 6 test officers with different scenarios:

1. **NCSTEST001** - Past preretirement date (should auto-place)
2. **NCSTEST002** - Approaching preretirement (should auto-place)
3. **NCSTEST003** - Preretirement date is today (should auto-place)
4. **NCSTEST004** - Already past preretirement (should auto-place)
5. **NCSTEST005** - Already past preretirement (should auto-place)
6. **NCSTEST006** - CGC approved (already approved in office)

## Testing Steps

### Step 1: Test Automatic Preretirement Leave Placement

Run the automatic placement service to place officers on preretirement leave:

```bash
php artisan tinker
```

Then execute:
```php
$service = new App\Services\RetirementService();
$result = $service->checkAndActivatePreRetirementStatus();
echo "Activated: " . $result . " officers\n";
```

**Expected Result:**
- Officers with preretirement dates in the past should be automatically placed
- Their status should change to `ON_PRERETIREMENT_LEAVE`
- Notifications should be sent to officers, CGC, Accounts, and Welfare

### Step 2: Login as CGC

1. Go to login page: `http://your-domain/login`
2. Login with:
   - **Email:** `cgc@ncs.gov.ng`
   - **Password:** `password123`

**Expected Result:**
- Should redirect to CGC dashboard (`/cgc/dashboard`)
- Sidebar should show:
  - Dashboard
  - Preretirement Leave (with submenu)

### Step 3: View Preretirement Leave Dashboard

1. Click on "Dashboard" in sidebar
2. Check statistics cards:
   - On Preretirement Leave count
   - Approaching (3 Months) count
   - CGC Approved (In Office) count
   - Auto Placed count

**Expected Result:**
- Should see counts for different statuses
- Recent placements should be listed

### Step 4: View All Preretirement Leave

1. Click "Preretirement Leave" → "All Preretirement Leave"
2. You should see officers on preretirement leave

**Expected Result:**
- Should see officers with status:
  - "Auto Placed" (blue badge)
  - "Approved (In Office)" (green badge) - for TEST006
- Can search by service number or name
- Can filter by status

### Step 5: View Officers Approaching

1. Click "Preretirement Leave" → "Officers Approaching"
2. Should see officers approaching preretirement leave (within 3 months)

**Expected Result:**
- Should see officers with preretirement dates in the future
- Days until preretirement should be displayed
- Can approve them proactively

### Step 6: Test CGC Approval (Approve In Office)

1. Go to "All Preretirement Leave"
2. Find an officer with "Auto Placed" status
3. Click "Approve In Office" button
4. Fill in approval reason (optional)
5. Click "Approve"

**Expected Result:**
- Officer status should change to "Approved (In Office)"
- Officer's preretirement_leave_status should be `PRERETIREMENT_LEAVE_IN_OFFICE`
- Officer should receive notification
- Success message should appear

### Step 7: Test Cancel CGC Approval

1. Find an officer with "Approved (In Office)" status (TEST006)
2. Click "Cancel Approval" button
3. Confirm the action

**Expected Result:**
- Officer status should revert to "Auto Placed"
- Officer's preretirement_leave_status should be `ON_PRERETIREMENT_LEAVE`
- Success message should appear

### Step 8: Test Officer Restriction

1. Logout as CGC
2. Login as one of the test officers (e.g., `testtest001@test.ncs.gov.ng` / `password123`)
3. Try to apply for leave
4. Select "Pre-retirement leave" from leave types
5. Try to submit

**Expected Result:**
- Should see error message: "Preretirement leave cannot be applied by officers. It is automatically placed 3 months before retirement and managed by CGC only."
- Application should not be submitted

### Step 9: Verify Notifications

Check notifications for:
- Officers (should receive preretirement leave placement notification)
- CGC (should receive notification when officers are placed)
- Accounts (should receive notification)
- Welfare (should receive notification)

**Expected Result:**
- All relevant parties should have received notifications
- Notifications should contain correct information

## Manual Testing Checklist

- [ ] Test data seeded successfully
- [ ] Automatic placement service runs without errors
- [ ] CGC can login and access dashboard
- [ ] Sidebar shows correct navigation links
- [ ] Dashboard shows correct statistics
- [ ] Can view all preretirement leave officers
- [ ] Can view approaching officers
- [ ] Can approve officer for "in office" status
- [ ] Can cancel CGC approval
- [ ] Officers cannot apply for preretirement leave
- [ ] Notifications are sent correctly
- [ ] Officer status updates correctly in database

## Database Verification

Check the database to verify:

```sql
-- Check retirement list items
SELECT service_number, preretirement_leave_status, auto_placed_at, cgc_approved_at 
FROM retirement_list_items rli
JOIN officers o ON rli.officer_id = o.id;

-- Check officer status
SELECT service_number, preretirement_leave_status, preretirement_leave_started_at 
FROM officers 
WHERE service_number LIKE 'NCSTEST%';

-- Check notifications
SELECT notification_type, title, COUNT(*) 
FROM notifications 
WHERE notification_type LIKE '%PRERETIREMENT%' 
GROUP BY notification_type, title;
```

## Troubleshooting

### Issue: Officers not auto-placed
- Check if preretirement date has passed
- Verify `notified` field is false
- Check logs for errors

### Issue: CGC cannot see officers
- Verify CGC role is assigned
- Check retirement list items exist
- Verify preretirement_leave_status is not null

### Issue: Notifications not sent
- Check notification table
- Verify user has email
- Check logs for notification errors

## Cleanup

To remove test data:

```bash
php artisan tinker
```

```php
// Delete test officers
App\Models\Officer::where('service_number', 'LIKE', 'NCSTEST%')->delete();

// Delete test users
App\Models\User::where('email', 'LIKE', '%@test.ncs.gov.ng')->delete();

// Delete retirement list items
App\Models\RetirementListItem::whereHas('officer', function($q) {
    $q->where('service_number', 'LIKE', 'NCSTEST%');
})->delete();
```

