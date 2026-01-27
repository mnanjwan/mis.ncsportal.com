# Pharmacy Module Testing Guide

A comprehensive guide to test all features of the Pharmacy Workflow System.

---

## Test Credentials

| Role | Email | Password | Primary Function |
|------|-------|----------|------------------|
| **Controller Procurement** | `officer69@ncs.gov.ng` | `password123` | Create and submit procurement requests |
| **OC Pharmacy** | `officer88@ncs.gov.ng` | `password123` | Approve procurements & requisitions, view reports |
| **Central Medical Store** | `officer99@ncs.gov.ng` | `password123` | Receive procurements, issue requisitions |
| **Command Pharmacist** | `officer97@ncs.gov.ng` | `password123` | Create requisitions, dispense drugs |
| **Command Pharmacist 1** | `command.pharmacist.1@ncs.gov.ng` | `password123` | Command: CGC OFFICE |
| **Command Pharmacist 2** | `command.pharmacist.2@ncs.gov.ng` | `password123` | Command: FATS-HQTRS |
| **Command Pharmacist 3** | `command.pharmacist.3@ncs.gov.ng` | `password123` | Command: SR&P-HQTRS |

---

## System URL

- **Base URL**: `http://127.0.0.1:8000`
- **Login**: `http://127.0.0.1:8000/login`

---

## Workflow Overview

### Procurement Workflow (Controller Procurement → OC Pharmacy → Central Medical Store)

```
┌─────────────────────┐     ┌─────────────────────┐     ┌─────────────────────┐
│ Controller          │     │ OC Pharmacy         │     │ Central Medical     │
│ Procurement         │     │                     │     │ Store               │
├─────────────────────┤     ├─────────────────────┤     ├─────────────────────┤
│ 1. Create Draft     │────▶│ 2. Review & Approve │────▶│ 3. Receive Items    │
│ 2. Submit           │     │    or Reject        │     │    Update Stock     │
└─────────────────────┘     └─────────────────────┘     └─────────────────────┘
```

### Requisition Workflow (Command Pharmacist → OC Pharmacy → Central Medical Store → Command Pharmacist)

```
┌─────────────────────┐     ┌─────────────────────┐     ┌─────────────────────┐     ┌─────────────────────┐
│ Command Pharmacist  │     │ OC Pharmacy         │     │ Central Medical     │     │ Command Pharmacist  │
│                     │     │                     │     │ Store               │     │                     │
├─────────────────────┤     ├─────────────────────┤     ├─────────────────────┤     ├─────────────────────┤
│ 1. Create Draft     │────▶│ 2. Review & Approve │────▶│ 3. Issue Items      │────▶│ 4. Confirm Receipt  │
│ 2. Submit           │     │    or Reject        │     │    from Central     │     │    & Dispense       │
└─────────────────────┘     └─────────────────────┘     └─────────────────────┘     └─────────────────────┘
```

---

## Complete Testing Workflow

### Phase 1: Controller Procurement Testing

**Login**: `officer69@ncs.gov.ng` / `password123`

#### Dashboard Verification
- [ ] Navigate to `/pharmacy/controller-procurement/dashboard`
- [ ] Verify dashboard shows:
  - My Procurements count
  - Pending Approval count
  - Draft Procurements list
  - Recent Activity

#### Drug Catalog (View Only)
- [ ] Navigate to `/pharmacy/drugs`
- [ ] Verify drug list displays (33 drugs seeded)
- [ ] Click on any drug to view details
- [ ] Verify you CANNOT add/edit drugs (no Add button visible)

#### Stock Overview (View Only)
- [ ] Navigate to `/pharmacy/stocks`
- [ ] Verify Central Store stock displays
- [ ] Switch to Command Pharmacy tab
- [ ] Click on a drug to view stock details

#### Create New Procurement
- [ ] Navigate to `/pharmacy/procurements/create`
- [ ] Add items:
  - Click "Add Item"
  - Enter Drug Name: `Test Drug ABC`
  - Enter Quantity: `1000`
  - Select Unit: `tablets`
- [ ] Add another item with different drug name
- [ ] Add optional notes
- [ ] Click "Create Draft"
- [ ] Verify redirect to procurement details page

#### Edit Draft Procurement
- [ ] From procurement list, find a DRAFT procurement
- [ ] Click Edit
- [ ] Modify quantity or add new item
- [ ] Save changes
- [ ] Verify changes are saved

#### Submit Procurement
- [ ] From a DRAFT procurement, click "Submit for Approval"
- [ ] Confirm submission
- [ ] Verify status changes to SUBMITTED

#### View Procurement History
- [ ] Navigate to `/pharmacy/procurements`
- [ ] Verify all states visible: DRAFT, SUBMITTED, APPROVED, RECEIVED, REJECTED

---

### Phase 2: OC Pharmacy Testing

**Login**: `officer88@ncs.gov.ng` / `password123`

#### Dashboard Verification
- [ ] Navigate to `/pharmacy/oc-pharmacy/dashboard`
- [ ] Verify dashboard shows:
  - Pending Approvals (procurements + requisitions)
  - Stock Overview
  - Recent Activity
  - Expiring Soon alerts

#### Drug Catalog (Full Access)
- [ ] Navigate to `/pharmacy/drugs`
- [ ] Click "Add Drug" button
- [ ] Create new drug:
  - Name: `Test Drug XYZ`
  - Unit: `capsules`
  - Category: `Test Category`
- [ ] Verify drug is created
- [ ] Edit the drug you created
- [ ] Toggle drug active/inactive

#### Approve Procurement
- [ ] Navigate to `/pharmacy/procurements`
- [ ] Find a SUBMITTED procurement
- [ ] Click to view details
- [ ] Click "Approve" button
- [ ] Add comment: "Approved for procurement"
- [ ] Verify status changes to APPROVED

#### Reject Procurement (Test Rejection)
- [ ] Find another SUBMITTED procurement (or create one)
- [ ] Click to view details
- [ ] Click "Reject" button
- [ ] Add reason: "Budget constraints"
- [ ] Verify status changes to REJECTED

#### Approve Requisition
- [ ] Navigate to `/pharmacy/requisitions`
- [ ] Find a SUBMITTED requisition
- [ ] Click to view details
- [ ] Click "Approve" button
- [ ] Add comment
- [ ] Verify status changes to APPROVED

#### Reports Testing
- [ ] Navigate to `/pharmacy/reports/stock-balance`
  - [ ] Verify stock balance report displays
  - [ ] Test print button
- [ ] Navigate to `/pharmacy/reports/expiry`
  - [ ] Verify expiring/expired items show
  - [ ] Test print button
- [ ] Navigate to `/pharmacy/reports/custom`
  - [ ] Select date range
  - [ ] Generate report
  - [ ] Test print button

---

### Phase 3: Central Medical Store Testing

**Login**: `officer99@ncs.gov.ng` / `password123`

#### Dashboard Verification
- [ ] Navigate to `/pharmacy/central-medical-store/dashboard`
- [ ] Verify dashboard shows:
  - Pending Receipts (approved procurements)
  - Pending Issues (approved requisitions)
  - Stock Summary
  - Low Stock alerts

#### Receive Procurement
- [ ] Navigate to `/pharmacy/procurements`
- [ ] Find an APPROVED procurement
- [ ] Click to view details
- [ ] Click "Receive" button
- [ ] For each item:
  - Enter Quantity Received
  - Enter Batch Number (e.g., `BATCH-2026-001`)
  - Enter Expiry Date (future date)
- [ ] Submit receipt
- [ ] Verify status changes to RECEIVED
- [ ] Verify stock is updated (check `/pharmacy/stocks`)

#### Issue Requisition
- [ ] Navigate to `/pharmacy/requisitions`
- [ ] Find an APPROVED requisition
- [ ] Click to view details
- [ ] Click "Issue" button
- [ ] For each item:
  - Enter Quantity Issued
- [ ] Submit issue
- [ ] Verify status changes to ISSUED

#### Stock Management
- [ ] Navigate to `/pharmacy/stocks`
- [ ] View Central Store stock
- [ ] Click on a drug to view details
- [ ] Test stock adjustment:
  - Click "Adjust Stock"
  - Enter adjustment quantity (+ or -)
  - Enter reason
  - Submit
  - Verify stock is updated

#### Drug Catalog Management
- [ ] Navigate to `/pharmacy/drugs`
- [ ] Add a new drug
- [ ] Edit existing drug
- [ ] Verify changes

---

### Phase 4: Command Pharmacist Testing

**Login**: `command.pharmacist.1@ncs.gov.ng` / `password123` (for CGC OFFICE)

#### Dashboard Verification
- [ ] Navigate to `/pharmacy/command-pharmacist/dashboard`
- [ ] Verify dashboard shows:
  - Your command name in breadcrumbs
  - My Requisitions
  - Command Stock
  - Ready to Dispense

#### Command Isolation Testing (IMPORTANT)
- [ ] Verify you can ONLY see data for your assigned command
- [ ] You should NOT see other commands' requisitions
- [ ] You should NOT see other commands' stock
- [ ] Command filter dropdown should NOT be visible in stock page

#### Create Requisition
- [ ] Navigate to `/pharmacy/requisitions/create`
- [ ] Add items:
  - Select Drug from dropdown (only drugs in catalog)
  - Enter Quantity
- [ ] Add notes
- [ ] Click "Create Draft"
- [ ] Verify requisition is created with your command

#### Submit Requisition
- [ ] From a DRAFT requisition, click "Submit"
- [ ] Confirm submission
- [ ] Verify status changes to SUBMITTED

#### Dispense Drugs
- [ ] Navigate to `/pharmacy/requisitions`
- [ ] Find an ISSUED requisition
- [ ] Click to view details
- [ ] Click "Confirm Receipt & Dispense" button
- [ ] Verify status changes to DISPENSED

#### Stock View (Command Only)
- [ ] Navigate to `/pharmacy/stocks`
- [ ] Switch to "Command Pharmacy" tab
- [ ] Verify you ONLY see stock for your command
- [ ] Command filter should be hidden or locked

---

### Phase 5: Cross-Role Workflow Testing

#### Complete Procurement Cycle
1. **Controller Procurement** creates and submits procurement
2. **OC Pharmacy** approves procurement
3. **Central Medical Store** receives items
4. Verify stock is added to Central Store

#### Complete Requisition Cycle
1. **Command Pharmacist** creates and submits requisition
2. **OC Pharmacy** approves requisition
3. **Central Medical Store** issues items
4. **Command Pharmacist** dispenses
5. Verify stock movements are recorded

---

## Page Reference by Role

### Controller Procurement Pages
| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/pharmacy/controller-procurement/dashboard` | Overview |
| Procurements | `/pharmacy/procurements` | List all procurements |
| Create Procurement | `/pharmacy/procurements/create` | New procurement |
| View Procurement | `/pharmacy/procurements/{id}` | Details |
| Edit Procurement | `/pharmacy/procurements/{id}/edit` | Edit draft |
| Drug Catalog | `/pharmacy/drugs` | View drugs |
| Stock | `/pharmacy/stocks` | View stock |

### OC Pharmacy Pages
| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/pharmacy/oc-pharmacy/dashboard` | Overview |
| Procurements | `/pharmacy/procurements` | View/approve |
| Requisitions | `/pharmacy/requisitions` | View/approve |
| Drug Catalog | `/pharmacy/drugs` | Manage drugs |
| Add Drug | `/pharmacy/drugs/create/new` | Create drug |
| Stock | `/pharmacy/stocks` | View all stock |
| Stock Balance Report | `/pharmacy/reports/stock-balance` | Report |
| Expiry Report | `/pharmacy/reports/expiry` | Report |
| Custom Report | `/pharmacy/reports/custom` | Report |

### Central Medical Store Pages
| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/pharmacy/central-medical-store/dashboard` | Overview |
| Procurements | `/pharmacy/procurements` | View/receive |
| Requisitions | `/pharmacy/requisitions` | View/issue |
| Drug Catalog | `/pharmacy/drugs` | Manage drugs |
| Stock | `/pharmacy/stocks` | Manage stock |

### Command Pharmacist Pages
| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/pharmacy/command-pharmacist/dashboard` | Overview |
| Requisitions | `/pharmacy/requisitions` | Manage requisitions |
| Create Requisition | `/pharmacy/requisitions/create` | New requisition |
| View Requisition | `/pharmacy/requisitions/{id}` | Details |
| Edit Requisition | `/pharmacy/requisitions/{id}/edit` | Edit draft |
| Drug Catalog | `/pharmacy/drugs` | View drugs |
| Stock | `/pharmacy/stocks` | View command stock |

---

## Expected Seeded Data

### Procurements (6 records)
- 1x DRAFT
- 1x SUBMITTED (pending OC Pharmacy)
- 1x REJECTED
- 1x APPROVED (pending receipt)
- 2x RECEIVED (1 fully, 1 partially)

### Requisitions (7 records)
- 1x DRAFT
- 1x SUBMITTED (pending OC Pharmacy)
- 1x REJECTED
- 1x APPROVED (pending issue)
- 2x ISSUED (1 fully, 1 partially)
- 1x DISPENSED (completed)

### Stock Records
- Central Store: 15+ drugs with stock
- Command Pharmacies: 3 commands with distributed stock
- Expiry conditions: Good (12-24 months), Expiring Soon (1-3 months), Expired

### Drugs (33 drugs)
- Categories: Analgesics, Antibiotics, Antimalarials, Antihypertensives, Antidiabetics, Gastrointestinal, Respiratory, IV Fluids, Emergency

---

## Testing Checklist Summary

### Core Functionality
- [ ] All 4 role dashboards load correctly
- [ ] Sidebar shows correct menu for each role
- [ ] Role isolation works (Command Pharmacist sees only their command)

### Procurement Workflow
- [ ] Create draft procurement
- [ ] Edit draft procurement
- [ ] Submit procurement
- [ ] Approve procurement (OC Pharmacy)
- [ ] Reject procurement (OC Pharmacy)
- [ ] Receive procurement (Central Medical Store)
- [ ] Stock updates after receipt

### Requisition Workflow
- [ ] Create draft requisition
- [ ] Edit draft requisition
- [ ] Submit requisition
- [ ] Approve requisition (OC Pharmacy)
- [ ] Reject requisition (OC Pharmacy)
- [ ] Issue requisition (Central Medical Store)
- [ ] Dispense requisition (Command Pharmacist)

### Stock Management
- [ ] View Central Store stock
- [ ] View Command Pharmacy stock
- [ ] Stock adjustment works
- [ ] Stock movements recorded
- [ ] Expiry tracking works

### Drug Catalog
- [ ] Add new drug
- [ ] Edit drug
- [ ] Toggle drug active/inactive
- [ ] View drug details

### Reports
- [ ] Stock Balance Report
- [ ] Expiry Report
- [ ] Custom Report
- [ ] Print functionality

### Print Functions
- [ ] Print procurement details
- [ ] Print requisition details
- [ ] Print reports

---

## Notification Testing

Notifications are sent both **in-app** (stored in database) and via **email** (queued job).

### Notification Flow by Action

| Action | Who Gets Notified | Notification Type |
|--------|-------------------|-------------------|
| **Submit Procurement** | OC Pharmacy users | `pharmacy_procurement_pending` |
| **Approve Procurement** | Creator + Central Medical Store | `pharmacy_procurement_update` + `pharmacy_procurement_pending` |
| **Reject Procurement** | Creator | `pharmacy_procurement_update` |
| **Receive Procurement** | Creator | `pharmacy_procurement_update` |
| **Submit Requisition** | OC Pharmacy users | `pharmacy_requisition_pending` |
| **Approve Requisition** | Creator + Central Medical Store | `pharmacy_requisition_update` + `pharmacy_requisition_pending` |
| **Reject Requisition** | Creator | `pharmacy_requisition_update` |
| **Issue Requisition** | Creator | `pharmacy_requisition_update` |
| **Dispense Requisition** | OC Pharmacy users | `pharmacy_requisition_update` |

### Testing Notifications

#### In-App Notifications
1. Login as a user who should receive notifications
2. Look for the notification bell icon in the header
3. Click to view unread notifications
4. Verify notifications appear for relevant actions

#### Email Notifications
1. Ensure mail configuration is set in `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=your-smtp-host
   MAIL_PORT=587
   MAIL_USERNAME=your-username
   MAIL_PASSWORD=your-password
   MAIL_FROM_ADDRESS=noreply@ncs.gov.ng
   ```
2. Run queue worker (if using queued emails):
   ```bash
   php artisan queue:work
   ```
3. Perform actions and check recipient email inbox

#### Notification Test Checklist

**Procurement Notifications:**
- [ ] Submit procurement → OC Pharmacy receives notification
- [ ] Approve procurement → Creator + Central Store receive notifications
- [ ] Reject procurement → Creator receives notification
- [ ] Receive procurement → Creator receives notification

**Requisition Notifications:**
- [ ] Submit requisition → OC Pharmacy receives notification
- [ ] Approve requisition → Creator + Central Store receive notifications
- [ ] Reject requisition → Creator receives notification
- [ ] Issue requisition → Creator (Command Pharmacist) receives notification
- [ ] Dispense requisition → OC Pharmacy receives notification

### Viewing Notifications in Database

```sql
-- View recent pharmacy notifications
SELECT * FROM notifications 
WHERE notification_type LIKE 'pharmacy_%' 
ORDER BY created_at DESC 
LIMIT 20;

-- View notifications for specific user
SELECT * FROM notifications 
WHERE user_id = [USER_ID] 
AND notification_type LIKE 'pharmacy_%' 
ORDER BY created_at DESC;
```

---

## Troubleshooting

### "No drugs available" message
- Drugs are added to catalog when:
  1. OC Pharmacy/Central Medical Store adds them manually
  2. Procurements are received (auto-creates drugs)
- Run seeder: `php artisan db:seed --class=PharmacySeeder`

### "You are not assigned to any command"
- Command Pharmacist must have a `command_id` in their role assignment
- Check user_roles pivot table

### Empty dashboards
- Run seeder: `php artisan db:seed --class=PharmacySeeder`
- Check user has correct role assigned

### Sidebar not visible
- Ensure user has one of: Controller Procurement, OC Pharmacy, Central Medical Store, Command Pharmacist roles
- Clear cache: `php artisan cache:clear && php artisan view:clear`

---

## Quick Commands

```bash
# Run seeder to populate test data
php artisan db:seed --class=PharmacySeeder

# Clear all caches
php artisan cache:clear && php artisan config:clear && php artisan view:clear && php artisan route:clear

# Start development server
php artisan serve

# Check routes
php artisan route:list --name=pharmacy
```

---

**Happy Testing!** 🎉
