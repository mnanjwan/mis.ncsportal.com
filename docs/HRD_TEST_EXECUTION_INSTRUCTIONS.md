# HRD Test Execution Instructions

## ✅ Complete Testing Package Created

### Files Created:
1. **Test Data Seeder:** `database/seeders/HRDTestDataSeeder.php`
2. **Feature Tests:** `tests/Feature/HRDFeatureTest.php`
3. **User Guide:** `docs/HRD_USER_GUIDE.md`
4. **Testing Guide:** `docs/HRD_TESTING_GUIDE.md`

---

## 🚀 Step-by-Step Execution

### Step 1: Seed Test Data

**Command:**
```bash
php artisan db:seed --class=HRDTestDataSeeder
```

**What This Does:**
- Creates 50 officers (surname: TEST1-TEST50)
- Creates 5 commands
- Creates staff orders, movement orders, manning requests
- Creates promotion criteria and lists
- Creates retirement lists
- Creates leave types, courses, system settings
- **Does NOT delete existing data** - uses `firstOrCreate`

**Expected Output:**
```
Creating HRD Test Data...
HRD Test Data created successfully!
```

**Verify:**
```bash
php artisan tinker
\App\Models\Officer::where('surname', 'LIKE', 'TEST%')->count(); // Should return 50
\App\Models\StaffOrder::where('order_number', 'LIKE', 'SO-HRD-%')->count(); // Should return 10
```

---

### Step 2: Run Automated Tests

**Command:**
```bash
php artisan test --filter HRDFeatureTest
```

**What Tests Cover:**
- ✅ All view pages load
- ✅ All forms submit
- ✅ All data saves to database
- ✅ Workflow automations trigger
- ✅ Exclusions work (interdicted officers excluded)

**Expected Output:**
- Tests should pass (some may need route/view fixes)
- Check output for specific failures

---

### Step 3: Manual Testing

**Login:**
- Email: `hrd@ncs.gov.ng`
- Password: `password123`

**Follow Workflows:**
See `docs/HRD_USER_GUIDE.md` for complete step-by-step workflows

**Test Each Function:**
1. Navigate to page (View test)
2. Click button/form (Controller test)
3. Verify data in database (Database test)

---

## 📋 Manual Testing Checklist

### Test Each Button/Form (View → Controller → Database)

#### 1. Staff Orders
- [ ] Click "Create Staff Order" button → Form loads
- [ ] Fill form and submit → Data saves to `staff_orders` table
- [ ] Edit order, change status to PUBLISHED → Officer's `present_station` updates

**Database Check:**
```sql
SELECT * FROM staff_orders WHERE order_number LIKE 'SO-HRD-%';
SELECT id, present_station FROM officers WHERE id = [officer_id];
```

#### 2. Movement Orders
- [ ] Click "Create Movement Order" → Form loads
- [ ] Submit form → Data saves to `movement_orders` table

**Database Check:**
```sql
SELECT * FROM movement_orders WHERE order_number LIKE 'MO-HRD-%';
```

#### 3. Promotion Eligibility
- [ ] Click "Generate Eligibility List" → Form loads
- [ ] Submit form → List created in `promotion_eligibility_lists`
- [ ] Verify interdicted officers NOT in `promotion_eligibility_list_items`

**Database Check:**
```sql
SELECT * FROM promotion_eligibility_lists;
SELECT * FROM promotion_eligibility_list_items WHERE eligibility_list_id = [list_id];
-- Verify no interdicted officers
```

#### 4. Retirement List
- [ ] Click "Generate Retirement List" → Form loads
- [ ] Submit form → List created in `retirement_list`
- [ ] Verify items created in `retirement_list_items`

**Database Check:**
```sql
SELECT * FROM retirement_list;
SELECT * FROM retirement_list_items WHERE retirement_list_id = [list_id];
```

#### 5. Leave Types
- [ ] Click "Create Leave Type" → Form loads
- [ ] Submit form → Data saves to `leave_types` table

**Database Check:**
```sql
SELECT * FROM leave_types WHERE code = 'TL';
```

#### 6. Course Nominations
- [ ] Click "Nominate Officer" → Form loads
- [ ] Submit form → Data saves to `officer_courses` table

**Database Check:**
```sql
SELECT * FROM officer_courses;
```

#### 7. System Settings
- [ ] View settings page → Settings display
- [ ] Update setting → Data updates in `system_settings` table

**Database Check:**
```sql
SELECT * FROM system_settings WHERE setting_key = 'retirement_age';
```

#### 8. Onboarding
- [ ] Click "Initiate" on officer → Form loads
- [ ] Submit form → User created in `users` table
- [ ] Verify officer linked (officer.user_id set)

**Database Check:**
```sql
SELECT * FROM users WHERE email = '[test_email]';
SELECT user_id FROM officers WHERE id = [officer_id];
```

---

## 🔍 Database Verification Queries

### Check Test Data Exists

```sql
-- Officers
SELECT COUNT(*) FROM officers WHERE surname LIKE 'TEST%';

-- Staff Orders
SELECT COUNT(*) FROM staff_orders WHERE order_number LIKE 'SO-HRD-%';

-- Movement Orders
SELECT COUNT(*) FROM movement_orders WHERE order_number LIKE 'MO-HRD-%';

-- Promotion Lists
SELECT COUNT(*) FROM promotion_eligibility_lists;

-- Retirement Lists
SELECT COUNT(*) FROM retirement_list;

-- Leave Types
SELECT COUNT(*) FROM leave_types;

-- Courses
SELECT COUNT(*) FROM officer_courses;

-- System Settings
SELECT COUNT(*) FROM system_settings;
```

### Check Workflow Automation

```sql
-- After publishing staff order, check officer updated
SELECT o.id, o.present_station, so.to_command_id 
FROM officers o
JOIN staff_orders so ON o.id = so.officer_id
WHERE so.order_number LIKE 'SO-HRD-%'
AND o.present_station = so.to_command_id; -- Should match if published
```

### Check Exclusions

```sql
-- Verify interdicted officers NOT in promotion lists
SELECT o.id, o.surname, o.interdicted
FROM officers o
WHERE o.interdicted = 1
AND o.id NOT IN (
    SELECT officer_id FROM promotion_eligibility_list_items
); -- Should return interdicted officers
```

---

## 📝 Test Execution Workflow

### For Each HRD Function:

1. **View Test:**
   - Navigate to page
   - Verify page loads
   - Verify data displays

2. **Button Test:**
   - Click create/edit/delete button
   - Verify form/page loads
   - Verify no errors

3. **Form Submission Test:**
   - Fill form
   - Click submit
   - Verify success message
   - Verify redirect

4. **Database Test:**
   - Check database for new/updated record
   - Verify all fields saved correctly
   - Verify relationships work

5. **Workflow Test:**
   - Trigger workflow (e.g., publish order)
   - Verify automation triggers
   - Verify related data updates

---

## ✅ Success Indicators

### Automated Tests
- ✅ Tests run without fatal errors
- ✅ Most tests pass
- ✅ Assertions verify data

### Manual Testing
- ✅ All pages load
- ✅ All buttons work
- ✅ All forms submit
- ✅ All data saves
- ✅ Workflows complete

### Database Verification
- ✅ Test data exists
- ✅ Relationships work
- ✅ Workflow automations trigger
- ✅ Data persists

---

## 🎯 Quick Test Commands

```bash
# Seed data
php artisan db:seed --class=HRDTestDataSeeder

# Run tests
php artisan test --filter HRDFeatureTest

# Check data
php artisan tinker
\App\Models\Officer::where('surname', 'LIKE', 'TEST%')->count();

# View logs
tail -f storage/logs/laravel.log
```

---

## 📚 Documentation Files

1. **HRD_USER_GUIDE.md** - Complete workflows
2. **HRD_TESTING_GUIDE.md** - Testing procedures
3. **HRD_TEST_EXECUTION_SUMMARY.md** - Test summary
4. **HRD_COMPLETE_TESTING_PACKAGE.md** - Package overview
5. **HRD_TEST_EXECUTION_INSTRUCTIONS.md** - This file

---

**Status:** ✅ Complete  
**Ready for Testing:** Yes  
**Data Persistence:** Enabled

