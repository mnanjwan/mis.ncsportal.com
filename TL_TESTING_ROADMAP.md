# T&L System - Complete Testing Roadmap

**All passwords: `password`**

---

## ROLE 1: CD (tl_user_1@example.com)

### Pages to Test:
1. **Dashboard** → `/fleet/dashboard/cd`
   - ✅ View dashboard cards (Draft, Submitted, KIV, Released requests)
   - ✅ View command pool vehicles count
   - ✅ View active assignments count
   - ✅ View pending returns count
   - ✅ Click quick links

2. **Create Request** → `/fleet/requests/create`
   - ✅ Create New Vehicle Request (DRAFT)
   - ✅ Create Re-allocation Request (DRAFT)
   - ✅ Create OPE Request (DRAFT)
   - ✅ Create Repair Request (DRAFT)
   - ✅ Create Use Request (DRAFT)
   - ✅ Create Requisition (DRAFT)
   - ✅ Upload documents
   - ✅ Save as DRAFT (verify confirmation dialog)

3. **View Requests** → `/fleet/requests`
   - ✅ View "My Requests" section
   - ✅ View "Inbox" section (if any)
   - ✅ Submit DRAFT request (verify confirmation dialog)
   - ✅ View request details

4. **View Vehicles** → `/fleet/vehicles`
   - ✅ View command-scoped vehicles list
   - ✅ View vehicle details

5. **Issue Vehicle** → `/fleet/vehicles/{vehicle}/issue`
   - ✅ Select vehicle from command pool
   - ✅ Select officer
   - ✅ Add notes
   - ✅ Submit issuance

6. **Return Vehicle** → `/fleet/vehicles/{vehicle}/return`
   - ✅ View assignment details
   - ✅ Process return
   - ✅ Add return notes

7. **Update Service Status** → `/fleet/vehicles/{vehicle}/service-status`
   - ✅ Update vehicle service status

8. **Returns Report** → `/fleet/reports/returns`
   - ✅ View returns report

---

## ROLE 2: Area Controller (tl_user_2@example.com)

### Pages to Test:
1. **Dashboard** → `/area-controller/dashboard` (if exists)
   - ✅ View dashboard

2. **Create Request** → `/fleet/requests/create`
   - ✅ Create New Vehicle Request
   - ✅ Create Re-allocation Request
   - ✅ Save as DRAFT (verify confirmation dialog)

3. **View Requests** → `/fleet/requests`
   - ✅ View "My Requests"
   - ✅ View "Inbox" (if any)
   - ✅ Submit DRAFT request (verify confirmation dialog)

4. **View Request Details** → `/fleet/requests/{id}`
   - ✅ View request information
   - ✅ View workflow progress
   - ✅ View Amount/Vehicle fields
   - ✅ View Notes/Attachments

5. **Acknowledge Receipt** → `/fleet/assignments/{assignment}/receive`
   - ✅ Acknowledge receipt of released vehicles

---

## ROLE 3: OC Workshop (tl_user_3@example.com)

### Pages to Test:
1. **Create Requisition** → `/fleet/requests/create`
   - ✅ Create Maintenance Requisition (₦150,000)
   - ✅ Create Maintenance Requisition (₦450,000)
   - ✅ Create Maintenance Requisition (₦1,200,000)
   - ✅ Upload bills/recommendations
   - ✅ Upload job completion receipts
   - ✅ Save as DRAFT (verify confirmation dialog)

2. **View Requests** → `/fleet/requests`
   - ✅ View "My Requests"
   - ✅ View "Inbox" (if any)
   - ✅ Submit DRAFT requisition (verify confirmation dialog)

3. **View Request Details** → `/fleet/requests/{id}`
   - ✅ View requisition details
   - ✅ View Amount field
   - ✅ View workflow progress
   - ✅ Verify routing based on amount

---

## ROLE 4: Staff Officer T&L (tl_user_4@example.com)

### Pages to Test:
1. **Create Request** → `/fleet/requests/create`
   - ✅ Create Repair Request
   - ✅ Create OPE Request
   - ✅ Create Use Request
   - ✅ Save as DRAFT (verify confirmation dialog)

2. **View Requests** → `/fleet/requests`
   - ✅ View "My Requests"
   - ✅ View "Inbox" (if any)
   - ✅ Submit DRAFT request (verify confirmation dialog)

3. **View Request Details** → `/fleet/requests/{id}`
   - ✅ View request details
   - ✅ View workflow progress
   - ✅ Approve/Reject/KIV (if at Staff Officer step)

---

## ROLE 5: CC T&L (tl_user_5@example.com)

### Pages to Test:
1. **Dashboard** → `/fleet/dashboard/cc-tl`
   - ✅ View dashboard cards (In Stock, Reserved, KIV, Inventory Checks, Release Pending)
   - ✅ View inbox items

2. **View Requests** → `/fleet/requests`
   - ✅ View "Inbox" (requests awaiting CC T&L action)
   - ✅ View all requests

3. **Propose Vehicles** → `/fleet/requests/{id}` (New Vehicle, Step 1)
   - ✅ View CC T&L Action Panel
   - ✅ Select vehicles from available list
   - ✅ Submit Proposal (verify confirmation dialog - with vehicles)
   - ✅ Submit Proposal without vehicles (verify KIV warning)

4. **Release Vehicles** → `/fleet/requests/{id}` (New Vehicle Step 5 or Re-allocation Step 1)
   - ✅ View Release Panel
   - ✅ View reserved vehicles count
   - ✅ Approve and Release (verify confirmation dialog)

5. **View Vehicles** → `/fleet/vehicles`
   - ✅ View all vehicles
   - ✅ View vehicle inventory

6. **Edit Vehicle Identifiers** → `/fleet/vehicles/{vehicle}/identifiers`
   - ✅ Edit vehicle identifiers (Reg No, Chassis No, Engine No)

7. **Returns Report** → `/fleet/reports/returns`
   - ✅ View returns report

---

## ROLE 6: ACG TS (tl_user_6@example.com)

### Pages to Test:
1. **Dashboard** → `/fleet/dashboard/acg-ts`
   - ✅ View dashboard cards (Inbox, Pending Approval, KIV)
   - ✅ View inbox items

2. **View Requests** → `/fleet/requests`
   - ✅ View "Inbox" (requests awaiting ACG TS action)
   - ✅ Filter by request type

3. **Approve Requisitions** → `/fleet/requests/{id}` (Requisition Step 1)
   - ✅ View request details
   - ✅ View Amount field
   - ✅ Approve ≤ ₦300k (verify confirmation dialog - completes workflow)
   - ✅ Forward > ₦300k (verify confirmation dialog - goes to DCG FATS)
   - ✅ Reject (verify confirmation dialog)
   - ✅ Place on KIV (verify confirmation dialog)

4. **Forward New Vehicle** → `/fleet/requests/{id}` (New Vehicle Step 4)
   - ✅ Forward to CC T&L Release (verify confirmation dialog)

5. **View Vehicles** → `/fleet/vehicles`
   - ✅ View vehicles list

6. **Edit Vehicle Identifiers** → `/fleet/vehicles/{vehicle}/identifiers`
   - ✅ Edit vehicle identifiers

---

## ROLE 7: DCG FATS (tl_user_7@example.com)

### Pages to Test:
1. **Dashboard** → `/fleet/dashboard/dcg-fats`
   - ✅ View dashboard cards (Inbox, Pending Approval, KIV)
   - ✅ View inbox items

2. **View Requests** → `/fleet/requests`
   - ✅ View "Inbox" (requests awaiting DCG FATS action)

3. **Approve Requisitions** → `/fleet/requests/{id}` (Requisition Step 2)
   - ✅ View request details
   - ✅ View Amount field
   - ✅ Approve ≤ ₦500k (verify confirmation dialog - completes workflow)
   - ✅ Forward > ₦500k (verify confirmation dialog - goes to CGC)
   - ✅ Reject (verify confirmation dialog)
   - ✅ Place on KIV (verify confirmation dialog)

4. **Forward New Vehicle** → `/fleet/requests/{id}` (New Vehicle Step 3)
   - ✅ Forward to ACG TS (verify confirmation dialog)

5. **View Vehicles** → `/fleet/vehicles`
   - ✅ View vehicles list

6. **Edit Vehicle Identifiers** → `/fleet/vehicles/{vehicle}/identifiers`
   - ✅ Edit vehicle identifiers

---

## ROLE 8: CGC (tl_user_8@example.com)

### Pages to Test:
1. **Dashboard** → `/cgc/dashboard`
   - ✅ View dashboard
   - ✅ Navigate to Fleet Requests

2. **View Requests** → `/fleet/requests`
   - ✅ View "Inbox" (requests awaiting CGC action)

3. **Approve New Vehicle** → `/fleet/requests/{id}` (New Vehicle Step 2)
   - ✅ View request details
   - ✅ View CC T&L proposal
   - ✅ Approve (verify confirmation dialog - goes to DCG FATS)
   - ✅ Reject (verify confirmation dialog - goes back to CC T&L)
   - ✅ Place on KIV (verify confirmation dialog)

4. **Approve Requisitions** → `/fleet/requests/{id}` (Requisition Step 3)
   - ✅ View request details
   - ✅ View Amount field
   - ✅ Approve > ₦500k (verify confirmation dialog - completes workflow)
   - ✅ Reject (verify confirmation dialog)
   - ✅ Place on KIV (verify confirmation dialog)

---

## COMPLETE WORKFLOW TESTING SEQUENCE

### Test 1: New Vehicle Request (Full Chain)
1. **Area Controller** → Create New Vehicle Request → Submit
2. **CC T&L** → View Inbox → Open Request → Select Vehicles → Submit Proposal
3. **CGC** → View Inbox → Open Request → Approve
4. **DCG FATS** → View Inbox → Open Request → Forward
5. **ACG TS** → View Inbox → Open Request → Forward
6. **CC T&L** → View Inbox → Open Request → Approve and Release
7. **Area Controller** → View Request → Verify Status = RELEASED

### Test 2: Re-allocation Request (Direct Path)
1. **Area Controller** → Create Re-allocation Request → Select Vehicle → Submit
2. **CC T&L** → View Inbox → Open Request → Approve and Release
3. **Area Controller** → View Request → Verify Status = RELEASED

### Test 3: Requisition - ₦150k (ACG TS Only)
1. **OC Workshop** → Create Requisition (₦150,000) → Submit
2. **ACG TS** → View Inbox → Open Request → Approve
3. **OC Workshop** → View Request → Verify Status = RELEASED

### Test 4: Requisition - ₦450k (ACG TS → DCG FATS)
1. **OC Workshop** → Create Requisition (₦450,000) → Submit
2. **ACG TS** → View Inbox → Open Request → Forward
3. **DCG FATS** → View Inbox → Open Request → Approve
4. **OC Workshop** → View Request → Verify Status = RELEASED

### Test 5: Requisition - ₦1.2M (Full Chain)
1. **OC Workshop** → Create Requisition (₦1,200,000) → Submit
2. **ACG TS** → View Inbox → Open Request → Forward
3. **DCG FATS** → View Inbox → Open Request → Forward
4. **CGC** → View Inbox → Open Request → Approve
5. **OC Workshop** → View Request → Verify Status = RELEASED

### Test 6: KIV Functionality
1. **CC T&L** → Open New Vehicle Request → Submit Proposal without vehicles → Verify KIV
2. **CGC** → Open Request → Place on KIV → Verify Status = KIV
3. **CGC** → Open KIV Request → Approve → Verify workflow continues

### Test 7: Rejection Flow
1. **CGC** → Open New Vehicle Request → Reject
2. **CC T&L** → View Request → Verify Status = REJECTED → Verify request returned to CC T&L

---

## UI/UX VERIFICATION CHECKLIST

For ALL roles on ALL request detail pages (`/fleet/requests/{id}`):

- [ ] **Amount Field**: Visible when request has amount
- [ ] **Vehicle Field**: Visible when request has vehicle
- [ ] **Workflow Progress Table**: Current step highlighted (blue background, bold)
- [ ] **Notes**: Displayed properly in styled box
- [ ] **Attachments**: Clickable download link with icon
- [ ] **Action Panels**: Appear only when user is at current step
- [ ] **Action Buttons**: Display based on step action type
- [ ] **Confirmation Dialogs**: All action buttons show confirmation with WHAT/WHY

---

## CONFIRMATION DIALOG VERIFICATION

For ALL action buttons, verify confirmation dialogs show:

- [ ] **Submit Request**: Explains workflow initiation
- [ ] **CC T&L Propose**: Explains vehicle reservation, warns if no vehicles
- [ ] **CC T&L Release**: Explains vehicle release to command
- [ ] **Approve**: Explains next steps, amount-based routing (for requisitions)
- [ ] **Reject**: Warning message, explains workflow stop
- [ ] **Forward**: Explains next step movement
- [ ] **KIV**: Explains temporary pause
- [ ] **Save Draft**: Explains draft vs submitted

---

## QUICK TEST CHECKLIST

**Login & Access:**
- [ ] All 8 test users can login
- [ ] Each role can access their dashboard
- [ ] Each role can access Fleet > Requests
- [ ] Role-specific pages are accessible

**Request Creation:**
- [ ] CD can create all request types
- [ ] Area Controller can create New Vehicle & Re-allocation
- [ ] OC Workshop can create Requisitions
- [ ] Staff Officer T&L can create Repair/OPE/Use

**Workflow Actions:**
- [ ] CC T&L can propose vehicles
- [ ] CC T&L can release vehicles
- [ ] CGC can approve/reject/KIV
- [ ] DCG FATS can approve/forward/KIV
- [ ] ACG TS can approve/forward/KIV

**Amount Thresholds:**
- [ ] ₦150k stops at ACG TS
- [ ] ₦450k goes to DCG FATS
- [ ] ₦1.2M goes to CGC

**UI Elements:**
- [ ] All fields visible when applicable
- [ ] Workflow progress highlights current step
- [ ] Confirmation dialogs work for all actions

---

**Total Pages to Test: 30+**
**Total Roles: 8**
**Total Workflows: 7**
