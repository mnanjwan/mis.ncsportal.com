# CC T&L Proposal Page Explanation

## What is This Page For?

This page (`/fleet/requests/{id}`) is the **CC T&L Inventory Check & Proposal** page. It appears when:

- A **New Vehicle Request** has been submitted
- The request is at **Step 1** of the workflow
- The current user has the **CC T&L** role
- The request is awaiting CC T&L's action

### Purpose:
CC T&L uses this page to:
1. **Review the request** - See what vehicles are being requested (type, quantity)
2. **Check available inventory** - View vehicles in stock that match the request criteria
3. **Propose vehicle selection** - Select specific vehicles from inventory to reserve for this request
4. **Submit proposal** - Forward the proposal to CGC for approval

### What Happens:
- **If vehicles are selected**: Vehicles are reserved, request moves to CGC for approval
- **If NO vehicles are selected**: Request becomes KIV (Keep In View), stays at CC T&L step

---

## Confirmation Dialog Issue - FIXED

**Problem**: The confirmation modal wasn't showing when clicking "Submit Proposal"

**Solution**: Updated the form to use an ID selector (`id="ccProposalForm"`) instead of attribute selector for more reliable JavaScript targeting.

**Now**: When you click "Submit Proposal", a confirmation modal will appear explaining:
- What will happen (vehicles reserved, request forwarded)
- Why it's happening (workflow progression)
- Warning if no vehicles selected (will become KIV)

---

## How Are Vehicles Added to Be Selected?

Vehicles are **NOT added on this page**. They are added to the system through a separate process:

### Vehicle Intake Process:

1. **Transport Store/Receiver** role adds vehicles to the system
2. **Route**: `/fleet/vehicles/intake` (Vehicle Intake form)
3. **Process**:
   - Transport Store/Receiver fills in vehicle details:
     - Make, Model, Year
     - Vehicle Type (SALOON, SUV, BUS)
     - Chassis Number (required)
     - Engine Number
     - Registration Number
     - Received Date
   - Vehicle is created with status: `IN_STOCK`
   - Vehicle is assigned to a command

### Vehicle Lifecycle:

```
New Vehicle Intake (Transport Store/Receiver)
    ↓
Status: IN_STOCK
    ↓
Available for CC T&L Selection
    ↓
When Selected: Reserved for Request
    ↓
After Approval: Released to Command Pool (AT_COMMAND_POOL)
    ↓
CD Issues to Officer: IN_OFFICER_CUSTODY
```

### Why "No Available Vehicles Found"?

The table shows "No available vehicles found" because:

1. **No vehicles match the criteria**:
   - Vehicle Type must match (SALOON in your case)
   - Status must be `IN_STOCK`
   - Vehicle must not be reserved (`reserved_fleet_request_id` is null)
   - Vehicle must match requested make/model/year (if specified)

2. **Vehicles haven't been added yet**:
   - Transport Store/Receiver needs to add vehicles via `/fleet/vehicles/intake`
   - Or vehicles need to be returned to stock

3. **All matching vehicles are reserved**:
   - Other requests may have reserved available vehicles

### To Add Vehicles:

1. **Login as Transport Store/Receiver** (or user with that role)
2. **Navigate to**: Fleet > Vehicles > Intake Vehicle (or `/fleet/vehicles/intake`)
3. **Fill in vehicle details** and submit
4. **Vehicles will appear** in CC T&L's selection table when:
   - They match the request type (SALOON for your request)
   - They are in `IN_STOCK` status
   - They are not reserved

---

## Page Sections Explained

### 1. Request Information Card
- Shows request details (ID, type, origin command, status, requested quantity)
- Displays who created the request

### 2. CC T&L Action Panel (Current Step)
- **Comment field**: Optional notes about the proposal
- **Vehicle Selection Table**: Shows available vehicles matching request criteria
- **Submit Proposal Button**: Submits the proposal (now with confirmation)

### 3. Workflow Progress Table
- Shows all workflow steps
- Highlights current step (Step 1 - CC T&L)
- Shows decisions made at each step
- Shows who acted and when

---

## Current Status in Your Screenshot

Based on the image:
- **Request #22**: New Vehicle Request for 10 Saloons
- **Status**: KIV (Keep In View)
- **Current Step**: Step 1 - CC T&L
- **Decision**: KIV (already made)
- **Reason**: No vehicles available/selected

This means CC T&L has already submitted the proposal with no vehicles selected, placing it on KIV. The request will remain at CC T&L's step until vehicles become available.

---

## Next Steps

1. **Add vehicles** via Transport Store/Receiver intake
2. **Return to this request** when vehicles are available
3. **Select vehicles** from the table
4. **Submit proposal** again (will remove KIV status and forward to CGC)
