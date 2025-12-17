# HRD Complete Testing Package

## 📦 What's Included

### 1. Test Data Seeder ✅
**File:** `database/seeders/HRDTestDataSeeder.php`

**Purpose:** Creates comprehensive test data for all HRD functions

**Features:**
- ✅ Does NOT delete existing data (uses `firstOrCreate`)
- ✅ Creates 50 officers with various statuses
- ✅ Creates all necessary supporting data
- ✅ Identifiable test data (HRD prefix, TEST surname)

**Run:**
```bash
php artisan db:seed --class=HRDTestDataSeeder
```

---

### 2. Feature Tests ✅
**File:** `tests/Feature/HRDFeatureTest.php`

**Coverage:** 25+ tests covering all 18 HRD core functions

**Test Types:**
- **View Tests:** Verify pages load correctly
- **Controller Tests:** Verify forms submit and process
- **Database Tests:** Verify data is saved correctly
- **Workflow Tests:** Verify automations trigger

**Run:**
```bash
php artisan test --filter HRDFeatureTest
```

---

### 3. User Guide ✅
**File:** `docs/HRD_USER_GUIDE.md`

**Contents:**
- Complete workflows for all 18 functions
- Step-by-step instructions
- View → Controller → Database flow explanations
- Testing checklist
- Troubleshooting guide

---

### 4. Testing Guide ✅
**File:** `docs/HRD_TESTING_GUIDE.md`

**Contents:**
- Quick start instructions
- Test coverage details
- Manual testing procedures
- Verification steps

---

## 🚀 Quick Start

### Step 1: Seed Test Data
```bash
php artisan db:seed --class=HRDTestDataSeeder
```

**Expected Output:**
```
Creating HRD Test Data...
HRD Test Data created successfully!
```

**What Happens:**
- Creates commands, officers, orders, lists, etc.
- Data persists (not deleted)
- You can view results in UI

---

### Step 2: Run Automated Tests
```bash
php artisan test --filter HRDFeatureTest
```

**Expected Output:**
```
PASS  Tests\Feature\HRDFeatureTest
✓ hrd_can_access_dashboard
✓ hrd_can_view_officers_list
✓ hrd_can_create_staff_order
... (25+ tests)
```

**What Happens:**
- Tests all views load
- Tests all forms submit
- Tests all data saves
- Tests workflow automations

---

### Step 3: Manual Testing
1. Login: `hrd@ncs.gov.ng` / `password123`
2. Follow workflows in `HRD_USER_GUIDE.md`
3. Verify buttons work, forms submit, data displays

---

## 📋 Test Coverage by Function

### ✅ Function 1: Generate Staff Orders
- **Test:** `hrd_can_create_staff_order`
- **Test:** `hrd_can_publish_staff_order_and_update_officer`
- **Verifies:** View loads → Form submits → Data saves → Officer updates

### ✅ Function 2: Generate Movement Orders
- **Test:** `hrd_can_create_movement_order`
- **Verifies:** View loads → Form submits → Data saves

### ✅ Function 3: Onboard Serving Officers
- **Test:** `hrd_can_initiate_onboarding`
- **Verifies:** View loads → Form submits → User created → Officer linked

### ✅ Function 4: Generate Eligibility List for Promotion
- **Test:** `hrd_can_generate_promotion_eligibility_list`
- **Test:** `promotion_eligibility_excludes_interdicted_officers`
- **Verifies:** View loads → Form submits → List created → Exclusions work

### ✅ Function 5: Generate Retirement List
- **Test:** `hrd_can_generate_retirement_list`
- **Verifies:** View loads → Form submits → List created

### ✅ Function 6: Nominate Officers for courses
- **Test:** `hrd_can_nominate_officer_for_course`
- **Verifies:** View loads → Form submits → Course saved

### ✅ Function 7: Create timeline for Officers to raise emolument
- **Test:** `hrd_can_create_emolument_timeline`
- **Test:** `hrd_can_extend_emolument_timeline`
- **Verifies:** View loads → Form submits → Timeline created/extended

### ✅ Function 8: Trigger the system to match criteria for Manning Level requests
- **Test:** `hrd_can_view_manning_requests`
- **Verifies:** View loads → Requests displayed

### ✅ Function 9: Alter Staff Orders and Movement Orders
- **Test:** `hrd_can_publish_staff_order_and_update_officer`
- **Verifies:** Edit works → Updates save

### ✅ Function 10: Set the number of years that an officer will stay on the rank to be eligible for promotion
- **Test:** `hrd_can_create_promotion_criteria`
- **Verifies:** View loads → Form submits → Criteria saved

### ✅ Function 11: Create new types of leave and assign duration
- **Test:** `hrd_can_create_leave_type`
- **Verifies:** View loads → Form submits → Leave type saved

### ✅ Function 12: Onboard officers for NCS Employee App
- **Test:** `hrd_can_initiate_onboarding`
- **Verifies:** User created → Email sent → Officer linked

### ✅ Function 13: Create, extend, and manage emolument timelines
- **Test:** `hrd_can_create_emolument_timeline`
- **Test:** `hrd_can_extend_emolument_timeline`
- **Verifies:** Full timeline management works

### ✅ Function 14: Generate and process staff orders
- **Test:** `hrd_can_create_staff_order`
- **Test:** `hrd_can_publish_staff_order_and_update_officer`
- **Verifies:** Order creation and workflow automation

### ✅ Function 15: Create movement orders based on tenure criteria or manning requirements
- **Test:** `hrd_can_create_movement_order`
- **Verifies:** Both methods work

### ✅ Function 16: Override posting decisions when necessary
- **Test:** `hrd_can_publish_staff_order_and_update_officer`
- **Verifies:** Edit and update works

### ✅ Function 17: System-wide configuration and parameter management
- **Test:** `hrd_can_update_system_settings`
- **Verifies:** Settings persist

### ✅ Function 18: Generate comprehensive system reports
- **Test:** `hrd_can_view_reports`
- **Verifies:** Reports page loads

---

## 🔍 Test Flow Verification

### Example: Staff Order Creation

**1. View Test:**
```php
$response = $this->actingAs($this->hrdUser)
    ->get(route('hrd.staff-orders.create'));
$response->assertStatus(200);
$response->assertViewIs('forms.staff-order.create');
```
✅ **Button Works:** "Create Staff Order" button loads the form

**2. Controller Test:**
```php
$response = $this->actingAs($this->hrdUser)
    ->post(route('hrd.staff-orders.store'), [...]);
$response->assertRedirect(route('hrd.staff-orders'));
$response->assertSessionHas('success');
```
✅ **Form Submits:** Submit button processes the form

**3. Database Test:**
```php
$this->assertDatabaseHas('staff_orders', [
    'officer_id' => $officer->id,
    'from_command_id' => $fromCommand->id,
    'to_command_id' => $toCommand->id,
]);
```
✅ **Data Saved:** Data is correctly stored in database

**4. Workflow Test:**
```php
// Publish order
$order->update(['status' => 'PUBLISHED']);
// Verify officer updated
$this->assertEquals($toCommand->id, $officer->present_station);
```
✅ **Workflow Triggers:** Publishing order updates officer automatically

---

## 📊 Test Data Summary

### Created Data (Persistent)

| Data Type | Count | Identification |
|-----------|-------|----------------|
| Commands | 5 | Codes: LAG, ABJ, KAN, PHC, IBD |
| Officers | 50 | Surname: TEST1-TEST50 |
| Staff Orders | 10 | Order Number: SO-HRD-YYYY-XXX |
| Movement Orders | 3 | Order Number: MO-HRD-YYYY-XXX |
| Manning Requests | 5 | Status: APPROVED |
| Promotion Criteria | 5 | One per rank |
| Promotion Lists | 3 | Years: Y-1, Y, Y+1 |
| Retirement Lists | 3 | Years: Y, Y+1, Y+2 |
| Leave Types | 4 | Codes: AL, SL, ML, STL |
| Course Nominations | 10 | Various courses |
| System Settings | 9 | All settings |

---

## ✅ Verification Steps

### After Seeding
1. **Check Database:**
   ```bash
   php artisan tinker
   \App\Models\Officer::where('surname', 'LIKE', 'TEST%')->count();
   \App\Models\StaffOrder::where('order_number', 'LIKE', 'SO-HRD-%')->count();
   ```

2. **Check UI:**
   - Login as HRD
   - Navigate to Officers → Should see TEST officers
   - Navigate to Staff Orders → Should see SO-HRD- orders
   - Navigate to Promotion Eligibility → Should see lists

3. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### After Running Tests
1. **Check Test Output:**
   - All tests should pass ✅
   - No errors in output

2. **Check Database:**
   - Test data should still exist
   - New test data may be created

3. **Verify Workflows:**
   - Staff order publish updates officer
   - Promotion list excludes interdicted officers
   - Onboarding creates user account

---

## 🎯 Testing Workflows

### Workflow 1: Staff Order → Officer Update

**Steps:**
1. Create staff order (status: DRAFT)
2. Verify order in database
3. Edit order, change status to PUBLISHED
4. Verify officer's `present_station` updated in database

**Test:** `hrd_can_publish_staff_order_and_update_officer`

---

### Workflow 2: Promotion Eligibility → Exclusions

**Steps:**
1. Create interdicted officer
2. Create promotion criteria
3. Generate eligibility list
4. Verify interdicted officer NOT in list

**Test:** `promotion_eligibility_excludes_interdicted_officers`

---

### Workflow 3: Onboarding → User Creation

**Steps:**
1. Find officer without user account
2. Initiate onboarding
3. Enter email
4. Verify user account created
5. Verify officer linked to user
6. Verify Officer role assigned

**Test:** `hrd_can_initiate_onboarding`

---

## 📝 Manual Testing Checklist

After automated tests pass, perform manual testing:

### Views
- [ ] All pages load without errors
- [ ] All data displays correctly
- [ ] Filters work
- [ ] Pagination works
- [ ] Breadcrumbs display

### Forms
- [ ] All forms submit successfully
- [ ] Validation errors display
- [ ] Success messages display
- [ ] Error messages display
- [ ] Auto-fill features work (e.g., from command)

### Buttons
- [ ] Create buttons work
- [ ] Edit buttons work
- [ ] Delete buttons work
- [ ] View buttons work
- [ ] Action buttons work

### Database
- [ ] Data saves correctly
- [ ] Data updates correctly
- [ ] Data deletes correctly (when allowed)
- [ ] Relationships work correctly

### Workflows
- [ ] Staff order publish updates officer
- [ ] Promotion list excludes ineligible officers
- [ ] Onboarding creates user account
- [ ] System settings persist

---

## 🐛 Troubleshooting

### Tests Failing
1. **Check database:** Run migrations
2. **Check test data:** Run seeder
3. **Check logs:** Review error messages
4. **Check routes:** Verify routes exist

### Data Not Appearing
1. **Check seeder:** Run seeder again
2. **Check database:** Verify data exists
3. **Check filters:** Verify filters aren't hiding data
4. **Check relationships:** Verify relationships are loaded

### Workflows Not Triggering
1. **Check status:** Verify status is correct (e.g., PUBLISHED)
2. **Check logs:** Review workflow service logs
3. **Check database:** Verify data is correct
4. **Check service:** Verify PostingWorkflowService is called

---

## 📚 Documentation Files

1. **HRD_USER_GUIDE.md** - Complete workflows and instructions
2. **HRD_TESTING_GUIDE.md** - Testing procedures and checklist
3. **HRD_TEST_EXECUTION_SUMMARY.md** - Test execution summary
4. **HRD_COMPLETE_TESTING_PACKAGE.md** - This file

---

## ✅ Success Criteria

### Automated Tests
- ✅ All 25+ tests pass
- ✅ No errors in test output
- ✅ All assertions pass

### Manual Testing
- ✅ All pages load
- ✅ All forms work
- ✅ All buttons work
- ✅ All data displays
- ✅ All workflows complete

### Database Verification
- ✅ Test data exists
- ✅ Data relationships work
- ✅ Workflow automations trigger
- ✅ Data persists correctly

---

## 🎉 Ready for Testing!

**Everything is set up:**
- ✅ Test data seeder created
- ✅ Feature tests created
- ✅ User guide created
- ✅ Testing guide created
- ✅ Data persists (not deleted)

**Next Steps:**
1. Run seeder: `php artisan db:seed --class=HRDTestDataSeeder`
2. Run tests: `php artisan test --filter HRDFeatureTest`
3. Manual testing: Follow `HRD_USER_GUIDE.md`
4. Verify results: Check database and UI

---

**Status:** ✅ Complete  
**Test Coverage:** All 18 HRD Core Functions  
**Data Persistence:** Enabled

