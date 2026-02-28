# Medical/Pharmacy System - Complete Flow Documentation

## Overview

The Medical/Pharmacy system manages procurement, requisition, stock management, and distribution of medical supplies and drugs across commands and the central medical store.

## Roles Involved

1. **Controller Procurement** - Creates procurement requests for new drugs/supplies
2. **OC Pharmacy** - Approves procurements and requisitions, manages drug catalog
3. **Central Medical Store** - Receives procurements, issues requisitions, manages central stock
4. **Command Pharmacist** - Creates requisitions for their command, dispenses drugs

---

## Flow 1: Procurement (New Drug/Supply Acquisition)

### Step-by-Step Flow

```
Controller Procurement
    ↓ Creates Draft Procurement
    ↓ Adds Items (Drug Name, Quantity, Unit)
    ↓ Submits for Approval
    ↓
OC Pharmacy
    ↓ Reviews & Approves/Rejects
    ↓ (If Approved)
    ↓
Central Medical Store
    ↓ Receives Procurement
    ↓ Updates Stock with Batch Numbers & Expiry Dates
    ↓ Status: RECEIVED
```

### Detailed Process

#### 1. Controller Procurement Creates Procurement
- **Page**: `/pharmacy/procurements/create`
- **Action**: Create new procurement draft
- **Fields**:
  - Notes (optional)
  - Items (Drug Name, Quantity, Unit of Measure)
- **Status**: DRAFT

#### 2. Controller Procurement Submits
- **Page**: `/pharmacy/procurements/{id}`
- **Action**: Click "Submit for Approval"
- **Status**: Changes to SUBMITTED
- **Notification**: OC Pharmacy is notified

#### 3. OC Pharmacy Reviews
- **Dashboard**: Shows pending procurements
- **Page**: `/pharmacy/procurements/{id}`
- **Actions Available**:
  - **Approve** → Moves to Central Medical Store
  - **Reject** → Returns to Controller Procurement
- **Status**: Changes to APPROVED or REJECTED
- **Notification**: Controller Procurement and Central Medical Store notified

#### 4. Central Medical Store Receives
- **Dashboard**: Shows approved procurements awaiting receipt
- **Page**: `/pharmacy/procurements/{id}`
- **Action**: "Confirm Receipt & Update Stock"
- **Required Fields**:
  - Quantity Received (per item)
  - Batch Number (per item)
  - Expiry Date (per item)
- **Status**: Changes to RECEIVED
- **Stock**: Automatically updated in central store
- **Notification**: Controller Procurement notified

---

## Flow 2: Requisition (Command Requests Drugs from Central Store)

### Step-by-Step Flow

```
Command Pharmacist
    ↓ Creates Draft Requisition
    ↓ Selects Drugs from Catalog
    ↓ Adds Quantities
    ↓ Submits for Approval
    ↓
OC Pharmacy
    ↓ Reviews & Approves/Rejects
    ↓ (If Approved)
    ↓
Central Medical Store
    ↓ Issues Drugs
    ↓ Updates Stock (Reduces Central Stock)
    ↓ Status: ISSUED
    ↓
Command Pharmacist
    ↓ Dispenses to Patients/Officers
    ↓ Updates Command Stock
    ↓ Status: DISPENSED
```

### Detailed Process

#### 1. Command Pharmacist Creates Requisition
- **Page**: `/pharmacy/requisitions/create`
- **Action**: Create new requisition draft
- **Fields**:
  - Notes (optional)
  - Items (Select Drug from Catalog, Quantity)
  - Shows available stock at central store for reference
- **Status**: DRAFT

#### 2. Command Pharmacist Submits
- **Page**: `/pharmacy/requisitions/{id}`
- **Action**: Click "Submit for Approval"
- **Status**: Changes to SUBMITTED
- **Notification**: OC Pharmacy is notified

#### 3. OC Pharmacy Reviews
- **Dashboard**: Shows pending requisitions
- **Page**: `/pharmacy/requisitions/{id}`
- **Actions Available**:
  - **Approve** → Moves to Central Medical Store
  - **Reject** → Returns to Command Pharmacist
- **Status**: Changes to APPROVED or REJECTED
- **Notification**: Command Pharmacist and Central Medical Store notified

#### 4. Central Medical Store Issues
- **Dashboard**: Shows approved requisitions awaiting issue
- **Page**: `/pharmacy/requisitions/{id}`
- **Action**: "Issue Requisition"
- **Required Fields**:
  - Quantity Issued (per item, cannot exceed requested)
- **Status**: Changes to ISSUED
- **Stock**: 
  - Central Store stock reduced
  - Command Pharmacy stock increased
- **Notification**: Command Pharmacist notified

#### 5. Command Pharmacist Dispenses
- **Dashboard**: Shows issued requisitions ready to dispense
- **Page**: `/pharmacy/requisitions/{id}`
- **Action**: "Mark as Dispensed"
- **Status**: Changes to DISPENSED
- **Stock**: Command stock updated (reduced when dispensed)

---

## Flow 3: Stock Management

### Central Medical Store Stock
- **Page**: `/pharmacy/stocks?location_type=CENTRAL_STORE`
- **View**: All drugs in central store
- **Actions**:
  - View stock levels
  - Adjust stock (OC Pharmacy/Central Medical Store only)
  - View stock movements/history

### Command Pharmacy Stock
- **Page**: `/pharmacy/stocks?location_type=COMMAND_PHARMACY&command_id={id}`
- **View**: Drugs at specific command pharmacy
- **Actions**:
  - View stock levels (Command Pharmacist sees only their command)
  - View stock movements/history

### Stock Adjustments
- **Who Can Adjust**: OC Pharmacy, Central Medical Store
- **Page**: `/pharmacy/stocks/{stockId}/adjust`
- **Fields**:
  - Adjustment Type (Increase/Decrease)
  - Quantity
  - Reason
- **Result**: Stock movement recorded, stock updated

---

## Flow 4: Drug Catalog Management

### View Drugs
- **Page**: `/pharmacy/drugs`
- **Access**: All pharmacy roles
- **Shows**: List of all drugs in catalog

### Create/Edit Drugs
- **Who Can Manage**: OC Pharmacy, Central Medical Store
- **Page**: `/pharmacy/drugs/create` or `/pharmacy/drugs/{id}/edit`
- **Fields**:
  - Drug Name
  - Unit of Measure
  - Active/Inactive status
- **Action**: Toggle active status to enable/disable drug

---

## Flow 5: Reports

### Available Reports (OC Pharmacy Only)
1. **Stock Balance Report**
   - Page: `/pharmacy/reports/stock-balance`
   - Shows: Current stock levels across all locations
   - Print: Available

2. **Expiry Report**
   - Page: `/pharmacy/reports/expiry`
   - Shows: Drugs expiring within specified period
   - Print: Available

3. **Custom Report**
   - Page: `/pharmacy/reports/custom`
   - Shows: Custom filtered reports
   - Print: Available

---

## Pages & Access by Role

### Controller Procurement
- ✅ Dashboard: `/pharmacy/controller-procurement/dashboard`
- ✅ Create Procurement: `/pharmacy/procurements/create`
- ✅ View My Procurements: `/pharmacy/procurements`
- ✅ Edit Draft: `/pharmacy/procurements/{id}/edit`
- ✅ Submit: `/pharmacy/procurements/{id}/submit`

### OC Pharmacy
- ✅ Dashboard: `/pharmacy/oc-pharmacy/dashboard`
- ✅ View All Procurements: `/pharmacy/procurements`
- ✅ Approve/Reject Procurements: `/pharmacy/procurements/{id}/act`
- ✅ View All Requisitions: `/pharmacy/requisitions`
- ✅ Approve/Reject Requisitions: `/pharmacy/requisitions/{id}/act`
- ✅ Manage Drug Catalog: `/pharmacy/drugs`
- ✅ View Stock: `/pharmacy/stocks`
- ✅ Adjust Stock: `/pharmacy/stocks/{id}/adjust`
- ✅ Reports: `/pharmacy/reports/*`

### Central Medical Store
- ✅ Dashboard: `/pharmacy/central-medical-store/dashboard`
- ✅ View Procurements: `/pharmacy/procurements`
- ✅ Receive Procurement: `/pharmacy/procurements/{id}/receive`
- ✅ View Requisitions: `/pharmacy/requisitions`
- ✅ Issue Requisition: `/pharmacy/requisitions/{id}/issue`
- ✅ View Stock: `/pharmacy/stocks`
- ✅ Adjust Stock: `/pharmacy/stocks/{id}/adjust`
- ✅ Manage Drug Catalog: `/pharmacy/drugs`

### Command Pharmacist
- ✅ Dashboard: `/pharmacy/command-pharmacist/dashboard`
- ✅ Create Requisition: `/pharmacy/requisitions/create`
- ✅ View My Requisitions: `/pharmacy/requisitions` (filtered to their command)
- ✅ Edit Draft: `/pharmacy/requisitions/{id}/edit`
- ✅ Submit: `/pharmacy/requisitions/{id}/submit`
- ✅ Dispense: `/pharmacy/requisitions/{id}/dispense`
- ✅ View Command Stock: `/pharmacy/stocks` (filtered to their command)
- ✅ View Drug Catalog: `/pharmacy/drugs`

---

## UI/UX Features

### ✅ Implemented
- Clean card-based layouts
- Status badges (color-coded)
- Responsive tables
- Breadcrumb navigation
- Success/Error alerts
- Workflow progress indicators
- Dashboard statistics
- Stock level indicators
- Expiry date warnings

### ⚠️ Missing (Needs Implementation)
- **Confirmation Dialogs**: No confirmation modals for critical actions
  - Submit Procurement/Requisition
  - Approve/Reject actions
  - Receive Procurement
  - Issue Requisition
  - Stock Adjustments
  - Dispense Requisition
- **Form Validation Feedback**: Could be enhanced
- **Loading States**: No loading indicators on form submissions

---

## Notifications

### Current Notification System
Notifications are sent via `NotificationService` for:
- ✅ Procurement submitted → OC Pharmacy notified
- ✅ Procurement approved/rejected → Controller Procurement notified
- ✅ Procurement approved → Central Medical Store notified
- ✅ Procurement received → Controller Procurement notified
- ✅ Requisition submitted → OC Pharmacy notified
- ✅ Requisition approved/rejected → Command Pharmacist notified
- ✅ Requisition approved → Central Medical Store notified
- ✅ Requisition issued → Command Pharmacist notified

### Notification Types
- Internal notifications (in-app)
- Email notifications (if configured)

---

## Status Flow Summary

### Procurement Statuses
1. **DRAFT** → Created but not submitted
2. **SUBMITTED** → Submitted for approval
3. **APPROVED** → Approved by OC Pharmacy, awaiting receipt
4. **RECEIVED** → Received by Central Medical Store, stock updated
5. **REJECTED** → Rejected by OC Pharmacy

### Requisition Statuses
1. **DRAFT** → Created but not submitted
2. **SUBMITTED** → Submitted for approval
3. **APPROVED** → Approved by OC Pharmacy, awaiting issue
4. **ISSUED** → Issued by Central Medical Store, stock transferred
5. **DISPENSED** → Dispensed by Command Pharmacist
6. **REJECTED** → Rejected by OC Pharmacy

---

## Key Features

### ✅ Working Features
- Multi-step workflow with role-based access
- Stock tracking (Central Store & Command Pharmacies)
- Batch number and expiry date tracking
- Stock movements/history
- Drug catalog management
- Reports (Stock Balance, Expiry, Custom)
- Command-scoped access for Command Pharmacist
- Dashboard with pending items and statistics

### 🔧 Recommended Improvements
1. **Add Confirmation Dialogs** for all critical actions
2. **Add Loading States** for form submissions
3. **Enhance Form Validation** with better error messages
4. **Add Print Functionality** for requisitions/procurements
5. **Add Search/Filter** enhancements
6. **Add Bulk Actions** where applicable
7. **Add Export Functionality** for reports

---

## Straightforward Testing Flow

### Test Procurement Flow
1. Login as **Controller Procurement**
2. Create new procurement → Add items → Save Draft
3. Submit procurement
4. Login as **OC Pharmacy** → Approve procurement
5. Login as **Central Medical Store** → Receive procurement → Enter batch/expiry → Confirm
6. Verify stock updated in central store

### Test Requisition Flow
1. Login as **Command Pharmacist**
2. Create new requisition → Select drugs → Add quantities → Save Draft
3. Submit requisition
4. Login as **OC Pharmacy** → Approve requisition
5. Login as **Central Medical Store** → Issue requisition → Enter quantities → Confirm
6. Verify stock transferred (central reduced, command increased)
7. Login as **Command Pharmacist** → Mark as dispensed
8. Verify command stock updated

### Test Stock Management
1. Login as **OC Pharmacy** or **Central Medical Store**
2. View stock → Select item → Adjust stock
3. Enter adjustment details → Confirm
4. Verify stock movement recorded

---

## Summary

The Medical/Pharmacy system is **functionally complete** with:
- ✅ Complete workflow for procurement and requisition
- ✅ Stock management (central and command levels)
- ✅ Role-based access control
- ✅ Notifications system
- ✅ Reports functionality
- ✅ Clean UI/UX

**Main Gap**: Missing confirmation dialogs for critical actions (similar to T&L system implementation needed).
