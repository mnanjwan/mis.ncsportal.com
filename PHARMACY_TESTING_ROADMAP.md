# Pharmacy/Medical System - Straightforward Testing Roadmap

## Prerequisites
- Run seeder: `php artisan db:seed --class=PharmacyTestDataSeeder`
- Test users with roles: Controller Procurement, OC Pharmacy, Central Medical Store, Command Pharmacist
- All passwords: `password`

## 🔑 Login Credentials

### Controller Procurement
- **Email**: `pharmacy.procurement@ncs.gov.ng`
- **Password**: `password`

### OC Pharmacy
- **Email**: `pharmacy.oc@ncs.gov.ng`
- **Password**: `password`

### Central Medical Store
- **Email**: `pharmacy.store@ncs.gov.ng`
- **Password**: `password`

### Command Pharmacist
- **Email**: `pharmacy.command1@ncs.gov.ng`
- **Password**: `password`

---

## PART 1: PROCUREMENT FLOW (New Drug Acquisition)

### Step 1: Controller Procurement Creates Procurement
1. **Login as**: Controller Procurement
2. **Go to**: `/pharmacy/procurements/create`
3. **Actions**:
   - Add notes (optional)
   - Add items:
     - Drug Name: "Paracetamol 500mg"
     - Quantity: 1000
     - Unit: "Tablets"
   - Click "Save Draft"
4. **Verify**: Draft created, can see in "My Procurements"

### Step 2: Controller Procurement Submits
1. **Go to**: `/pharmacy/procurements/{id}` (click on draft)
2. **Click**: "Submit for Approval"
3. **Verify**: Status changes to SUBMITTED

### Step 3: OC Pharmacy Approves
1. **Login as**: OC Pharmacy
2. **Dashboard**: Should see "Pending Procurements" count
3. **Go to**: `/pharmacy/procurements/{id}`
4. **Actions**:
   - Add comment (optional)
   - Click "Approve"
5. **Verify**: Status changes to APPROVED

### Step 4: Central Medical Store Receives
1. **Login as**: Central Medical Store
2. **Dashboard**: Should see "Pending Receipt" count
3. **Go to**: `/pharmacy/procurements/{id}`
4. **Actions**:
   - Enter Quantity Received: 1000
   - Enter Batch Number: "BTH-001"
   - Enter Expiry Date: (future date, e.g., 6 months from now)
   - Click "Confirm Receipt & Update Stock"
5. **Verify**:
   - Status changes to RECEIVED
   - Stock added to Central Store
   - Go to `/pharmacy/stocks?location_type=CENTRAL_STORE` → See new stock entry

---

## PART 2: REQUISITION FLOW (Command Requests Drugs)

### Step 5: Command Pharmacist Creates Requisition
1. **Login as**: Command Pharmacist
2. **Go to**: `/pharmacy/requisitions/create`
3. **Actions**:
   - Select drug from dropdown (should see drugs from catalog)
   - Enter quantity: 200
   - Add more items if needed
   - Add notes (optional)
   - Click "Save Draft"
4. **Verify**: Draft created, shows available stock at central store

### Step 6: Command Pharmacist Submits
1. **Go to**: `/pharmacy/requisitions/{id}`
2. **Click**: "Submit for Approval"
3. **Verify**: Status changes to SUBMITTED

### Step 7: OC Pharmacy Approves Requisition
1. **Login as**: OC Pharmacy
2. **Dashboard**: Should see "Pending Requisitions" count
3. **Go to**: `/pharmacy/requisitions/{id}`
4. **Actions**:
   - Add comment (optional)
   - Click "Approve"
5. **Verify**: Status changes to APPROVED

### Step 8: Central Medical Store Issues
1. **Login as**: Central Medical Store
2. **Dashboard**: Should see "Pending Issue" count
3. **Go to**: `/pharmacy/requisitions/{id}`
4. **Actions**:
   - Enter Quantity to Issue: 200 (or less if available)
   - Click "Issue Items to Command"
5. **Verify**:
   - Status changes to ISSUED
   - Central Store stock reduced
   - Command Pharmacy stock increased
   - Check `/pharmacy/stocks?location_type=CENTRAL_STORE` → Stock reduced
   - Check `/pharmacy/stocks?location_type=COMMAND_PHARMACY` → Stock added

### Step 9: Command Pharmacist Dispenses
1. **Login as**: Command Pharmacist
2. **Dashboard**: Should see "Ready to Dispense" count
3. **Go to**: `/pharmacy/requisitions/{id}`
4. **Actions**:
   - Enter Quantity to Dispense: 200 (or less)
   - Add dispensing notes (optional)
   - Click "Mark as Dispensed"
5. **Verify**:
   - Status changes to DISPENSED
   - Command Pharmacy stock reduced
   - Check `/pharmacy/stocks?location_type=COMMAND_PHARMACY` → Stock reduced

---

## PART 3: STOCK MANAGEMENT

### Step 10: View Central Store Stock
1. **Login as**: Any pharmacy role
2. **Go to**: `/pharmacy/stocks?location_type=CENTRAL_STORE`
3. **Verify**:
   - See all drugs at central store
   - See quantities, batch numbers, expiry dates
   - See status badges (OK, Low Stock, Expiring, etc.)
   - Expiry warnings displayed correctly

### Step 11: View Command Pharmacy Stock
1. **Login as**: Command Pharmacist
2. **Go to**: `/pharmacy/stocks?location_type=COMMAND_PHARMACY`
3. **Verify**:
   - See only your command's stock
   - See quantities, batch numbers, expiry dates
   - Expiry warnings displayed

### Step 12: Stock Adjustment (OC Pharmacy/Central Medical Store)
1. **Login as**: OC Pharmacy or Central Medical Store
2. **Go to**: `/pharmacy/stocks/{stockId}` (click on a stock item)
3. **Click**: "Adjust Stock"
4. **Actions**:
   - Select Adjustment Type: Increase/Decrease
   - Enter Quantity: 50
   - Enter Reason: "Stock correction"
   - Click "Confirm Adjustment"
5. **Verify**:
   - Stock quantity updated
   - Stock movement recorded

---

## PART 4: EXPIRY DATE WARNINGS

### Step 13: Test Expiry Warnings
1. **Create test stock with different expiry dates**:
   - Item expiring in 15 days (should show CRITICAL - Red)
   - Item expiring in 45 days (should show WARNING - Yellow)
   - Item expiring in 75 days (should show CAUTION - Blue)
   - Item expired (should show EXPIRED - Red)
   - Item expiring in 120 days (should show OK - Green)

2. **View Stock Index** (`/pharmacy/stocks`):
   - Verify expiry dates color-coded correctly
   - Verify days remaining displayed
   - Verify status badges show correct warning level

3. **View Dashboards**:
   - OC Pharmacy Dashboard → "Expiring Soon" section shows warnings
   - Central Medical Store Dashboard → Stock overview shows warnings
   - Command Pharmacist Dashboard → My Pharmacy Stock shows warnings

---

## PART 5: DRUG CATALOG MANAGEMENT

### Step 14: View Drug Catalog
1. **Login as**: Any pharmacy role
2. **Go to**: `/pharmacy/drugs`
3. **Verify**: See list of all drugs in catalog

### Step 15: Create New Drug (OC Pharmacy/Central Medical Store)
1. **Login as**: OC Pharmacy or Central Medical Store
2. **Go to**: `/pharmacy/drugs/create/new`
3. **Actions**:
   - Enter Drug Name: "Test Drug"
   - Enter Unit of Measure: "Tablets"
   - Select Active: Yes
   - Click "Create Drug"
4. **Verify**: Drug appears in catalog

### Step 16: Edit Drug
1. **Go to**: `/pharmacy/drugs/{id}/edit`
2. **Actions**: Update details, click "Update Drug"
3. **Verify**: Changes saved

### Step 17: Toggle Drug Active Status
1. **Go to**: `/pharmacy/drugs/{id}`
2. **Click**: "Toggle Active Status"
3. **Verify**: Status changes, drug appears/disappears from dropdowns

---

## PART 6: REPORTS (OC Pharmacy Only)

### Step 18: Stock Balance Report
1. **Login as**: OC Pharmacy
2. **Go to**: `/pharmacy/reports/stock-balance`
3. **Verify**: See stock levels across all locations
4. **Click**: "Print Report"
5. **Verify**: Print view formatted correctly

### Step 19: Expiry Report
1. **Go to**: `/pharmacy/reports/expiry`
2. **Actions**: Select date range (e.g., next 90 days)
3. **Verify**: See items expiring in selected period
4. **Click**: "Print Report"
5. **Verify**: Print view formatted correctly

### Step 20: Custom Report
1. **Go to**: `/pharmacy/reports/custom`
2. **Actions**: Select filters (location, date range, etc.)
3. **Verify**: See filtered results
4. **Click**: "Print Report"
5. **Verify**: Print view formatted correctly

---

## PART 7: DASHBOARDS

### Step 21: Controller Procurement Dashboard
1. **Login as**: Controller Procurement
2. **Go to**: `/pharmacy/controller-procurement/dashboard`
3. **Verify**:
   - Stats cards show correct counts (Draft, Submitted, Approved, Received)
   - Recent procurements listed
   - Quick actions available

### Step 22: OC Pharmacy Dashboard
1. **Login as**: OC Pharmacy
2. **Go to**: `/pharmacy/oc-pharmacy/dashboard`
3. **Verify**:
   - Stats cards (Pending Procurements, Pending Requisitions, Low Stock, Expiring Soon)
   - Pending items listed
   - Low stock alerts
   - Expiring soon items with warnings

### Step 23: Central Medical Store Dashboard
1. **Login as**: Central Medical Store
2. **Go to**: `/pharmacy/central-medical-store/dashboard`
3. **Verify**:
   - Stats cards (Pending Receipt, Pending Issue, Total Stock Items)
   - Procurements awaiting receipt
   - Requisitions awaiting issue
   - Stock overview with expiry warnings

### Step 24: Command Pharmacist Dashboard
1. **Login as**: Command Pharmacist
2. **Go to**: `/pharmacy/command-pharmacist/dashboard`
3. **Verify**:
   - Stats cards (Draft, Submitted, Ready to Dispense, Stock Items)
   - Ready to dispense items listed
   - Recent requisitions
   - My Pharmacy Stock with expiry warnings

---

## PART 8: STOCK MOVEMENTS & HISTORY

### Step 25: View Stock Movements
1. **Go to**: `/pharmacy/stocks/{drugId}` (any drug)
2. **Scroll to**: "Recent Stock Movements" section
3. **Verify**:
   - See all stock movements (Procurement Receipt, Requisition Issue, Dispensed, Adjustments)
   - See dates, quantities, locations, batch numbers
   - See who made the movement

---

## PART 9: EDGE CASES & VALIDATION

### Step 26: Test Low Stock Warnings
1. **Create stock with quantity < 10**
2. **Verify**: Shows "Low Stock" badge in red
3. **Verify**: Appears in OC Pharmacy dashboard "Low Stock Alerts"

### Step 27: Test FEFO (First Expiry First Out)
1. **Create multiple stock entries with different expiry dates**
2. **Issue/Dispense items**
3. **Verify**: Oldest expiry dates are used first

### Step 28: Test Stock Availability
1. **Create requisition for more than available stock**
2. **Try to issue**
3. **Verify**: Cannot issue more than available
4. **Verify**: System shows available quantity

### Step 29: Test Rejection Flow
1. **Create procurement/requisition**
2. **OC Pharmacy rejects it**
3. **Verify**: Status changes to REJECTED
4. **Verify**: Creator notified
5. **Verify**: Stock NOT affected

---

## PART 10: NOTIFICATIONS

### Step 30: Verify Notifications
1. **Submit procurement** → OC Pharmacy should be notified
2. **Approve procurement** → Controller Procurement and Central Medical Store notified
3. **Receive procurement** → Controller Procurement notified
4. **Submit requisition** → OC Pharmacy notified
5. **Approve requisition** → Command Pharmacist and Central Medical Store notified
6. **Issue requisition** → Command Pharmacist notified
7. **Dispense requisition** → OC Pharmacy notified

---

## QUICK TEST CHECKLIST

### Procurement Flow
- [ ] Create draft procurement
- [ ] Submit procurement
- [ ] Approve procurement
- [ ] Receive procurement (adds to central stock)
- [ ] Verify stock added

### Requisition Flow
- [ ] Create draft requisition
- [ ] Submit requisition
- [ ] Approve requisition
- [ ] Issue requisition (subtracts from central, adds to command)
- [ ] Dispense requisition (subtracts from command stock)
- [ ] Verify stock movements correct

### Stock Management
- [ ] View central store stock
- [ ] View command pharmacy stock
- [ ] Adjust stock (if authorized)
- [ ] View stock movements/history

### Expiry Warnings
- [ ] Verify Critical warnings (≤30 days)
- [ ] Verify Warning badges (31-60 days)
- [ ] Verify Caution badges (61-90 days)
- [ ] Verify Expired badges
- [ ] Verify days remaining displayed

### Drug Catalog
- [ ] View drug catalog
- [ ] Create new drug (if authorized)
- [ ] Edit drug (if authorized)
- [ ] Toggle active status

### Reports
- [ ] Stock Balance Report
- [ ] Expiry Report
- [ ] Custom Report
- [ ] Print functionality

### Dashboards
- [ ] Controller Procurement dashboard
- [ ] OC Pharmacy dashboard
- [ ] Central Medical Store dashboard
- [ ] Command Pharmacist dashboard

---

## Expected Results Summary

### Stock Management
- ✅ Receiving procurement → Central stock increases
- ✅ Issuing requisition → Central stock decreases, Command stock increases
- ✅ Dispensing → Command stock decreases
- ✅ FEFO implemented (oldest expiry first)

### Expiry Warnings
- ✅ Expired items → Red "Expired" badge
- ✅ ≤30 days → Red "Critical" badge with days
- ✅ 31-60 days → Yellow "Warning" badge with days
- ✅ 61-90 days → Blue "Caution" badge with days
- ✅ >90 days → Green "OK" badge

### UI/UX
- ✅ All pages accessible based on roles
- ✅ Clean, consistent UI
- ✅ Status badges color-coded
- ✅ Expiry warnings visible everywhere
- ✅ Days remaining displayed clearly

---

## Testing Time Estimate
- **Full Test**: ~2-3 hours
- **Quick Test**: ~45 minutes (focus on main flows)
- **Expiry Test**: ~15 minutes (create test data with various expiry dates)

---

**Note**: Make sure to test with different expiry dates to verify all warning levels work correctly!
