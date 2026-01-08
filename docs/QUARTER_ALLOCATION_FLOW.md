# Quarter Allocation Flow - Complete Trace

## Overview
This document traces the complete flow of quarter allocation from Building Unit allocation to officer acceptance/rejection.

## Flow Steps

### 1. BUILDING UNIT ALLOCATES QUARTER

**Endpoint:** `POST /api/v1/quarters/allocate`  
**Controller:** `App\Http\Controllers\Api\V1\QuarterController@allocate`  
**Location:** `app/Http/Controllers/Api/V1/QuarterController.php:224-342`

**What Happens:**
1. ✅ Validates Building Unit role and command assignment
2. ✅ Validates `officer_id`, `quarter_id`, `allocation_date`
3. ✅ Ensures quarter belongs to Building Unit's command
4. ✅ Ensures officer belongs to Building Unit's command
5. ✅ Checks if quarter is already occupied (ACCEPTED allocation exists)
6. ✅ Deactivates previous ACCEPTED allocations for the officer (`is_current = false`)
7. ✅ **Creates NEW allocation with:**
   - `officer_id`: The officer
   - `quarter_id`: The quarter
   - `allocated_date`: Allocation date
   - `is_current`: `true`
   - `status`: `PENDING` ⚠️ **IMPORTANT: Status is PENDING, not ACCEPTED**
   - `allocated_by`: Building Unit user ID
8. ✅ **Does NOT:**
   - Mark quarter as occupied (`is_occupied` remains `false`)
   - Update officer's quartered status (`quartered` remains `false`)
9. ✅ Sends notification to officer via `notifyQuarterAllocated()`

**Database State After Allocation:**
```sql
officer_quarters table:
- id: [new_id]
- officer_id: [officer_id]
- quarter_id: [quarter_id]
- status: 'PENDING'
- is_current: true
- allocated_by: [building_unit_user_id]
- accepted_at: NULL
- rejected_at: NULL

quarters table:
- id: [quarter_id]
- is_occupied: false (still available)

officers table:
- id: [officer_id]
- quartered: false (still not quartered)
```

---

### 2. OFFICER RECEIVES NOTIFICATION

**Notification Service:** `App\Services\NotificationService@notifyQuarterAllocated`  
**Location:** `app/Services/NotificationService.php:1006-1025`

**Notification Content:**
- Title: "Quarter Allocation Pending"
- Message: "You have been allocated Quarter {number} ({type}) effective from {date}. Please accept or reject this allocation on your dashboard."
- Type: `quarter_allocated`
- Notification is saved to `notifications` table

---

### 3. OFFICER VIEWS DASHBOARD

**Route:** `GET /officer/dashboard`  
**Controller:** `App\Http\Controllers\OfficerController@dashboard`  
**Location:** `app/Http/Controllers/OfficerController.php:714-928`

**What Happens:**
1. ✅ Loads officer from authenticated user
2. ✅ Queries pending allocations:
   ```php
   $pendingAllocations = OfficerQuarter::where('officer_id', $officer->id)
       ->where('status', 'PENDING')
       ->where(function($query) {
           $query->where('is_current', true)
                 ->orWhere('created_at', '>=', now()->subDay());
       })
       ->with(['quarter', 'allocatedBy.officer'])
       ->get();
   ```
3. ✅ Passes to view: `dashboards.officer.dashboard`

**View Display:** `resources/views/dashboards/officer/dashboard.blade.php:48-82`

**What Officer Sees:**
- ⚠️ **Alert Card at TOP of dashboard** (if pending allocations exist)
- Shows quarter number, type, allocation date
- "Accept" and "Reject" buttons

---

### 4. OFFICER ACCEPTS ALLOCATION

**Route:** `POST /officer/quarters/allocations/{id}/accept`  
**Controller:** `App\Http\Controllers\QuarterController@acceptAllocation`  
**Location:** `app/Http/Controllers/QuarterController.php:80-154`

**JavaScript Function:** `acceptAllocation(allocationId)`  
**Location:** `resources/views/dashboards/officer/dashboard.blade.php:495-513`

**What Happens:**
1. ✅ Validates officer owns the allocation
2. ✅ Checks allocation is PENDING (not already ACCEPTED/REJECTED)
3. ✅ Checks quarter is still available (not accepted by another officer)
4. ✅ **In Database Transaction:**
   - Updates allocation: `status = 'ACCEPTED'`, `accepted_at = now()`
   - Marks quarter: `is_occupied = true`
   - Updates officer: `quartered = true`
   - Rejects other pending allocations for this officer
5. ✅ Sends notification to Building Unit via `notifyQuarterAllocationAccepted()`
6. ✅ Redirects to dashboard with success message

**Database State After Acceptance:**
```sql
officer_quarters table:
- status: 'ACCEPTED'
- accepted_at: [timestamp]
- is_current: true

quarters table:
- is_occupied: true (now occupied)

officers table:
- quartered: true (now quartered)
```

---

### 5. OFFICER REJECTS ALLOCATION

**Route:** `POST /officer/quarters/allocations/{id}/reject`  
**Controller:** `App\Http\Controllers\QuarterController@rejectAllocation`  
**Location:** `app/Http/Controllers/QuarterController.php:159-208`

**JavaScript Function:** `rejectAllocation(allocationId)`  
**Location:** `resources/views/dashboards/officer/dashboard.blade.php:515-550`

**What Happens:**
1. ✅ Validates officer owns the allocation
2. ✅ Checks allocation is PENDING
3. ✅ Updates allocation:
   - `status = 'REJECTED'`
   - `rejected_at = now()`
   - `is_current = false`
   - `rejection_reason = [optional reason]`
4. ✅ **Does NOT:**
   - Update quarter (`is_occupied` remains `false`)
   - Update officer (`quartered` remains `false`)
5. ✅ Sends notification to Building Unit via `notifyQuarterAllocationRejected()`
6. ✅ Redirects to dashboard with success message

**Database State After Rejection:**
```sql
officer_quarters table:
- status: 'REJECTED'
- rejected_at: [timestamp]
- is_current: false
- rejection_reason: [reason if provided]

quarters table:
- is_occupied: false (still available)

officers table:
- quartered: false (still not quartered)
```

---

## POTENTIAL ISSUES IDENTIFIED

### Issue 1: Dashboard Query May Not Find Allocations
**Location:** `app/Http/Controllers/OfficerController.php:793-798`

**Current Query:**
```php
$pendingAllocations = OfficerQuarter::where('officer_id', $officer->id)
    ->where('status', 'PENDING')
    ->where(function($query) {
        $query->where('is_current', true)
              ->orWhere('created_at', '>=', now()->subDay());
    })
```

**Potential Problems:**
- If allocation was created more than 24 hours ago AND `is_current = false`, it won't show
- If `officer_id` doesn't match logged-in user's officer ID, allocation won't show
- If status is not exactly `'PENDING'` (e.g., space/typo), it won't show

### Issue 2: Route Path Mismatch
**Expected Route:** `/officer/quarters/allocations/{id}/accept`  
**JavaScript Call:** `/officer/quarters/allocations/${allocationId}/accept`  
**Status:** ✅ **CORRECT** - These match

### Issue 3: Notification vs. Database State
**Issue:** Notification says "Please accept or reject" but allocation might already be processed.

**Check:** 
- Verify notification `created_at` matches allocation `created_at`
- Verify allocation status is actually `PENDING` in database
- Verify `officer_id` matches logged-in officer

---

## DEBUGGING CHECKLIST

To debug why allocation doesn't show on dashboard:

1. **Check Database:**
   ```sql
   SELECT * FROM officer_quarters 
   WHERE officer_id = [officer_id] 
   AND status = 'PENDING' 
   ORDER BY created_at DESC;
   ```

2. **Check Officer ID Match:**
   ```sql
   SELECT u.id as user_id, o.id as officer_id 
   FROM users u 
   JOIN officers o ON u.id = o.user_id 
   WHERE u.id = [logged_in_user_id];
   ```

3. **Check Recent Allocations:**
   ```sql
   SELECT oq.*, o.service_number, q.quarter_number 
   FROM officer_quarters oq
   JOIN officers o ON oq.officer_id = o.id
   JOIN quarters q ON oq.quarter_id = q.id
   WHERE oq.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
   ORDER BY oq.created_at DESC;
   ```

4. **Check Notification:**
   ```sql
   SELECT * FROM notifications 
   WHERE notifiable_id = [user_id] 
   AND type = 'App\Notifications\QuarterAllocatedNotification'
   ORDER BY created_at DESC;
   ```

---

## RECOMMENDED FIXES

### Fix 1: Improve Dashboard Query
Show ALL pending allocations, regardless of age or `is_current` status:

```php
$pendingAllocations = OfficerQuarter::where('officer_id', $officer->id)
    ->where('status', 'PENDING')
    ->with(['quarter:id,quarter_number,quarter_type,command_id', 'allocatedBy:id,email', 'allocatedBy.officer:id,user_id,initials,surname'])
    ->orderBy('created_at', 'desc')
    ->get();
```

### Fix 2: Add Logging
Add logging to track allocation creation and dashboard loading:

```php
\Log::info('Allocation Created', [
    'allocation_id' => $allocation->id,
    'officer_id' => $officer->id,
    'status' => $allocation->status,
    'is_current' => $allocation->is_current
]);

\Log::info('Dashboard Loaded', [
    'officer_id' => $officer->id,
    'pending_count' => $pendingAllocations->count()
]);
```

### Fix 3: Verify Notification Timing
Ensure notification is only sent AFTER allocation is successfully saved in database.

---

## SUMMARY

**Expected Flow:**
1. Building Unit allocates → Creates PENDING allocation
2. Notification sent → Officer receives notification
3. Officer checks dashboard → Sees pending allocation at top
4. Officer accepts/rejects → Database updates accordingly

**Current Status:**
- ✅ Allocation creation works (creates PENDING status)
- ✅ Notification sending works
- ⚠️ Dashboard query may not find allocations in some cases
- ✅ Accept/reject functionality works

**Next Steps:**
1. Verify database has PENDING allocations
2. Check if `officer_id` matches logged-in user
3. Verify dashboard query is working correctly
4. Add debugging to track flow

