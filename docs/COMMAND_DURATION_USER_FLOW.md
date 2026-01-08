# Command Duration Feature - User Flow

## Visual User Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    HRD Dashboard                             │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Menu: Command Duration                              │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────────────┬──────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────┐
│              Command Duration Search Page                     │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  FILTER FORM                                         │   │
│  │  ┌──────────────────────────────────────────────┐   │   │
│  │  │ Zone: [Dropdown ▼] *Required                │   │   │
│  │  │ Command: [Dropdown ▼] *Required            │   │   │
│  │  │ Rank: [Dropdown ▼] Optional                │   │   │
│  │  │ Cadre: [Dropdown ▼] Optional               │   │   │
│  │  │ Sex: [Dropdown ▼] Optional                 │   │   │
│  │  │ Qualification: [Dropdown ▼] Optional        │   │   │
│  │  │ Duration: [0-10+ Years ▼] Optional          │   │   │
│  │  └──────────────────────────────────────────────┘   │   │
│  │  [Search] [Reset]                                   │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────────────┬──────────────────────────────────┘
                             │
                             │ User clicks "Search"
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                    SEARCH RESULTS                            │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Results: 25 officers found                          │   │
│  │  [Print Results] [Add Selected to Draft]              │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  TABLE: Officer List                                  │   │
│  │  ┌──┬──────────┬──────────┬──────┬──────────┬────┐ │   │
│  │  │☑│Service # │Name      │Rank  │Duration  │Stat│ │   │
│  │  ├──┼──────────┼──────────┼──────┼──────────┼────┤ │   │
│  │  │☑│NCS001    │J. Doe    │AC   │3Y 5M     │Act │ │   │
│  │  │☑│NCS002    │M. Smith  │CSC  │2Y 1M     │Act │ │   │
│  │  │☐│NCS003    │K. Jones  │SC   │1Y 8M     │Sus │ │   │
│  │  │☑│NCS004    │L. Brown  │ASC  │5Y 2M     │Act │ │   │
│  │  │☐│NCS005    │P. White  │DSC  │0Y 11M    │Inv │ │   │
│  │  └──┴──────────┴──────────┴──────┴──────────┴────┘ │   │
│  │                                                      │   │
│  │  Legend:                                             │   │
│  │  ☑ = Selectable (Active)                            │   │
│  │  ☐ = Disabled (Suspended/Under Investigation)     │   │
│  │  [⚠ In Draft] = Officer already in draft           │   │
│  └──────────────────────────────────────────────────────┘   │
└────────────────────────────┬──────────────────────────────────┘
                             │
        ┌────────────────────┴────────────────────┐
        │                                        │
        │ User selects officers                  │ User clicks "Print"
        │ and clicks "Add to Draft"              │
        ▼                                        ▼
┌───────────────────────────┐      ┌───────────────────────────┐
│  Add to Draft Confirmation│      │   Print Preview/PDF        │
│                           │      │                           │
│  "Add 3 officers to       │      │  Command Duration Report │
│   draft deployment?"      │      │  Zone: Zone A            │
│                           │      │  Command: Command 1       │
│  [Cancel] [Confirm]       │      │  Duration: 3 Years        │
│                           │      │                           │
│                           │      │  [Officer List Table]    │
│                           │      │                           │
│                           │      │  [Print] [Close]         │
└───────────┬───────────────┘      └───────────────────────────┘
            │
            │ User confirms
            ▼
┌─────────────────────────────────────────────────────────────┐
│              Draft Deployment Page                           │
│                                                               │
│  Officers added from Command Duration search                 │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Officer | From Command | To Command | Actions      │   │
│  │  ───────────────────────────────────────────────────│   │
│  │  NCS001  | Command A   | Command B  | [Swap][Remove]│   │
│  │  NCS002  | Command A   | Command B  | [Swap][Remove]│   │
│  │  NCS004  | Command A   | Command B  | [Swap][Remove]│   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  [Publish Deployment] [Print Draft]                          │
└─────────────────────────────────────────────────────────────┘
```

---

## Step-by-Step User Flow

### Step 1: Access Command Duration
1. User logs in as HRD Officer or Super Admin
2. Navigates to HRD Dashboard
3. Clicks on "Command Duration" menu item
4. **Route**: `GET /hrd/command-duration`
5. **View**: `dashboards/hrd/command-duration/index.blade.php`

### Step 2: Set Search Filters
1. **Select Zone** (Required)
   - Dropdown populated with all active zones
   - Selection triggers command dropdown update

2. **Select Command** (Required)
   - Dropdown populated with commands in selected zone
   - Becomes enabled after zone selection

3. **Optional Filters**:
   - **Rank**: Filter by officer rank
   - **Cadre**: Filter by cadre
   - **Sex**: Filter by Male/Female
   - **Qualification**: Filter by qualification
   - **Command Duration**: Select duration range (0-10+ years)

4. Click **"Search"** button
   - **Route**: `POST /hrd/command-duration/search`
   - Validates required fields
   - Executes search query

### Step 3: Review Search Results
1. System displays table with matching officers:
   - **Service Number**
   - **Full Name**
   - **Rank**
   - **Command** (current command)
   - **Date Posted to Command**
   - **Duration in Command** (Years & Months format)
   - **Current Status** (Active/Suspended/Under Investigation/etc.)

2. **Status Indicators**:
   - ✅ **Active**: Green badge, officer is selectable
   - ⚠️ **Suspended**: Red badge, checkbox disabled
   - ⚠️ **Under Investigation**: Orange badge, checkbox disabled
   - ⚠️ **Dismissed**: Red badge, checkbox disabled
   - ⚠️ **In Draft**: Warning badge, officer already in draft

3. User can:
   - Select/deselect officers using checkboxes
   - Use "Select All" for eligible officers
   - View officer details
   - Click "Print Results" to generate report

### Step 4: Add Officers to Draft
1. User selects one or more eligible officers
2. Clicks **"Add Selected to Draft"** button
   - **Route**: `POST /hrd/command-duration/add-to-draft`
   - Validates selected officers are eligible
   - Checks if draft deployment exists (creates if not)

3. System processes:
   - Creates `ManningDeploymentAssignment` records
   - Links officers to active draft deployment
   - Sets `manning_request_id` to NULL (not from manning request)

4. Success message displayed
5. Redirect to Draft Deployment page
   - **Route**: `GET /hrd/manning-deployments/draft`

### Step 5: Manage Draft Deployment
1. Officers appear in draft deployment view
2. User can:
   - **Remove** officers from draft
   - **Swap** officers with alternatives
   - **Add more** officers (from other sources)
   - **Print** draft deployment
   - **Publish** deployment when ready

### Step 6: Print Functionality
1. User clicks **"Print Results"** button
   - **Route**: `GET /hrd/command-duration/print?zone=X&command=Y&...`
   - Opens print view in new window/tab

2. Print view displays:
   - Search criteria used
   - Complete officer list with all details
   - Formatted for A4 printing
   - Page numbers and date

3. User prints or saves as PDF

---

## Data Flow

### Search Query Flow
```
User Input (Filters)
    ↓
Controller Validation
    ↓
Build Query:
  - Filter by Zone & Command (required)
  - Filter by Rank (optional)
  - Filter by Cadre (optional)
  - Filter by Sex (optional)
  - Filter by Qualification (optional)
  - Filter by Duration (optional)
    ↓
Join with OfficerPosting (current posting)
    ↓
Calculate Duration for each officer
    ↓
Check Officer Status
    ↓
Check if in Draft
    ↓
Format Results
    ↓
Return to View
```

### Add to Draft Flow
```
Selected Officer IDs
    ↓
Validate Eligibility
    ↓
Check/Create Draft Deployment
    ↓
Create ManningDeploymentAssignment records
    ↓
Link to Draft Deployment
    ↓
Return Success Response
    ↓
Redirect to Draft View
```

---

## Status Logic

### Officer Status Determination
```
IF dismissed = true
  → Status: "Dismissed" (Red badge, Disabled)
ELSE IF suspended = true
  → Status: "Suspended" (Red badge, Disabled)
ELSE IF interdicted = true
  → Status: "Interdicted" (Red badge, Disabled)
ELSE IF ongoing_investigation = true
  → Status: "Under Investigation" (Orange badge, Disabled)
ELSE
  → Status: "Active" (Green badge, Enabled)
```

### Eligibility for Movement
```
Eligible = NOT suspended 
        AND NOT dismissed 
        AND NOT ongoing_investigation
        AND NOT interdicted
        AND is_active = true
```

### In Draft Check
```
Check ManningDeploymentAssignment table:
  - officer_id matches
  - deployment.status = 'DRAFT'
```

---

## Duration Calculation Logic

### Duration Filter Application
```
IF duration = 0
  → posting_date >= (now - 1 year) AND posting_date <= now

IF duration = 1
  → posting_date >= (now - 2 years) AND posting_date < (now - 1 year)

IF duration = 2
  → posting_date >= (now - 3 years) AND posting_date < (now - 2 years)

...

IF duration = 9
  → posting_date >= (now - 10 years) AND posting_date < (now - 9 years)

IF duration = 10+
  → posting_date < (now - 10 years)
```

### Duration Display
```
Calculate: now - posting_date
Format: "X Years Y Months"
Example: "3 Years 5 Months"
```

---

## Error Handling Flow

### Invalid Search
```
User submits without Zone/Command
    ↓
Validation Error
    ↓
Display: "Zone and Command are required"
    ↓
Highlight required fields
```

### No Results
```
Search returns 0 officers
    ↓
Display: "No officers found matching criteria"
    ↓
Suggest: "Try adjusting your filters"
```

### Officer Not Eligible
```
User tries to add ineligible officer
    ↓
Validation fails
    ↓
Display: "Officer [Name] is not eligible for movement (Status: [Status])"
    ↓
Remove from selection
```

### Officer Already in Draft
```
Officer is in active draft
    ↓
Show warning badge: "In Draft"
    ↓
Allow selection but show message
    ↓
User can still add (will create duplicate or update)
```

---

## Integration Points

### With Draft Deployment System
- Uses existing `ManningDeployment` model
- Uses existing `ManningDeploymentAssignment` model
- Reuses draft view: `dashboards/hrd/manning-deployment-draft.blade.php`
- Officers appear alongside manning request assignments

### With Print System
- Follows existing print view pattern
- Uses same print styling as other HRD reports
- Route follows pattern: `/hrd/command-duration/print`

### With Access Control
- Uses existing HRD middleware: `role:HRD`
- Super Admin also has access
- Same authorization pattern as other HRD features

---

## Success Criteria

✅ User can search officers by command duration
✅ Search respects all filter criteria
✅ Results show accurate duration calculations
✅ Status badges correctly indicate officer eligibility
✅ Officers can be added to draft deployment
✅ Draft integration works seamlessly
✅ Print functionality generates proper report
✅ Access control restricts to HRD/Super Admin only
✅ UI follows existing HRD design patterns

