# Zonal Posting Implementation Flow

## Overview
This document outlines the implementation flow for **Zonal Posting** functionality, allowing Zone Coordinators to manage officer postings within their zone, following manning level and command duration rules, with a ceiling of IC (GL 07) and below.

## ⚠️ IMPORTANT: Flow Matches HRD Exactly

**The Zone Coordinator posting flow is IDENTICAL to HRD posting flow**, with only these modifications:

1. **Filtering Restrictions**:
   - Only commands within Zone Coordinator's zone are visible/selectable
   - Only officers at GL 07 and below are visible/selectable
   - Only Manning Requests from zone commands are visible

2. **Additional Validations** (NEW requirements for zonal posting):
   - **Manning Level Check**: Source and destination commands must meet manning requirements
   - **Command Duration Check**: Officer must have completed minimum time at current command
   - These validations are NOT part of HRD flow (HRD doesn't enforce these)

3. **Workflow Process**: 
   - **EXACTLY the same** as HRD:
     - Create order → Publish → Create OfficerPosting → Release letter → Acceptance → Transfer
     - Uses same `PostingWorkflowService`
     - Same notification system
     - Same Staff Officer workflow

**In summary**: Zone Coordinator uses the exact same controllers, services, and workflow as HRD, but with zone/rank filtering and additional validation checks.

---

## 1. Key Requirements

### 1.1 Scope Limitations
- **Zone Restriction**: Zone Coordinator can only manage postings for commands within their assigned zone
- **Rank Ceiling**: Only officers at **IC (GL 07) and below** can be posted through this system
- **Rules of Operation**: All postings must follow:
  - **Manning Level Rules**: Commands must meet minimum manning requirements
  - **Command Duration Rules**: Officers must have served minimum time at their current command

### 1.2 Functionalities
Zone Coordinator should have access to:
1. **Staff Orders** (for their zone, GL 07 and below)
2. **Movement Orders** (for their zone, GL 07 and below)
3. **Command Duration** (to search officers based on time at station)

### 1.3 Staff Officer Integration
- Staff Officers can request postings through this system
- Requests must meet "Rules of Operation" (manning level + command duration)
- Zone Coordinator processes these requests

---

## 2. Complete Flow Breakdown

### 2.1 Staff Order Flow (Zone Coordinator)

#### Step 1: Zone Coordinator Creates Staff Order
1. Zone Coordinator navigates to "Staff Orders" → "Create Order"
2. System filters:
   - **Commands**: Only shows commands within Zone Coordinator's zone
   - **Officers**: Only shows officers who:
     - Are in commands within the zone
     - Have GL 07 or below (`salary_grade_level <= GL07`)
     - Are active and eligible (not suspended, dismissed, interdicted, under investigation)
3. Zone Coordinator selects:
   - Officer (from filtered list)
   - From Command (must be in their zone)
   - To Command (must be in their zone)
   - Effective date
   - Order type (POSTING, TRANSFER, etc.)
   - Description

#### Step 2: Validation - Rules of Operation
Before creating the order, system checks:

**A. Manning Level Check:**
- Check if the **source command** (from_command) will still meet minimum manning requirements after losing this officer
- Check if the **destination command** (to_command) can accommodate this officer without exceeding maximum manning
- If either check fails, show error: "This posting violates manning level requirements"

**B. Command Duration Check:**
- Check if officer has served **minimum required time** at their current command
- Minimum duration is typically based on rank/GL level:
  - GL 07 (IC): Minimum X months
  - GL 06 (AIC): Minimum Y months
  - GL 05 and below: Minimum Z months
- If officer hasn't served minimum time, show error: "Officer has not completed minimum command duration"

**C. Rank Ceiling Check:**
- Verify officer's `salary_grade_level <= GL07`
- If above GL 07, show error: "Only officers at IC (GL 07) and below can be posted through zonal posting"

#### Step 3: Order Creation
- If all checks pass, create Staff Order
- **If order status is PUBLISHED**: System automatically calls `PostingWorkflowService::processStaffOrder()`
- This creates `OfficerPosting` record with:
  - `is_current = false` (becomes true after acceptance)
  - `release_letter_printed = false`
  - `accepted_by_new_command = false`
  - `posting_date = effective_date`
- Notifies FROM command Staff Officers about pending release letter

#### Step 4: Workflow (EXACTLY Same as HRD Staff Orders)
1. **Release Letter Stage**:
   - Staff Officer at FROM command sees pending release letter
   - Staff Officer prints release letter → sets `release_letter_printed = true`
   - Officer is notified of transfer
   
2. **Acceptance Stage**:
   - Staff Officer at TO command sees pending arrival
   - Staff Officer accepts officer → sets:
     - `accepted_by_new_command = true`
     - `accepted_at = now()`
     - `is_current = true`
     - Updates officer's `present_station` to new command
   - Officer is notified of acceptance
   
3. **Transfer Complete**:
   - Old posting marked as `is_current = false`
   - New posting marked as `is_current = true`
   - Officer's `present_station` updated

---

### 2.2 Movement Order Flow (Zone Coordinator)

#### Step 1: Zone Coordinator Creates Movement Order
1. Zone Coordinator navigates to "Movement Orders" → "Create Order"
2. System shows:
   - **Manning Requests**: Only from commands within their zone
   - **Officers**: Only GL 07 and below from zone commands
3. Zone Coordinator:
   - Links to a Manning Request (optional, but recommended)
   - Sets criteria: "Officers who have been at station for X months"
   - Creates Movement Order (status: DRAFT)

#### Step 2: View Eligible Officers
1. Zone Coordinator clicks "View Eligible Officers"
2. System filters officers who:
   - Are in commands within the zone
   - Have GL 07 or below
   - Have been at their current station for >= criteria months
   - Are active and eligible
   - Match manning request requirements (if linked):
     - Rank matches requested ranks
     - Sex matches (if specified)
     - Qualification matches (if specified)
   - **Meet Command Duration minimum** (additional check)
   - **Meet Manning Level requirements** (source command can spare them, destination can take them)

#### Step 3: Select and Post Officers
1. Zone Coordinator selects officers from eligible list
2. For each selected officer, system validates:
   - Manning level rules
   - Command duration rules
   - Rank ceiling (GL 07 and below)
3. If all validations pass:
   - Create `OfficerPosting` records
   - Link to Movement Order
   - Notify Staff Officers

#### Step 4: Publish Movement Order
1. Zone Coordinator reviews all postings
2. Clicks "Publish" to finalize
3. Movement Order status changes to PUBLISHED
4. System calls `PostingWorkflowService::processMovementOrder()`
5. This ensures all postings have correct workflow fields set
6. **Workflow begins (EXACTLY Same as HRD Movement Orders)**:
   - Release letter stage (FROM command Staff Officer prints)
   - Acceptance stage (TO command Staff Officer accepts)
   - Transfer complete (officer's `present_station` updated)

---

### 2.3 Command Duration Flow (Zone Coordinator)

#### Step 1: Search Officers by Command Duration
1. Zone Coordinator navigates to "Command Duration"
2. System shows filter form:
   - **Zone**: Pre-filled with Zone Coordinator's zone (read-only)
   - **Command**: Dropdown of commands in their zone
   - **Rank**: Filter by rank (optional)
   - **Cadre**: Filter by cadre (optional)
   - **Sex**: Filter by sex (optional)
   - **Qualification**: Filter by qualification (optional)
   - **Command Duration**: Filter by years at station (0-10+)

#### Step 2: Execute Search
1. Zone Coordinator selects filters and clicks "Search"
2. System returns officers who:
   - Are in the selected command (within zone)
   - Have GL 07 or below
   - Match optional filters (rank, cadre, sex, qualification)
   - Have been at station for the specified duration
   - Are active and eligible

#### Step 3: Display Results
Results table shows:
- Service Number
- Full Name
- Rank
- Current Command
- Date Posted to Command
- Duration in Command (Years & Months)
- Current Status (Active, Suspended, etc.)
- **Manning Level Status**: Can this officer be moved without violating manning?
- **Command Duration Status**: Has officer completed minimum duration?

#### Step 4: Add to Draft Deployment
1. Zone Coordinator selects officers
2. System validates each officer:
   - Manning level rules
   - Command duration rules
   - Rank ceiling
3. If valid, add to draft deployment
4. Zone Coordinator can then create Movement Order or Staff Order from draft

---

### 2.4 Staff Officer Request Flow

#### Step 1: Staff Officer Creates Manning Request
1. Staff Officer navigates to "Manning Level" → "Create Request"
2. Staff Officer specifies:
   - Ranks needed (only GL 07 and below)
   - Quantities needed
   - Sex requirements (optional)
   - Qualification requirements (optional)
   - Notes/justification
3. Staff Officer submits request

#### Step 2: Approval Workflow
1. Request goes to DC Admin for approval
2. DC Admin approves/rejects
3. If approved, request goes to Area Controller
4. Area Controller approves/rejects
5. If approved, request status becomes "APPROVED"

#### Step 3: Zone Coordinator Processes Request
1. Zone Coordinator sees approved Manning Request in their zone
2. Zone Coordinator can:
   - Create Movement Order linked to this request
   - Use Command Duration to find eligible officers
   - Match officers manually or automatically
3. All matching must follow:
   - Manning level rules
   - Command duration rules
   - Rank ceiling (GL 07 and below)

#### Step 4: Zone Coordinator Matches Officers
1. Zone Coordinator views eligible officers
2. System shows only officers who:
   - Are in zone commands
   - Have GL 07 or below
   - Meet rank/sex/qualification requirements
   - Meet manning level rules
   - Meet command duration rules
3. Zone Coordinator selects officers
4. System creates postings

---

## 3. Rules of Operation - Detailed

### 3.1 Manning Level Rules

**Purpose**: Ensure commands maintain adequate staffing levels

**Checks**:
1. **Source Command Check**:
   - Get current manning level of source command
   - Get minimum required manning for source command
   - Calculate: `current_manning - 1 >= minimum_required`
   - If false, officer cannot be moved

2. **Destination Command Check**:
   - Get current manning level of destination command
   - Get maximum allowed manning for destination command
   - Calculate: `current_manning + 1 <= maximum_allowed`
   - If false, officer cannot be moved

3. **Rank-Specific Manning**:
   - Check if source command has enough officers of this rank
   - Check if destination command needs officers of this rank
   - Both must be satisfied

**Implementation**:
- Use existing `ManningLevel` model/table
- Query current officer count per command
- Compare against minimum/maximum thresholds

### 3.2 Command Duration Rules

**Purpose**: Ensure officers serve minimum time at each command before being moved

**Minimum Duration by Rank/GL**:
- **GL 07 (IC)**: Minimum 24 months (2 years)
- **GL 06 (AIC)**: Minimum 18 months (1.5 years)
- **GL 05 and below**: Minimum 12 months (1 year)

**Calculation**:
- Get officer's current posting: `OfficerPosting` where `is_current = true`
- Get `posting_date` from current posting
- Calculate: `now() - posting_date >= minimum_duration`
- If false, officer cannot be moved

**Exceptions** (if needed):
- Emergency postings (with approval)
- Promotions (may override duration)
- Disciplinary transfers

### 3.3 Rank Ceiling (GL 07 and Below)

**Purpose**: Limit zonal posting authority to junior officers

**Check**:
```php
$officer->salary_grade_level <= 'GL07'
// OR
$officer->salary_grade_level == 'GL05' || 'GL06' || 'GL07'
// OR numeric check: <= 7
```

**Officers Above GL 07**:
- Cannot be posted through zonal posting system
- Must go through HRD posting system
- Zone Coordinator cannot see or select these officers

---

## 4. Database & Model Changes

### 4.1 No New Models Required
- Reuse existing models:
  - `StaffOrder`
  - `MovementOrder`
  - `OfficerPosting`
  - `ManningRequest`
  - `ManningDeployment`
  - `Command`
  - `Zone`
  - `Officer`

### 4.2 Possible New Fields (Optional)
- `StaffOrder.zone_id` - Track which zone created the order
- `MovementOrder.zone_id` - Track which zone created the order
- `OfficerPosting.created_by_role` - Track if created by HRD or Zone Coordinator

### 4.3 Manning Level Data
- Ensure `ManningLevel` table has:
  - `command_id`
  - `rank` (or `grade_level`)
  - `minimum_required`
  - `maximum_allowed`
  - `current_count` (calculated)

---

## 5. Controller Implementation

**Note**: Zone Coordinator can **reuse the existing HRD controllers** (`StaffOrderController`, `MovementOrderController`, `CommandDurationController`) with zone filtering and validation logic added. The existing `StaffOrderController` already has zone coordinator support partially implemented.

### 5.1 Zone Coordinator Staff Order Controller
**Option A**: Extend existing `StaffOrderController` (RECOMMENDED - already has zone coordinator support)
**Option B**: Create separate `app/Http/Controllers/ZoneCoordinator/StaffOrderController.php`

**Methods**:
- `index()` - List staff orders in zone
- `create()` - Show create form (filtered to zone + GL 07)
- `store()` - Create order (with validation)
- `show()` - View order details
- `edit()` - Edit order (if DRAFT)
- `update()` - Update order

**Key Validations in `store()`**:
```php
// 1. Zone check
if (!$this->isCommandInZone($fromCommandId) || !$this->isCommandInZone($toCommandId)) {
    return redirect()->back()->with('error', 'Commands must be in your zone');
}

// 2. Rank ceiling check
if (!$this->isOfficerGL07OrBelow($officerId)) {
    return redirect()->back()->with('error', 'Only officers at IC (GL 07) and below can be posted');
}

// 3. Manning level check
if (!$this->checkManningLevel($fromCommandId, $toCommandId, $officerId)) {
    return redirect()->back()->with('error', 'This posting violates manning level requirements');
}

// 4. Command duration check
if (!$this->checkCommandDuration($officerId)) {
    return redirect()->back()->with('error', 'Officer has not completed minimum command duration');
}
```

### 5.2 Zone Coordinator Movement Order Controller
**File**: `app/Http/Controllers/ZoneCoordinator/MovementOrderController.php`

**Methods**:
- `index()` - List movement orders in zone
- `create()` - Show create form
- `store()` - Create order
- `show()` - View order details
- `eligibleOfficers()` - Get eligible officers (with all filters)
- `postOfficers()` - Post selected officers
- `publish()` - Publish order

**Key Filters in `eligibleOfficers()`**:
```php
// 1. Zone filter
$zoneCommandIds = $this->getZoneCommandIds();
$query->whereIn('present_station', $zoneCommandIds);

// 2. Rank ceiling
$query->where(function($q) {
    $q->where('salary_grade_level', '<=', 'GL07')
      ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7");
});

// 3. Command duration (criteria)
$cutoffDate = Carbon::now()->subMonths($criteriaMonths);
$query->where('date_posted_to_station', '<=', $cutoffDate);

// 4. Minimum command duration check
$query->whereHas('currentPosting', function($q) use ($officer) {
    $minDuration = $this->getMinimumDurationForRank($officer->salary_grade_level);
    $minDate = Carbon::now()->subMonths($minDuration);
    $q->where('posting_date', '<=', $minDate);
});

// 5. Manning level check (for each officer)
// This is checked when selecting officers, not in query
```

### 5.3 Zone Coordinator Command Duration Controller
**File**: `app/Http/Controllers/ZoneCoordinator/CommandDurationController.php`

**Methods**:
- `index()` - Show search form
- `search()` - Execute search with filters
- `addToDraft()` - Add officers to draft deployment

**Key Filters in `search()`**:
```php
// 1. Zone filter (mandatory)
$zoneCommandIds = $this->getZoneCommandIds();
$query->whereIn('present_station', $zoneCommandIds);

// 2. Command filter (mandatory)
$query->where('present_station', $commandId);

// 3. Rank ceiling
$query->where(function($q) {
    $q->where('salary_grade_level', '<=', 'GL07')
      ->orWhereRaw("CAST(SUBSTRING(salary_grade_level, 3) AS UNSIGNED) <= 7");
});

// 4. Optional filters (rank, cadre, sex, qualification)
// 5. Command duration filter
// 6. Eligibility checks (not suspended, dismissed, etc.)
```

---

## 6. Helper Methods & Services

### 6.1 Zone Helper Methods
```php
private function getZoneCoordinatorZone()
{
    $user = auth()->user();
    $zoneCoordinatorRole = $user->roles()
        ->where('name', 'Zone Coordinator')
        ->wherePivot('is_active', true)
        ->first();
    
    if (!$zoneCoordinatorRole || !$zoneCoordinatorRole->pivot->command_id) {
        return null;
    }
    
    $command = Command::find($zoneCoordinatorRole->pivot->command_id);
    return $command ? $command->zone : null;
}

private function getZoneCommandIds()
{
    $zone = $this->getZoneCoordinatorZone();
    if (!$zone) {
        return [];
    }
    
    return Command::where('zone_id', $zone->id)
        ->where('is_active', true)
        ->pluck('id')
        ->toArray();
}

private function isCommandInZone($commandId)
{
    $zoneCommandIds = $this->getZoneCommandIds();
    return in_array($commandId, $zoneCommandIds);
}
```

### 6.2 Rank Ceiling Helper
```php
private function isOfficerGL07OrBelow($officerId)
{
    $officer = Officer::find($officerId);
    if (!$officer) {
        return false;
    }
    
    $gradeLevel = $officer->salary_grade_level;
    
    // Check various formats: GL07, GL 07, 07, 7
    if (preg_match('/GL?\s?0?7/i', $gradeLevel)) {
        return true; // GL 07
    }
    
    if (preg_match('/GL?\s?0?6/i', $gradeLevel)) {
        return true; // GL 06
    }
    
    if (preg_match('/GL?\s?0?[1-5]/i', $gradeLevel)) {
        return true; // GL 01-05
    }
    
    // Numeric check
    $numeric = (int) preg_replace('/[^0-9]/', '', $gradeLevel);
    return $numeric <= 7;
}
```

### 6.3 Manning Level Check Helper
```php
private function checkManningLevel($fromCommandId, $toCommandId, $officerId)
{
    $officer = Officer::find($officerId);
    $rank = $officer->substantive_rank;
    $gradeLevel = $officer->salary_grade_level;
    
    // Check source command
    $sourceManning = ManningLevel::where('command_id', $fromCommandId)
        ->where(function($q) use ($rank, $gradeLevel) {
            $q->where('rank', $rank)
              ->orWhere('grade_level', $gradeLevel);
        })
        ->first();
    
    if ($sourceManning) {
        $currentCount = Officer::where('present_station', $fromCommandId)
            ->where('substantive_rank', $rank)
            ->where('is_active', true)
            ->count();
        
        if (($currentCount - 1) < $sourceManning->minimum_required) {
            return false; // Source command will be under-manned
        }
    }
    
    // Check destination command
    $destManning = ManningLevel::where('command_id', $toCommandId)
        ->where(function($q) use ($rank, $gradeLevel) {
            $q->where('rank', $rank)
              ->orWhere('grade_level', $gradeLevel);
        })
        ->first();
    
    if ($destManning) {
        $currentCount = Officer::where('present_station', $toCommandId)
            ->where('substantive_rank', $rank)
            ->where('is_active', true)
            ->count();
        
        if (($currentCount + 1) > $destManning->maximum_allowed) {
            return false; // Destination command will be over-manned
        }
    }
    
    return true; // All checks passed
}
```

### 6.4 Command Duration Check Helper
```php
private function checkCommandDuration($officerId)
{
    $officer = Officer::find($officerId);
    $gradeLevel = $officer->salary_grade_level;
    
    // Get minimum duration for this rank/GL
    $minMonths = $this->getMinimumDurationForRank($gradeLevel);
    
    // Get current posting
    $currentPosting = OfficerPosting::where('officer_id', $officerId)
        ->where('is_current', true)
        ->first();
    
    if (!$currentPosting || !$currentPosting->posting_date) {
        return false; // No current posting found
    }
    
    // Calculate duration
    $postingDate = Carbon::parse($currentPosting->posting_date);
    $monthsAtStation = $postingDate->diffInMonths(now());
    
    return $monthsAtStation >= $minMonths;
}

private function getMinimumDurationForRank($gradeLevel)
{
    // GL 07 (IC): 24 months
    if (preg_match('/GL?\s?0?7/i', $gradeLevel) || preg_match('/^7$/', $gradeLevel)) {
        return 24;
    }
    
    // GL 06 (AIC): 18 months
    if (preg_match('/GL?\s?0?6/i', $gradeLevel) || preg_match('/^6$/', $gradeLevel)) {
        return 18;
    }
    
    // GL 05 and below: 12 months
    return 12;
}
```

---

## 7. Route Structure

### 7.1 Zone Coordinator Routes
Add to `routes/web.php`:

```php
// Zone Coordinator Routes
Route::prefix('zone-coordinator')->name('zone-coordinator.')->middleware('role:Zone Coordinator')->group(function () {
    
    // Existing routes...
    
    // Staff Orders
    Route::get('/staff-orders', [ZoneCoordinator\StaffOrderController::class, 'index'])->name('staff-orders');
    Route::get('/staff-orders/create', [ZoneCoordinator\StaffOrderController::class, 'create'])->name('staff-orders.create');
    Route::post('/staff-orders', [ZoneCoordinator\StaffOrderController::class, 'store'])->name('staff-orders.store');
    Route::get('/staff-orders/{id}', [ZoneCoordinator\StaffOrderController::class, 'show'])->name('staff-orders.show');
    Route::get('/staff-orders/{id}/edit', [ZoneCoordinator\StaffOrderController::class, 'edit'])->name('staff-orders.edit');
    Route::put('/staff-orders/{id}', [ZoneCoordinator\StaffOrderController::class, 'update'])->name('staff-orders.update');
    
    // Movement Orders
    Route::get('/movement-orders', [ZoneCoordinator\MovementOrderController::class, 'index'])->name('movement-orders');
    Route::get('/movement-orders/create', [ZoneCoordinator\MovementOrderController::class, 'create'])->name('movement-orders.create');
    Route::post('/movement-orders', [ZoneCoordinator\MovementOrderController::class, 'store'])->name('movement-orders.store');
    Route::get('/movement-orders/{id}', [ZoneCoordinator\MovementOrderController::class, 'show'])->name('movement-orders.show');
    Route::get('/movement-orders/{id}/edit', [ZoneCoordinator\MovementOrderController::class, 'edit'])->name('movement-orders.edit');
    Route::put('/movement-orders/{id}', [ZoneCoordinator\MovementOrderController::class, 'update'])->name('movement-orders.update');
    Route::get('/movement-orders/{id}/eligible-officers', [ZoneCoordinator\MovementOrderController::class, 'eligibleOfficers'])->name('movement-orders.eligible-officers');
    Route::post('/movement-orders/{id}/post-officers', [ZoneCoordinator\MovementOrderController::class, 'postOfficers'])->name('movement-orders.post-officers');
    Route::post('/movement-orders/{id}/publish', [ZoneCoordinator\MovementOrderController::class, 'publish'])->name('movement-orders.publish');
    
    // Command Duration
    Route::get('/command-duration', [ZoneCoordinator\CommandDurationController::class, 'index'])->name('command-duration.index');
    Route::post('/command-duration/search', [ZoneCoordinator\CommandDurationController::class, 'search'])->name('command-duration.search');
    Route::post('/command-duration/add-to-draft', [ZoneCoordinator\CommandDurationController::class, 'addToDraft'])->name('command-duration.add-to-draft');
    Route::get('/command-duration/print', [ZoneCoordinator\CommandDurationController::class, 'print'])->name('command-duration.print');
});
```

---

## 8. View Implementation

### 8.1 Staff Order Views
- `resources/views/dashboards/zone-coordinator/staff-orders/index.blade.php`
- `resources/views/dashboards/zone-coordinator/staff-orders/create.blade.php`
- `resources/views/dashboards/zone-coordinator/staff-orders/show.blade.php`
- `resources/views/dashboards/zone-coordinator/staff-orders/edit.blade.php`

**Key Features**:
- Show zone name at top
- Filter officers to zone + GL 07 and below
- Show validation errors for manning level and command duration
- Display officer's current command duration
- Display manning level status for source/destination commands

### 8.2 Movement Order Views
- `resources/views/dashboards/zone-coordinator/movement-orders/index.blade.php`
- `resources/views/dashboards/zone-coordinator/movement-orders/create.blade.php`
- `resources/views/dashboards/zone-coordinator/movement-orders/show.blade.php`
- `resources/views/dashboards/zone-coordinator/movement-orders/eligible-officers.blade.php`

**Key Features**:
- Filter manning requests to zone only
- Show eligible officers with all validation statuses
- Display manning level and command duration status for each officer
- Allow bulk selection with validation

### 8.3 Command Duration Views
- `resources/views/dashboards/zone-coordinator/command-duration/index.blade.php`

**Key Features**:
- Zone pre-filled and read-only
- Command dropdown (zone commands only)
- Search results show:
  - Command duration
  - Manning level status (can move? yes/no)
  - Command duration status (completed minimum? yes/no)
- Disable selection for officers who don't meet rules

---

## 9. Menu Integration

### 9.1 Update Sidebar
**File**: `resources/views/components/sidebar.blade.php`

Add to Zone Coordinator menu:
```php
case 'Zone Coordinator':
    $menuItems = [
        // Existing items...
        
        // Postings Section
        [
            'icon' => 'ki-filled ki-people',
            'title' => 'Postings',
            'children' => [
                ['title' => 'Staff Orders', 'href' => route('zone-coordinator.staff-orders')],
                ['title' => 'Movement Orders', 'href' => route('zone-coordinator.movement-orders')],
                ['title' => 'Command Duration', 'href' => route('zone-coordinator.command-duration.index')],
            ],
        ],
    ];
```

---

## 10. Implementation Checklist

### Phase 1: Helper Methods & Services
- [ ] Create zone helper methods
- [ ] Create rank ceiling check method
- [ ] Create manning level check method
- [ ] Create command duration check method
- [ ] Create minimum duration lookup method

### Phase 2: Staff Order Controller
- [ ] Create `ZoneCoordinator/StaffOrderController.php`
- [ ] Implement `index()` with zone filtering
- [ ] Implement `create()` with officer/command filtering
- [ ] Implement `store()` with all validations
- [ ] Implement `show()`, `edit()`, `update()`

### Phase 3: Movement Order Controller
- [ ] Create `ZoneCoordinator/MovementOrderController.php`
- [ ] Implement `index()` with zone filtering
- [ ] Implement `create()` with manning request filtering
- [ ] Implement `store()`
- [ ] Implement `eligibleOfficers()` with all filters
- [ ] Implement `postOfficers()` with validations
- [ ] Implement `publish()`

### Phase 4: Command Duration Controller
- [ ] Create `ZoneCoordinator/CommandDurationController.php`
- [ ] Implement `index()` with zone pre-filled
- [ ] Implement `search()` with all filters
- [ ] Implement `addToDraft()`

### Phase 5: Views
- [ ] Create Staff Order views
- [ ] Create Movement Order views
- [ ] Create Command Duration view
- [ ] Add validation error displays
- [ ] Add status indicators (manning level, command duration)

### Phase 6: Routes & Menu
- [ ] Add routes to `web.php`
- [ ] Update sidebar menu
- [ ] Test route access control

### Phase 7: Testing
- [ ] Test zone filtering (only zone commands visible)
- [ ] Test rank ceiling (only GL 07 and below)
- [ ] Test manning level validation
- [ ] Test command duration validation
- [ ] Test Staff Order creation
- [ ] Test Movement Order creation
- [ ] Test Command Duration search
- [ ] Test Staff Officer request workflow

---

## 11. Key Differences from HRD Posting

| Feature | HRD Posting | Zonal Posting |
|---------|-------------|---------------|
| **Scope** | All commands, all zones | Zone commands only |
| **Rank Limit** | All ranks | GL 07 and below only |
| **Manning Level Validation** | Not enforced (optional) | **Mandatory validation** |
| **Command Duration Validation** | Not enforced (optional) | **Mandatory validation** |
| **Workflow Process** | Same | **EXACTLY Same** |
| **Staff Officer Requests** | Processed by HRD | Processed by Zone Coordinator (if in zone) |
| **Order Creation** | Same process | Same process (with additional validations) |
| **Release Letter** | Same workflow | Same workflow |
| **Acceptance** | Same workflow | Same workflow |
| **Transfer Completion** | Same workflow | Same workflow |

**Important**: The workflow is IDENTICAL to HRD. The only differences are:
1. **Filtering**: Zone + GL 07 and below
2. **Additional Validations**: Manning level + Command duration (these are NEW requirements for zonal posting, not existing HRD requirements)

---

## 12. Summary

**What We're Building**:
1. Zone Coordinator can create Staff Orders for their zone (GL 07 and below)
2. Zone Coordinator can create Movement Orders for their zone (GL 07 and below)
3. Zone Coordinator can search officers by Command Duration in their zone
4. All postings must follow manning level and command duration rules
5. Staff Officers can request postings, which Zone Coordinator processes

**Key Validations**:
- Zone restriction (commands must be in coordinator's zone)
- Rank ceiling (GL 07 and below only)
- Manning level (source and destination commands must meet requirements)
- Command duration (officer must have served minimum time)

**Workflow**:
- **EXACTLY the same as HRD posting workflow**:
  1. Create order (DRAFT or PUBLISHED)
  2. If PUBLISHED, `PostingWorkflowService` creates pending `OfficerPosting` records
  3. FROM command Staff Officer prints release letter
  4. TO command Staff Officer accepts officer
  5. Officer's `present_station` updated, posting marked as current
- **Additional validations** (manning level + command duration) are NEW requirements for zonal posting
- **Additional restrictions** (zone + GL 07 filtering) limit scope but don't change workflow

---

## Conclusion

This implementation provides Zone Coordinators with controlled posting authority within their zone, ensuring proper staffing levels and officer development through command duration requirements, while maintaining oversight through rank limitations and rule enforcement.

