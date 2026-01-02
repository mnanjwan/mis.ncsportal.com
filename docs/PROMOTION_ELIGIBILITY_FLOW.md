# Promotion Eligibility Flow - Complete Documentation

## Overview
The Promotion Eligibility system identifies officers who are eligible for promotion based on their years in their current rank. It follows a workflow similar to the Retirement List system, with smart year suggestions when no eligible officers are found.

---

## Architecture & Data Models

### 1. **PromotionEligibilityList**
- **Purpose**: Container for a promotion eligibility list for a specific year
- **Fields**:
  - `year`: The year for which the list is generated
  - `generated_by`: User ID who created the list
  - `status`: Status of the list (e.g., 'DRAFT')
- **Relationships**:
  - `items()`: Has many `PromotionEligibilityListItem`
  - `generatedBy()`: Belongs to `User`

### 2. **PromotionEligibilityListItem**
- **Purpose**: Individual officer entry in an eligibility list
- **Fields**:
  - `eligibility_list_id`: Foreign key to the list
  - `officer_id`: Foreign key to the officer
  - `serial_number`: Position in the list
  - `current_rank`: Officer's current rank (snapshot)
  - `years_in_rank`: Calculated years in current rank
  - `date_of_first_appointment`: Officer's first appointment date
  - `date_of_present_appointment`: When officer got current rank
  - `state`: Officer's state of origin
  - `date_of_birth`: Officer's date of birth
  - `excluded_reason`: If officer was later excluded, reason stored here

### 3. **PromotionEligibilityCriterion**
- **Purpose**: Defines promotion requirements for each rank
- **Fields**:
  - `rank`: Rank abbreviation (e.g., 'IC', 'SC', 'DC')
  - `years_in_rank_required`: Minimum years needed in rank before promotion
  - `is_active`: Whether this criterion is currently active
  - `created_by`: User who created the criterion

---

## Complete Flow

### **Step 1: User Access & Navigation**
```
HRD User → Dashboard → Promotion Eligibility → Create List
Route: GET /hrd/promotion-eligibility/create
Controller: PromotionController::createEligibilityList()
View: forms/promotion/create-eligibility-list.blade.php
```

### **Step 2: Form Submission**
```
User enters a year → Submits form
Route: POST /hrd/promotion-eligibility
Controller: PromotionController::storeEligibilityList()
```

### **Step 3: List Creation**
The system creates a new `PromotionEligibilityList` record:
```php
$list = PromotionEligibilityList::create([
    'year' => $validated['year'],
    'generated_by' => auth()->id(),
    'status' => 'DRAFT',
]);
```

### **Step 4: Load Promotion Criteria**
The system loads all active promotion criteria:
```php
$criteria = PromotionEligibilityCriterion::where('is_active', true)
    ->get()
    ->keyBy('rank');
```
This creates a lookup map: `['IC' => Criterion, 'SC' => Criterion, ...]`

### **Step 5: Filter Eligible Officers**
The system queries for active officers who meet basic eligibility:

**Included Officers:**
- `is_active = true`
- `is_deceased = false`
- `interdicted = false`
- `suspended = false`
- `ongoing_investigation = false`
- `dismissed = false`
- Has `substantive_rank` (not null)
- Has `date_of_birth` (not null)
- Has `date_of_first_appointment` (not null)
- Has `date_of_present_appointment` (not null)

**Excluded Officers:**
- Deceased officers
- Interdicted officers
- Suspended officers
- Officers under investigation
- Dismissed officers
- Officers missing required data fields

### **Step 6: Rank Normalization**
For each officer, the system normalizes their rank:
```php
$normalizedRank = $this->normalizeRankToAbbreviation($currentRank);
```

**Why?** Officers may have ranks stored as:
- Full names: "Inspector of Customs (IC) GL07"
- Abbreviations: "IC"
- Variations: "Inspector"

The system converts all to standard abbreviations (IC, SC, DC, etc.) to match criteria.

**Rank Normalization Process:**
1. Check if already an abbreviation → return as-is
2. Check exact match in mapping table
3. Try partial matching (case-insensitive)
4. Extract from parentheses if present (e.g., "(IC)" → "IC")
5. Return original if no match found

### **Step 7: Eligibility Calculation**
For each officer:

1. **Get their rank's criteria:**
   ```php
   if (!$criteria->has($normalizedRank)) {
       continue; // Skip if no criteria for this rank
   }
   $criterion = $criteria->get($normalizedRank);
   ```

2. **Calculate years in rank:**
   ```php
   $yearsInRank = Carbon::parse($officer->date_of_present_appointment)
       ->diffInYears(now());
   ```

3. **Check eligibility:**
   ```php
   if ($yearsInRank >= $criterion->years_in_rank_required) {
       $eligibleOfficers->push($officer);
   }
   ```

**Example:**
- Officer appointed to current rank: January 1, 2020
- Today: January 1, 2024
- Years in rank: 4 years
- Required for rank: 5 years
- Result: **Not eligible** (4 < 5)

### **Step 8A: If Officers Found (Success Path)**

If `$eligibleOfficers->count() > 0`:

1. **Limit to 100 officers** (for processing efficiency):
   ```php
   $eligibleOfficers = $eligibleOfficers->take(100);
   ```

2. **Create list items:**
   For each eligible officer, create a `PromotionEligibilityListItem`:
   ```php
   PromotionEligibilityListItem::create([
       'eligibility_list_id' => $list->id,
       'officer_id' => $officer->id,
       'serial_number' => $serialNumber++,
       'current_rank' => $officer->substantive_rank,
       'years_in_rank' => round($yearsInRank, 2),
       'date_of_first_appointment' => $officer->date_of_first_appointment,
       'date_of_present_appointment' => $officer->date_of_present_appointment,
       'state' => $officer->state_of_origin,
       'date_of_birth' => $officer->date_of_birth,
   ]);
   ```

3. **Redirect with success:**
   ```
   Route: GET /hrd/promotion-eligibility
   Message: "Promotion eligibility list created successfully with X officers!"
   ```

### **Step 8B: If No Officers Found (With Year Suggestions)**

If `$eligibleOfficers->count() === 0`:

1. **Delete the empty list:**
   ```php
   $list->delete();
   ```

2. **Calculate future eligibility years:**
   ```php
   $futureEligibilityYears = $this->calculateFutureEligibilityYears(
       $allOfficers, 
       $criteria
   );
   ```

3. **Build suggestion message:**
   - If future years found: "Officers will become eligible in: 2025, 2026, 2027..."
   - If no future years: "No officers will become eligible in the near future. Check promotion criteria configuration."

4. **Redirect back with error and suggestions:**
   ```
   Route: POST /hrd/promotion-eligibility (back to form)
   Message: Error with year suggestions
   Input: Year is preserved in form
   ```

### **Step 9: Future Eligibility Calculation**

The `calculateFutureEligibilityYears()` method:

1. **Iterates through all officers** (same filtered set)

2. **For each officer:**
   - Normalize their rank
   - Check if criteria exists for that rank
   - Skip if already eligible (looking for future only)
   - Calculate: `eligibilityDate = dateOfAppointment + yearsRequired`
   - Extract year from eligibility date
   - Include if year is between current year and current year + 20

3. **Returns sorted, unique years:**
   ```php
   return $futureYears->unique()->sort()->values()->toArray();
   ```

**Example Calculation:**
- Officer appointed: January 1, 2022
- Required years: 5
- Eligibility date: January 1, 2027
- Eligibility year: **2027**

### **Step 10: View Lists**

**Index Page:**
```
Route: GET /hrd/promotion-eligibility
Controller: PromotionController::index()
View: dashboards/hrd/promotion-eligibility.blade.php
```

Shows paginated list of all eligibility lists with:
- Year
- Officers count
- Created date
- Actions (View, Delete)

**Sorting:** By year, officers_count, or created_at (asc/desc)

**Detail Page:**
```
Route: GET /hrd/promotion-eligibility/{id}
Controller: PromotionController::showEligibilityList()
View: dashboards/hrd/promotion-eligibility-list-show.blade.php
```

Shows full details of a specific list with all officers.

---

## Key Features

### 1. **Smart Year Suggestions**
When no officers are eligible, the system calculates and suggests future years when officers will become eligible, helping users find the right year to check.

### 2. **Rank Normalization**
Handles various rank formats automatically, ensuring officers match criteria regardless of how their rank is stored.

### 3. **Exclusion Rules**
Automatically excludes officers who shouldn't be considered for promotion:
- Deceased, interdicted, suspended, dismissed, under investigation
- Missing required data

### 4. **Year-Based Organization**
Each list is associated with a year, allowing organizations to track eligibility over time.

### 5. **Data Snapshot**
List items store snapshots of officer data at list creation time, preserving historical accuracy.

---

## Promotion Criteria Management

HRD can manage promotion criteria separately:

**Routes:**
- `GET /hrd/promotion-criteria` - List all criteria
- `GET /hrd/promotion-criteria/create` - Create new criterion
- `POST /hrd/promotion-criteria` - Store new criterion
- `GET /hrd/promotion-criteria/{id}/edit` - Edit criterion
- `PUT /hrd/promotion-criteria/{id}` - Update criterion

**Standard Ranks (with criteria):**
- DC, AC, CSC, SC, DSC
- ASC I, ASC II
- IC, AIC
- CA I, CA II, CA III

**Top Ranks (no criteria needed):**
- CGC, DCG, ACG, CC (highest ranks, don't get promoted further)

---

## Comparison with Retirement List

| Feature | Retirement List | Promotion Eligibility |
|---------|----------------|----------------------|
| **Criteria** | Age (60) OR Service (35 years) | Years in current rank |
| **Year Usage** | Finds officers retiring BY end of year | Currently stores year, calculates from current date |
| **Exclusions** | Deceased, inactive | Deceased, interdicted, suspended, dismissed, under investigation |
| **Year Suggestions** | Hardcoded range (2036-2057) | Dynamic calculation based on appointment dates |

---

## Data Flow Diagram

```
User Input (Year)
    ↓
Create PromotionEligibilityList
    ↓
Load Active Criteria (keyed by rank)
    ↓
Query Officers (filtered by status/exclusions)
    ↓
For Each Officer:
    ↓
    Normalize Rank → Match Criteria → Calculate Years in Rank
    ↓
    Eligible? → Add to Collection
    ↓
Officers Found?
    ├─ YES → Create List Items → Success
    └─ NO → Calculate Future Years → Show Suggestions → Error
```

---

## Example Scenarios

### Scenario 1: Successful List Generation
1. User enters year: 2024
2. System finds 15 officers eligible
3. Creates list with 15 items
4. Redirects to index with success message

### Scenario 2: No Officers, With Suggestions
1. User enters year: 2024
2. System finds 0 officers currently eligible
3. Calculates: Officers will be eligible in 2025, 2026, 2028
4. Shows error: "No officers found. Tip: Try 2025, 2026, 2028"
5. User can try one of those years

### Scenario 3: No Officers, No Future Eligibility
1. User enters year: 2024
2. System finds 0 officers currently eligible
3. Calculates: No officers will be eligible in next 20 years
4. Shows error: "No officers found. Check if promotion criteria are configured."

---

## Technical Notes

### Rank Normalization Complexity
The system handles multiple rank formats:
- Full names: "Inspector of Customs (IC) GL07"
- Abbreviations: "IC"
- Partial matches: "Inspector" → "IC"

This ensures criteria matching works regardless of data entry consistency.

### Years Calculation
Uses Carbon's `diffInYears()` which handles leap years and partial years correctly. The result is rounded to 2 decimal places for storage.

### Performance Considerations
- Limits eligible officers to 100 for processing
- Uses `keyBy('rank')` for O(1) criteria lookup
- Only calculates future years when needed (0 officers case)

### Database Integrity
- List items are linked to officers (foreign key)
- Lists can only be deleted if empty (prevents data loss)
- Criteria are soft-deleted via `is_active` flag

---

## Routes Summary

```
GET    /hrd/promotion-eligibility              → Index (list all lists)
GET    /hrd/promotion-eligibility/create       → Create form
POST   /hrd/promotion-eligibility              → Store new list
GET    /hrd/promotion-eligibility/{id}         → Show list details
DELETE /hrd/promotion-eligibility/{id}         → Delete empty list
```

