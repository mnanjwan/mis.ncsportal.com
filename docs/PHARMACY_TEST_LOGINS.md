# Pharmacy System - Test Login Credentials

## 🔑 Login Credentials

All passwords are: **`password`**

### Comptroller Procurement
- **Email**: `pharmacy.procurement@ncs.gov.ng`
- **Password**: `password`
- **Can Do**: Create and submit procurements

### Comptroller Pharmacy
- **Email**: `pharmacy.oc@ncs.gov.ng`
- **Password**: `password`
- **Can Do**: Approve/reject procurements and requisitions, manage drug catalog, view reports

### Central Medical Store
- **Email**: `pharmacy.store@ncs.gov.ng`
- **Password**: `password`
- **Can Do**: Receive procurements, issue requisitions, manage central stock

### Command Pharmacist
- **Email**: `pharmacy.command1@ncs.gov.ng`
- **Password**: `password`
- **Can Do**: Create requisitions, dispense drugs, view command stock

---

## 🧪 Test Data Created

### Stock with Expiry Scenarios
✅ **EXPIRED** - Items past expiry date (Red badge)
✅ **CRITICAL** - Items expiring ≤30 days (Red badge)
✅ **WARNING** - Items expiring 31-60 days (Yellow badge)
✅ **CAUTION** - Items expiring 61-90 days (Blue badge)
✅ **OK** - Items expiring >90 days (Green badge)
✅ **LOW STOCK** - Items with quantity < 10 (Red badge)

### Procurements Created
✅ **DRAFT** - Can be edited and submitted
✅ **SUBMITTED** - Pending Comptroller Pharmacy approval
✅ **APPROVED** - Pending Central Medical Store receipt
✅ **RECEIVED** - Fully received and stocked

### Requisitions Created
✅ **DRAFT** - Can be edited and submitted
✅ **SUBMITTED** - Pending Comptroller Pharmacy approval
✅ **APPROVED** - Pending Central Medical Store issue
✅ **ISSUED** - Pending Command Pharmacist dispense

### Drugs Created
✅ 10 common drugs across different categories:
- Paracetamol 500mg
- Amoxicillin 500mg
- Ibuprofen 400mg
- Artemether-Lumefantrine
- Ceftriaxone 1g
- Ciprofloxacin 500mg
- Azithromycin 500mg
- Normal Saline 0.9%
- Metronidazole 400mg
- Tramadol 50mg

---

## 🚀 Quick Start Testing

### 1. Run the Seeder
```bash
php artisan db:seed --class=PharmacyTestDataSeeder
```

### 2. Test Procurement Flow
1. Login as `pharmacy.procurement@ncs.gov.ng`
2. View draft procurement → Edit → Submit
3. Login as `pharmacy.oc@ncs.gov.ng` → Approve
4. Login as `pharmacy.store@ncs.gov.ng` → Receive

### 3. Test Requisition Flow
1. Login as `pharmacy.command1@ncs.gov.ng`
2. Create requisition → Submit
3. Login as `pharmacy.oc@ncs.gov.ng` → Approve
4. Login as `pharmacy.store@ncs.gov.ng` → Issue
5. Login as `pharmacy.command1@ncs.gov.ng` → Dispense

### 4. Test Expiry Warnings
1. Login as any role
2. Go to `/pharmacy/stocks?location_type=CENTRAL_STORE`
3. Verify:
   - Expired items show RED "Expired" badge
   - Items ≤30 days show RED "Critical" badge with days
   - Items 31-60 days show YELLOW "Warning" badge with days
   - Items 61-90 days show BLUE "Caution" badge with days
   - Items >90 days show GREEN "OK" badge

### 5. Test Stock Management
1. View Central Store stock → Verify quantities
2. Issue requisition → Verify central stock decreases, command stock increases
3. Dispense requisition → Verify command stock decreases

---

## 📋 Testing Checklist

- [ ] Login with all 4 test users
- [ ] Create procurement → Submit → Approve → Receive
- [ ] Create requisition → Submit → Approve → Issue → Dispense
- [ ] View stock with all expiry scenarios
- [ ] Verify expiry warnings display correctly
- [ ] Verify stock additions/subtractions work correctly
- [ ] Test low stock warnings
- [ ] View dashboards for each role
- [ ] Test reports (Comptroller Pharmacy only)
- [ ] Test drug catalog management

---

## 🎯 Expected Results

### Stock Management
- ✅ Receiving procurement → Central stock increases
- ✅ Issuing requisition → Central stock decreases, Command stock increases
- ✅ Dispensing → Command stock decreases

### Expiry Warnings
- ✅ Expired → Red "Expired" badge
- ✅ ≤30 days → Red "Critical" badge with days
- ✅ 31-60 days → Yellow "Warning" badge with days
- ✅ 61-90 days → Blue "Caution" badge with days
- ✅ >90 days → Green "OK" badge

### UI/UX
- ✅ All pages accessible
- ✅ Clean, consistent design
- ✅ Status badges color-coded
- ✅ Expiry warnings visible everywhere
