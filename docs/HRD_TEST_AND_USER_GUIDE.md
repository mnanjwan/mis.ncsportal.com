# HRD Test Suite & User Guide - Complete Package

## 📦 Complete Package Delivered

### ✅ What's Been Created

1. **Test Data Seeder** - `database/seeders/HRDTestDataSeeder.php`
2. **Feature Tests** - `tests/Feature/HRDFeatureTest.php`
3. **User Guide** - `docs/HRD_USER_GUIDE.md`
4. **Testing Guide** - `docs/HRD_TESTING_GUIDE.md`
5. **Execution Instructions** - `docs/HRD_TEST_EXECUTION_INSTRUCTIONS.md`

---

## 🚀 Quick Start

### Step 1: Seed Test Data
```bash
php artisan db:seed --class=HRDTestDataSeeder
```

**Result:** Creates 50 officers, orders, lists, settings - all test data
**Note:** Data is NOT deleted - persists for viewing

### Step 2: Run Tests
```bash
php artisan test --filter HRDFeatureTest
```

**Result:** Tests all HRD functions (View → Controller → Database)

### Step 3: Manual Testing
1. Login: `hrd@ncs.gov.ng` / `password123`
2. Follow workflows in `HRD_USER_GUIDE.md`
3. Test each button and verify in database

---

## 📋 Complete Workflow Testing

### For Each HRD Function, Test:

#### 1. View (Button Click Test)
- Navigate to page
- Click button (Create, Edit, View)
- ✅ Verify page/form loads
- ✅ Verify no errors

#### 2. Controller (Form Submission Test)
- Fill form with test data
- Click submit button
- ✅ Verify success message
- ✅ Verify redirect
- ✅ Verify no errors

#### 3. Database (Data Verification Test)
- Open database/tinker
- Query for new record
- ✅ Verify record exists
- ✅ Verify all fields saved correctly
- ✅ Verify relationships work

---

## 🎯 Testing All 18 Functions

### Function 1: Generate Staff Orders

**Workflow:**
1. Navigate to `/hrd/staff-orders`
2. Click "Create Staff Order" button
3. Fill form (officer, commands, dates)
4. Click "Create Staff Order" button
5. Verify order in database
6. Edit order, set status to PUBLISHED
7. Verify officer's `present_station` updated

**Database Check:**
```sql
SELECT * FROM staff_orders WHERE order_number LIKE 'SO-HRD-%';
SELECT id, present_station FROM officers WHERE id = [officer_id];
```

---

### Function 2: Generate Movement Orders

**Workflow:**
1. Navigate to `/hrd/movement-orders`
2. Click "Create Movement Order" button
3. Select manning request OR enter criteria
4. Click "Create Movement Order" button
5. Verify order in database

**Database Check:**
```sql
SELECT * FROM movement_orders WHERE order_number LIKE 'MO-HRD-%';
```

---

### Function 3: Onboard Serving Officers

**Workflow:**
1. Navigate to `/hrd/onboarding`
2. View "Officers Needing Onboarding"
3. Click "Initiate" button on an officer
4. Enter email address
5. Click "Initiate Onboarding" button
6. Verify user account created
7. Verify officer linked to user

**Database Check:**
```sql
SELECT * FROM users WHERE email = '[entered_email]';
SELECT user_id FROM officers WHERE id = [officer_id];
SELECT * FROM user_roles WHERE user_id = [user_id] AND role_id = [officer_role_id];
```

---

### Function 4: Generate Eligibility List for Promotion

**Workflow:**
1. Navigate to `/hrd/promotion-eligibility`
2. Click "Generate Eligibility List" button
3. Enter year
4. Click "Generate List" button
5. Verify list created
6. Verify interdicted officers NOT included

**Database Check:**
```sql
SELECT * FROM promotion_eligibility_lists WHERE year = [year];
SELECT * FROM promotion_eligibility_list_items WHERE eligibility_list_id = [list_id];
-- Verify no interdicted officers
SELECT o.id FROM officers o
JOIN promotion_eligibility_list_items p ON o.id = p.officer_id
WHERE o.interdicted = 1 AND p.eligibility_list_id = [list_id];
-- Should return 0 rows
```

---

### Function 5: Generate Retirement List

**Workflow:**
1. Navigate to `/hrd/retirement-list`
2. Click "Generate Retirement List" button
3. Enter year
4. Click "Generate List" button
5. Verify list created with officers

**Database Check:**
```sql
SELECT * FROM retirement_list WHERE year = [year];
SELECT * FROM retirement_list_items WHERE retirement_list_id = [list_id];
```

---

### Function 6: Nominate Officers for courses

**Workflow:**
1. Navigate to `/hrd/courses`
2. Click "Nominate Officer" button
3. Fill form (officer, course, dates)
4. Click "Nominate Officer" button
5. Verify course in database
6. Mark course as completed
7. Verify completion status

**Database Check:**
```sql
SELECT * FROM officer_courses WHERE officer_id = [officer_id];
SELECT is_completed, completion_date FROM officer_courses WHERE id = [course_id];
```

---

### Function 7: Create timeline for Officers to raise emolument

**Workflow:**
1. Navigate to `/hrd/emolument-timeline`
2. Click "Create Timeline" button
3. Fill form (year, dates)
4. Click "Create Timeline" button
5. Verify timeline in database
6. Extend timeline
7. Verify extension saved

**Database Check:**
```sql
SELECT * FROM emolument_timelines WHERE year = [year];
SELECT is_extended, extension_end_date FROM emolument_timelines WHERE id = [timeline_id];
```

---

### Function 8: Trigger the system to match criteria for Manning Level requests

**Workflow:**
1. Navigate to `/hrd/manning-requests`
2. View approved requests
3. Click on a request
4. Click "Find Matches" for an item
5. Verify matched officers displayed
6. Select officers
7. Generate movement order
8. Verify order created

**Database Check:**
```sql
SELECT * FROM manning_requests WHERE status = 'APPROVED';
SELECT * FROM movement_orders WHERE manning_request_id = [request_id];
```

---

### Function 9: Alter Staff Orders and Movement Orders

**Workflow:**
1. View staff order
2. Click "Edit" button
3. Modify fields
4. Click "Update" button
5. Verify changes in database

**Database Check:**
```sql
SELECT * FROM staff_orders WHERE id = [order_id];
```

---

### Function 10: Set the number of years that an officer will stay on the rank to be eligible for promotion

**Workflow:**
1. Navigate to `/hrd/promotion-criteria`
2. Click "Add Criteria" button
3. Fill form (rank, years)
4. Click "Save Criteria" button
5. Verify criteria in database

**Database Check:**
```sql
SELECT * FROM promotion_eligibility_criteria WHERE rank = '[rank]';
```

---

### Function 11: Create new types of leave and assign duration

**Workflow:**
1. Navigate to `/hrd/leave-types`
2. Click "Create Leave Type" button
3. Fill form (name, code, duration)
4. Click "Save Leave Type" button
5. Verify leave type in database

**Database Check:**
```sql
SELECT * FROM leave_types WHERE code = '[code]';
```

---

### Function 12: Onboard officers for NCS Employee App

**Workflow:**
1. Navigate to `/hrd/onboarding`
2. Initiate onboarding (see Function 3)
3. Verify email sent (check mailtrap/logs)
4. Verify user account created
5. Verify officer can login

**Database Check:**
```sql
SELECT * FROM users WHERE email = '[email]';
SELECT user_id FROM officers WHERE id = [officer_id];
```

---

### Function 13: Create, extend, and manage emolument timelines

**Workflow:**
1. Create timeline (see Function 7)
2. Extend timeline
3. Verify extension in database

**Database Check:**
```sql
SELECT is_extended, extension_end_date FROM emolument_timelines WHERE id = [timeline_id];
```

---

### Function 14: Generate and process staff orders

**Workflow:**
1. Create staff order (see Function 1)
2. Publish order
3. Verify officer updated

**Database Check:**
```sql
SELECT present_station FROM officers WHERE id = [officer_id];
-- Should match to_command_id from staff_order
```

---

### Function 15: Create movement orders based on tenure criteria or manning requirements

**Workflow:**
1. Create movement order (see Function 2)
2. Verify order in database

**Database Check:**
```sql
SELECT * FROM movement_orders WHERE order_number = '[order_number]';
```

---

### Function 16: Override posting decisions when necessary

**Workflow:**
1. Edit staff order (see Function 9)
2. Change posting
3. Verify officer updated

**Database Check:**
```sql
SELECT present_station FROM officers WHERE id = [officer_id];
```

---

### Function 17: System-wide configuration and parameter management

**Workflow:**
1. Navigate to `/hrd/system-settings`
2. Modify setting value
3. Click "Save Settings" button
4. Verify setting updated in database
5. Refresh page, verify change persists

**Database Check:**
```sql
SELECT setting_value FROM system_settings WHERE setting_key = '[key]';
```

---

### Function 18: Generate comprehensive system reports

**Workflow:**
1. Navigate to `/hrd/reports`
2. Select report type
3. Select date range
4. Click "Generate Report" button
5. Verify file downloads

**Database Check:**
- Reports query database directly
- Verify data exists for report type

---

## 🔍 Database Verification Queries

### Check All Test Data

```sql
-- Officers
SELECT COUNT(*) as officer_count FROM officers WHERE surname LIKE 'TEST%';

-- Staff Orders
SELECT COUNT(*) as staff_order_count FROM staff_orders WHERE order_number LIKE 'SO-HRD-%';

-- Movement Orders
SELECT COUNT(*) as movement_order_count FROM movement_orders WHERE order_number LIKE 'MO-HRD-%';

-- Promotion Lists
SELECT COUNT(*) as promotion_list_count FROM promotion_eligibility_lists;

-- Retirement Lists
SELECT COUNT(*) as retirement_list_count FROM retirement_list;

-- Leave Types
SELECT COUNT(*) as leave_type_count FROM leave_types;

-- Courses
SELECT COUNT(*) as course_count FROM officer_courses;

-- System Settings
SELECT COUNT(*) as setting_count FROM system_settings;
```

### Check Workflow Automation

```sql
-- Staff Order Workflow
SELECT 
    so.order_number,
    o.id as officer_id,
    o.present_station as officer_station,
    so.to_command_id as order_destination
FROM staff_orders so
JOIN officers o ON so.officer_id = o.id
WHERE so.order_number LIKE 'SO-HRD-%'
AND o.present_station = so.to_command_id;
-- If published, officer_station should match order_destination
```

### Check Exclusions

```sql
-- Promotion Eligibility Exclusions
SELECT 
    o.id,
    o.surname,
    o.interdicted,
    o.suspended,
    o.dismissed,
    o.is_deceased
FROM officers o
WHERE o.interdicted = 1 
   OR o.suspended = 1
   OR o.dismissed = 1
   OR o.is_deceased = 1
AND o.id IN (
    SELECT officer_id FROM promotion_eligibility_list_items
);
-- Should return 0 rows (excluded officers not in lists)
```

---

## ✅ Complete Testing Checklist

### Automated Tests
- [ ] Run: `php artisan test --filter HRDFeatureTest`
- [ ] Verify tests pass
- [ ] Check for any failures

### Manual Testing - Views
- [ ] Dashboard loads
- [ ] Officers list loads
- [ ] Staff orders list loads
- [ ] Movement orders list loads
- [ ] Emolument timeline loads
- [ ] Promotion criteria loads
- [ ] Promotion eligibility loads
- [ ] Retirement list loads
- [ ] Leave types loads
- [ ] Manning requests loads
- [ ] Courses loads
- [ ] System settings loads
- [ ] Onboarding loads
- [ ] Reports loads

### Manual Testing - Buttons
- [ ] Create buttons work
- [ ] Edit buttons work
- [ ] View buttons work
- [ ] Delete buttons work (where applicable)
- [ ] Submit buttons work
- [ ] Action buttons work

### Manual Testing - Forms
- [ ] All forms submit successfully
- [ ] Validation errors display
- [ ] Success messages display
- [ ] Error messages display
- [ ] Auto-fill features work

### Manual Testing - Database
- [ ] Data saves correctly
- [ ] Data updates correctly
- [ ] Relationships work
- [ ] Workflow automations trigger
- [ ] Exclusions work

---

## 📊 Expected Test Data

After running seeder, you should have:

- **50 Officers** with surname TEST1-TEST50
- **10 Staff Orders** with order numbers SO-HRD-YYYY-XXX
- **3 Movement Orders** with order numbers MO-HRD-YYYY-XXX
- **5 Manning Requests** (approved)
- **5 Promotion Criteria** (one per rank)
- **3 Promotion Eligibility Lists** (with officers)
- **3 Retirement Lists** (with officers)
- **4 Leave Types**
- **10 Course Nominations**
- **9 System Settings**

---

## 🎉 Success!

**Everything is ready for testing:**
- ✅ Test data seeder created and working
- ✅ Feature tests created
- ✅ User guide with complete workflows
- ✅ Testing guide created
- ✅ Data persists (not deleted)

**You can now:**
1. View test data in the UI
2. Test all buttons and forms
3. Verify data in database
4. See workflow automations in action

---

**Status:** ✅ Complete  
**Ready for Testing:** Yes  
**Data Persistence:** Enabled

