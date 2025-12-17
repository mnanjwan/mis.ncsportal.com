# NCS Employee Portal - Comprehensive Functionality Testing Guide

## Overview
This document provides a comprehensive testing guide for all core functionalities in the NCS Employee Portal, organized by role. Each section includes test cases, expected behaviors, and SweetAlert2 integration points.

---

## SweetAlert2 Integration

All confirmation dialogs and alerts now use SweetAlert2 with custom site colors (#088a56).

### Configuration
- **Confirm Button**: Green (#088a56)
- **Cancel Button**: Gray (#6c757d)
- **Success Icon**: Green (#088a56)
- **Custom Styling**: Matches site UI

### Usage Example
```javascript
Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'No, cancel'
}).then((result) => {
    if (result.isConfirmed) {
        // Perform action
        Swal.fire('Deleted!', 'Your file has been deleted.', 'success');
    }
});
```

---

## Role-by-Role Testing

### 1. HRD (Human Resources Department)

#### Login Credentials
- **Email**: `hrd@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `hrd.dashboard` | Access dashboard | Display statistics, recent activities | N/A |
| Officers List | `hrd.officers` | View all officers | Display 50+ officers in table | N/A |
| View Officer | `hrd.officers.show` | Click on officer | Display full officer profile | N/A |
| Edit Officer | `hrd.officers.edit` | Edit officer details | Update officer information | Confirm before save |
| Staff Orders | `hrd.staff-orders` | View staff orders | Display all staff orders | N/A |
| Create Staff Order | `hrd.staff-orders.create` | Create new order | Form to create staff order | Confirm before submit |
| Retirement List | `hrd.retirement-list` | View retirement list | Display officers due for retirement | N/A |
| Generate Retirement | `hrd.retirement-list.generate` | Generate new list | Create retirement list | Confirm generation |
| Promotion Eligibility | `hrd.promotion-eligibility` | View eligible officers | Display promotion-eligible officers | N/A |
| Emolument Timeline | `hrd.emolument-timeline` | Manage timeline | Create/view emolument timelines | Confirm before create |

#### Test Execution
```bash
php artisan test --filter=HRDFunctionalityTest
```

---

### 2. Staff Officer

#### Login Credentials
- **Email**: `staff@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `staff-officer.dashboard` | Access dashboard | Display pending applications, manning level | N/A |
| Leave & Pass Management | `staff-officer.leave-pass.index` | View applications | Display leave and pass applications in tabs | N/A |
| Minute Application | `staff-officer.leave-pass.minute` | Minute an application | Add minute to application | Confirm before submit |
| Approve Application | `staff-officer.leave-pass.approve` | Approve application | Change status to APPROVED | Confirm approval |
| Reject Application | `staff-officer.leave-pass.reject` | Reject application | Change status to REJECTED | Confirm rejection with reason |
| Manning Level | `staff-officer.manning.index` | View manning level | Display current manning statistics | N/A |
| Duty Roster | `staff-officer.duty-roster.index` | View duty roster | Display duty assignments | N/A |
| Create Roster | `staff-officer.duty-roster.create` | Create new roster | Form to create duty roster | Confirm before save |

#### Test Execution
```bash
php artisan test --filter=StaffOfficerFunctionalityTest
```

---

### 3. Officer

#### Login Credentials
- **Email**: `officer@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `officer.dashboard` | Access dashboard | Display quick actions, service info | N/A |
| Profile | `officer.profile` | View profile | Display complete officer profile | N/A |
| Edit Profile | `officer.profile.edit` | Update profile | Update personal information | Confirm before save |
| Raise Emolument | `officer.emoluments.index` | View emoluments | Display emolument history | N/A |
| Submit Emolument | `officer.emoluments.create` | Submit new emolument | Create emolument record | Confirm submission |
| Apply for Leave | `officer.leave.create` | Submit leave application | Create leave application | Confirm submission |
| Apply for Pass | `officer.pass.create` | Submit pass application | Create pass application | Confirm submission |
| View Applications | `officer.applications` | View all applications | Display leave/pass history | N/A |

---

### 4. Assessor

#### Login Credentials
- **Email**: `assessor@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `assessor.dashboard` | Access dashboard | Display pending emoluments for assessment | N/A |
| View Emoluments | `assessor.emoluments.index` | View RAISED emoluments | Display emoluments with RAISED status | N/A |
| Assess Emolument | `assessor.emoluments.assess` | Assess an emolument | Change status to ASSESSED | Confirm assessment |
| Reject Emolument | `assessor.emoluments.reject` | Reject an emolument | Change status to REJECTED | Confirm rejection with reason |

---

### 5. Validator

#### Login Credentials
- **Email**: `validator@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `validator.dashboard` | Access dashboard | Display pending emoluments for validation | N/A |
| View Emoluments | `validator.emoluments.index` | View ASSESSED emoluments | Display emoluments with ASSESSED status | N/A |
| Validate Emolument | `validator.emoluments.validate` | Validate an emolument | Change status to VALIDATED | Confirm validation |
| Reject Emolument | `validator.emoluments.reject` | Reject an emolument | Change status to REJECTED | Confirm rejection with reason |

---

### 6. Accounts

#### Login Credentials
- **Email**: `accounts@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `accounts.dashboard` | Access dashboard | Display validated emoluments, deceased officers | N/A |
| View Validated | `accounts.emoluments.validated` | View VALIDATED emoluments | Display emoluments ready for processing | N/A |
| Process Emolument | `accounts.emoluments.process` | Process an emolument | Change status to PROCESSED | Confirm processing |
| View Deceased | `accounts.deceased.index` | View deceased officers | Display deceased officers for benefits | N/A |
| Process Benefits | `accounts.deceased.process` | Process death benefits | Mark benefits as processed | Confirm processing |

---

### 7. Welfare

#### Login Credentials
- **Email**: `welfare@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `welfare.dashboard` | Access dashboard | Display deceased officers statistics | N/A |
| View Deceased | `welfare.deceased.index` | View deceased officers | Display all deceased officers | N/A |
| Record Deceased | `welfare.deceased.create` | Record new deceased officer | Create deceased officer record | Confirm submission |
| Update Record | `welfare.deceased.update` | Update deceased record | Update deceased officer information | Confirm before save |

---

### 8. Building Unit

#### Login Credentials
- **Email**: `building.unit@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `building.dashboard` | Access dashboard | Display quarters statistics | N/A |
| View Quarters | `building.quarters.index` | View all quarters | Display quarters inventory | N/A |
| Allocate Quarter | `building.quarters.allocate` | Allocate quarter to officer | Create allocation record | Confirm allocation |
| Deallocate Quarter | `building.quarters.deallocate` | Remove officer from quarter | Remove allocation | Confirm deallocation |

---

### 9. Area Controller

#### Login Credentials
- **Email**: `area.controller@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `area-controller.dashboard` | Access dashboard | Display area statistics | N/A |
| View Commands | `area-controller.commands.index` | View assigned commands | Display commands under control | N/A |
| View Officers | `area-controller.officers.index` | View officers in area | Display officers in assigned commands | N/A |

---

### 10. DC Admin

#### Login Credentials
- **Email**: `dc.admin@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `dc-admin.dashboard` | Access dashboard | Display DC statistics | N/A |
| View Officers | `dc-admin.officers.index` | View all officers | Display officers in DC | N/A |

---

### 11. Board

#### Login Credentials
- **Email**: `board@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `board.dashboard` | Access dashboard | Display board-level reports | N/A |
| View Reports | `board.reports.index` | View all reports | Display comprehensive reports | N/A |

---

### 12. Establishment

#### Login Credentials
- **Email**: `establishment@ncs.gov.ng`
- **Password**: `password123`

#### Core Functionalities

| Feature | Route | Test Case | Expected Behavior | SweetAlert Integration |
|---------|-------|-----------|-------------------|----------------------|
| Dashboard | `establishment.dashboard` | Access dashboard | Display establishment statistics | N/A |
| Manning Requests | `establishment.manning.index` | View manning requests | Display all manning requests | N/A |
| Approve Request | `establishment.manning.approve` | Approve manning request | Change status to APPROVED | Confirm approval |

---

## Testing Workflow

### 1. Manual Testing (Browser)
1. Login with role credentials
2. Navigate through each feature
3. Test all actions (create, read, update, delete)
4. Verify SweetAlert2 confirmations appear
5. Check data persistence

### 2. Automated Testing (PHPUnit)
```bash
# Run all functionality tests
php artisan test --filter=Functionality

# Run specific role tests
php artisan test --filter=HRDFunctionalityTest
php artisan test --filter=StaffOfficerFunctionalityTest
php artisan test --filter=EmolumentWorkflowTest
php artisan test --filter=WelfareFunctionalityTest
php artisan test --filter=BuildingUnitFunctionalityTest
```

### 3. API Testing (Postman/Insomnia)
Test all API endpoints with proper authentication:
```
GET /api/v1/officers
GET /api/v1/leave-applications
GET /api/v1/pass-applications
GET /api/v1/emoluments
GET /api/v1/deceased-officers
```

---

## Common Issues & Solutions

### Issue 1: Route Not Found
**Solution**: Check `routes/web.php` for route definition

### Issue 2: Unauthorized Access
**Solution**: Verify user has correct role assigned

### Issue 3: SweetAlert Not Showing
**Solution**: Run `npm run build` to compile assets

### Issue 4: Data Not Persisting
**Solution**: Check database connection and migrations

---

## Next Steps

1. ✅ SweetAlert2 integrated with site colors
2. ✅ Comprehensive test suites created
3. ⏳ Run manual browser testing for each role
4. ⏳ Fix any failing automated tests
5. ⏳ Document any bugs found
6. ⏳ Create user acceptance testing (UAT) checklist

---

## Test Results Summary

| Role | Dashboard | Core Features | Actions | Status |
|------|-----------|---------------|---------|--------|
| HRD | ✅ | ⏳ | ⏳ | In Progress |
| Staff Officer | ⏳ | ⏳ | ⏳ | Pending |
| Officer | ⏳ | ⏳ | ⏳ | Pending |
| Assessor | ⏳ | ⏳ | ⏳ | Pending |
| Validator | ⏳ | ⏳ | ⏳ | Pending |
| Accounts | ⏳ | ⏳ | ⏳ | Pending |
| Welfare | ⏳ | ⏳ | ⏳ | Pending |
| Building Unit | ⏳ | ⏳ | ⏳ | Pending |
| Area Controller | ⏳ | ⏳ | ⏳ | Pending |
| DC Admin | ⏳ | ⏳ | ⏳ | Pending |
| Board | ⏳ | ⏳ | ⏳ | Pending |
| Establishment | ⏳ | ⏳ | ⏳ | Pending |

---

**Last Updated**: 2025-12-14
**Version**: 1.0
