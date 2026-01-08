# Command Duration Feature - Implementation Flow

## Overview
This document outlines the complete implementation flow for the **Command Duration** feature in the HRD module. This feature allows HRD officers to search for officers based on how long they've been in their current command and add them to draft deployments.

---

## 1. Database & Model Analysis

### 1.1 Data Structure Review
- **Officer Model**: Contains `present_station` (command_id), `date_posted_to_station`
- **OfficerPosting Model**: Contains `posting_date`, `is_current`, `command_id`
- **Command Model**: Contains command information with zone relationship

### 1.2 Command Duration Calculation
- **Source**: Use `OfficerPosting` table where `is_current = true` and `posting_date` is the date posted to command
- **Formula**: `Current Date - posting_date` from current posting record
- **Display Format**: "X Years Y Months" (e.g., "3 Years 5 Months")

### 1.3 Officer Status Fields
- `suspended` (boolean)
- `dismissed` (boolean)
- `ongoing_investigation` (boolean)
- `interdicted` (boolean)
- `is_active` (boolean)

---

## 2. Route Structure

### 2.1 Routes to Add (in `routes/web.php` under HRD group)
```php
// Command Duration Routes
Route::prefix('command-duration')->name('command-duration.')->group(function () {
    Route::get('/', [CommandDurationController::class, 'index'])->name('index');
    Route::post('/search', [CommandDurationController::class, 'search'])->name('search');
    Route::post('/add-to-draft', [CommandDurationController::class, 'addToDraft'])->name('add-to-draft');
    Route::get('/print', [CommandDurationController::class, 'print'])->name('print');
});
```

---

## 3. Controller Implementation

### 3.1 Create Controller: `app/Http/Controllers/CommandDurationController.php`

#### 3.1.1 `index()` Method
**Purpose**: Display the search/filter form

**Steps**:
1. Get all zones for dropdown
2. Get all commands for dropdown (filtered by selected zone if provided)
3. Get unique ranks for dropdown
4. Get unique cadres for dropdown
5. Get unique qualifications for dropdown
6. Return view with filter options

**View**: `resources/views/dashboards/hrd/command-duration/index.blade.php`

#### 3.1.2 `search()` Method
**Purpose**: Execute search with filters and return results

**Filter Logic**:
- **Mandatory Filters**:
  - Zone (required)
  - Command (required)
- **Optional Filters**:
  - Rank
  - Cadre
  - Sex
  - Qualification
  - Command Duration (0-10+ years)

**Query Building**:
```php
$query = Officer::with(['presentStation.zone', 'currentPosting'])
    ->where('present_station', $commandId)
    ->whereHas('currentPosting', function($q) use ($commandId) {
        $q->where('command_id', $commandId)
          ->where('is_current', true);
    });

// Apply optional filters
if ($rank) { /* filter by rank */ }
if ($cadre) { /* filter by cadre */ }
if ($sex) { /* filter by sex */ }
if ($qualification) { /* filter by qualification */ }

// Command Duration Filter
if ($durationYears !== null) {
    $query->whereHas('currentPosting', function($q) use ($durationYears) {
        $dateThreshold = now()->subYears($durationYears);
        if ($durationYears == 10) {
            // 10+ years: >= 10 years
            $q->where('posting_date', '<=', $dateThreshold);
        } else {
            // Exact year: between X and X+1 years
            $nextYear = now()->subYears($durationYears + 1);
            $q->where('posting_date', '<=', $dateThreshold)
              ->where('posting_date', '>', $nextYear);
        }
    });
}
```

**Calculate Duration for Each Officer**:
```php
$officers->map(function($officer) {
    $posting = $officer->currentPosting;
    if ($posting && $posting->posting_date) {
        $diff = $posting->posting_date->diff(now());
        $officer->duration_years = $diff->y;
        $officer->duration_months = $diff->m;
        $officer->duration_display = "{$diff->y} Years {$diff->m} Months";
        $officer->date_posted_to_command = $posting->posting_date;
    }
    return $officer;
});
```

**Status Determination**:
```php
$officer->current_status = $this->getOfficerStatus($officer);
$officer->is_eligible_for_movement = $this->isEligibleForMovement($officer);
$officer->is_in_draft = $this->isInDraft($officer->id);
```

**Helper Methods**:
- `getOfficerStatus()`: Returns "Active", "Suspended", "Under Investigation", "Dismissed", "Interdicted"
- `isEligibleForMovement()`: Returns false if suspended, dismissed, or under investigation
- `isInDraft()`: Checks if officer is in an active draft deployment

#### 3.1.3 `addToDraft()` Method
**Purpose**: Add selected officers to draft deployment

**Steps**:
1. Validate selected officer IDs
2. Check if officers are eligible for movement
3. Get or create draft deployment (similar to ManningRequestController)
4. Add officers to draft (create ManningDeploymentAssignment records)
5. Return success response with redirect to draft page

**Note**: Reuse existing draft deployment system from ManningRequestController

#### 3.1.4 `print()` Method
**Purpose**: Generate printable report of search results

**Steps**:
1. Accept search parameters (zone, command, filters)
2. Execute same search logic as `search()` method
3. Format data for print view
4. Return print view with officer list

**View**: `resources/views/prints/command-duration.blade.php`

---

## 4. View Implementation

### 4.1 Main Search Page: `resources/views/dashboards/hrd/command-duration/index.blade.php`

#### 4.1.1 Layout Structure
- Extends `layouts.app`
- Breadcrumbs: HRD > Command Duration
- Page title: "Command Duration"

#### 4.1.2 Filter Form
**Form Fields**:
- **Zone** (Dropdown - Required)
  - Load commands dynamically based on selected zone
- **Command** (Dropdown - Required)
  - Populated based on selected zone
- **Rank** (Dropdown - Optional)
- **Cadre** (Dropdown - Optional)
- **Sex** (Dropdown - Optional: Male, Female, Any)
- **Qualification** (Dropdown - Optional)
- **Command Duration** (Dropdown - Optional)
  - Options: 0 Years, 1 Year, 2 Years, ..., 9 Years, 10+ Years

**Form Actions**:
- "Search" button (triggers search)
- "Reset" button (clears all filters)

#### 4.1.3 Search Results Section
**Table Columns**:
- Checkbox (for selection)
- Service Number
- Full Name
- Rank
- Command
- Date Posted to Command
- Duration in Command (Years & Months)
- Current Status (with badge)
- Actions

**Status Badges**:
- Active: Green badge
- Suspended: Red badge
- Under Investigation: Orange badge
- Dismissed: Red badge
- Interdicted: Red badge

**Row Styling**:
- Disabled officers (not eligible): Grayed out with disabled checkbox
- Officers in draft: Warning badge "In Draft"

**Action Buttons**:
- "Add Selected to Draft" button (appears when officers are selected)
- "Print Results" button (always visible)

#### 4.1.4 JavaScript Functionality
- Dynamic command loading based on zone selection
- Checkbox selection management
- Form validation (zone and command required)
- AJAX search (optional - can be form submit)
- Select all/none functionality

### 4.2 Print View: `resources/views/prints/command-duration.blade.php`

**Layout**:
- Print-optimized layout
- Header with search criteria
- Table with all officer details
- Footer with print date and page numbers

---

## 5. Integration with Existing Draft System

### 5.1 Reuse ManningDeployment System
- Use existing `ManningDeployment` model with status 'DRAFT'
- Use existing `ManningDeploymentAssignment` model
- Reuse draft management views and routes

### 5.2 Draft Assignment Creation
When adding officers from Command Duration search:
- Create `ManningDeploymentAssignment` records
- Set `manning_request_id` to NULL (since this is not from a manning request)
- Set `officer_id`, `from_command_id`, `to_command_id`
- Link to active draft deployment

### 5.3 Draft View Integration
- Officers added from Command Duration should appear in draft deployment view
- Should be distinguishable (maybe with a badge or note)
- Can be removed/swapped like other draft assignments

---

## 6. Access Control

### 6.1 Middleware
- Route middleware: `role:HRD` (already applied to HRD group)
- Additional check: Ensure user has HRD role or Super Admin role

### 6.2 Controller Authorization
```php
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('role:HRD|Super Admin');
}
```

---

## 7. Helper Methods & Services

### 7.1 Officer Status Helper
```php
private function getOfficerStatus($officer): string
{
    if ($officer->dismissed) return 'Dismissed';
    if ($officer->suspended) return 'Suspended';
    if ($officer->interdicted) return 'Interdicted';
    if ($officer->ongoing_investigation) return 'Under Investigation';
    return 'Active';
}
```

### 7.2 Eligibility Check
```php
private function isEligibleForMovement($officer): bool
{
    return !$officer->suspended 
        && !$officer->dismissed 
        && !$officer->ongoing_investigation
        && !$officer->interdicted
        && $officer->is_active;
}
```

### 7.3 Draft Check
```php
private function isInDraft($officerId): bool
{
    return ManningDeploymentAssignment::where('officer_id', $officerId)
        ->whereHas('deployment', function($q) {
            $q->where('status', 'DRAFT');
        })
        ->exists();
}
```

### 7.4 Duration Calculation
```php
private function calculateDuration($postingDate): array
{
    if (!$postingDate) {
        return ['years' => 0, 'months' => 0, 'display' => 'N/A'];
    }
    
    $diff = $postingDate->diff(now());
    return [
        'years' => $diff->y,
        'months' => $diff->m,
        'display' => "{$diff->y} Years {$diff->m} Months"
    ];
}
```

---

## 8. Menu Integration

### 8.1 Add to HRD Dashboard Menu
**File**: `resources/views/dashboards/hrd/dashboard.blade.php`

**Add Menu Item**:
```php
<a href="{{ route('hrd.command-duration.index') }}" class="menu-item">
    <i class="ki-filled ki-time"></i>
    <span>Command Duration</span>
</a>
```

---

## 9. Implementation Steps Checklist

### Phase 1: Database & Models
- [ ] Verify OfficerPosting model has required fields
- [ ] Verify relationships are properly set up
- [ ] Test command duration calculation logic

### Phase 2: Routes & Controller
- [ ] Create CommandDurationController
- [ ] Add routes to web.php
- [ ] Implement index() method
- [ ] Implement search() method with all filters
- [ ] Implement addToDraft() method
- [ ] Implement print() method

### Phase 3: Views
- [ ] Create index.blade.php with filter form
- [ ] Create search results table
- [ ] Add JavaScript for dynamic command loading
- [ ] Add checkbox selection functionality
- [ ] Create print.blade.php view
- [ ] Style status badges and disabled rows

### Phase 4: Integration
- [ ] Integrate with draft deployment system
- [ ] Test adding officers to draft
- [ ] Verify officers appear in draft view
- [ ] Test remove/swap functionality in draft

### Phase 5: Access Control & Menu
- [ ] Add menu item to HRD dashboard
- [ ] Test access control (HRD only)
- [ ] Test Super Admin access

### Phase 6: Testing
- [ ] Test all filter combinations
- [ ] Test command duration filter (0-10+ years)
- [ ] Test with officers in different statuses
- [ ] Test with officers already in draft
- [ ] Test print functionality
- [ ] Test adding to draft
- [ ] Test edge cases (no results, invalid filters, etc.)

---

## 10. Edge Cases & Validation

### 10.1 Validation Rules
- Zone and Command are required
- Command must belong to selected Zone
- Duration filter must be valid (0-10+)
- Selected officers must be eligible for movement

### 10.2 Edge Cases
- Officer with no current posting (show "N/A" for duration)
- Officer with posting_date in future (shouldn't happen, but handle gracefully)
- Multiple officers with same duration
- Empty search results
- Officers already in draft (show warning, allow selection but notify)

### 10.3 Error Handling
- Invalid zone/command combination
- No draft deployment exists (create one)
- Officer already in draft (show message but allow)
- Officer not eligible (disable selection, show reason)

---

## 11. UI/UX Considerations

### 11.1 User Experience
- Show loading state during search
- Display result count
- Show "No results" message when applicable
- Disable form submission until required fields are filled
- Clear visual distinction between eligible and ineligible officers

### 11.2 Responsive Design
- Mobile-friendly filter form
- Responsive table (consider card view for mobile)
- Print view optimized for A4 paper

### 11.3 Accessibility
- Proper form labels
- Keyboard navigation support
- Screen reader friendly status indicators

---

## 12. Future Enhancements (Optional)

- Export to Excel/CSV
- Save search criteria as favorites
- Bulk operations (add all eligible officers)
- Advanced filters (date range, multiple commands)
- Command duration statistics/charts
- Email notification when officers added to draft

---

## 13. Testing Scenarios

### 13.1 Search Scenarios
1. Search with only mandatory filters (Zone + Command)
2. Search with all filters
3. Search with duration filter (test each option: 0, 1, 2, ..., 10+)
4. Search with no results
5. Search with invalid zone/command combination

### 13.2 Draft Scenarios
1. Add single officer to draft
2. Add multiple officers to draft
3. Add officer already in draft (should show warning)
4. Add ineligible officer (should be prevented)
5. Verify officers appear in draft view
6. Remove officer from draft
7. Swap officer in draft

### 13.3 Status Scenarios
1. Active officer (should be selectable)
2. Suspended officer (should be disabled)
3. Officer under investigation (should be disabled)
4. Dismissed officer (should be disabled)
5. Officer in draft (should show warning badge)

### 13.4 Print Scenarios
1. Print with all filters applied
2. Print with no results
3. Print with many results (test pagination if needed)
4. Verify print layout and formatting

---

## 14. Code Structure Summary

```
app/Http/Controllers/
  └── CommandDurationController.php

resources/views/dashboards/hrd/command-duration/
  └── index.blade.php

resources/views/prints/
  └── command-duration.blade.php

routes/
  └── web.php (add routes)
```

---

## 15. Dependencies

- Existing ManningDeployment system
- Existing OfficerPosting model
- Existing Command/Zone models
- Existing draft deployment functionality
- Print functionality (similar to existing print views)

---

## Conclusion

This implementation flow provides a complete roadmap for building the Command Duration feature. The feature integrates seamlessly with the existing draft deployment system and follows the same patterns used in other HRD features like Manning Requests.

**Key Points**:
- Reuse existing draft deployment infrastructure
- Calculate duration from OfficerPosting.currentPosting.posting_date
- Filter officers based on eligibility status
- Integrate with existing print system
- Follow existing HRD UI patterns

