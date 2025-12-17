# HRD User Guide - Complete Workflows

## Table of Contents
1. [Getting Started](#getting-started)
2. [Dashboard Overview](#dashboard-overview)
3. [Officer Management](#officer-management)
4. [Staff Orders Workflow](#staff-orders-workflow)
5. [Movement Orders Workflow](#movement-orders-workflow)
6. [Emolument Timeline Management](#emolument-timeline-management)
7. [Promotion Management](#promotion-management)
8. [Retirement Management](#retirement-management)
9. [Leave Type Management](#leave-type-management)
10. [Manning Request Processing](#manning-request-processing)
11. [Course Nomination](#course-nomination)
12. [Officer Onboarding](#officer-onboarding)
13. [System Settings](#system-settings)
14. [Reports Generation](#reports-generation)

---

## Getting Started

### Login Credentials
- **Email:** `hrd@ncs.gov.ng`
- **Password:** `password123`

### Accessing the System
1. Navigate to the login page
2. Enter your credentials
3. Click "Login"
4. You will be redirected to the HRD Dashboard

---

## Dashboard Overview

### What You'll See
- **Total Officers:** Count of all active officers in the system
- **Pending Emoluments:** Number of emoluments awaiting processing
- **Active Timeline:** Current emolument submission timeline
- **Staff Orders:** Count of staff orders
- **Recent Officers:** Last 5 officers registered
- **Emolument Status Breakdown:** Chart showing emolument status distribution

### Quick Actions
- View recent officers by clicking on their names
- Navigate to different sections using the sidebar menu

---

## Officer Management

### Viewing Officers List

**Workflow:**
1. Click **"Officers"** in the sidebar
2. View the list of all officers
3. Use filters:
   - **Search:** Search by name or service number
   - **Rank:** Filter by officer rank
   - **Command:** Filter by command

**What Happens:**
- View loads → Controller fetches officers → Database query executed → Results displayed

**Test:** Navigate to `/hrd/officers` and verify officers are displayed

### Viewing Officer Details

**Workflow:**
1. From Officers list, click on an officer's name
2. View complete officer profile:
   - Personal information
   - Employment details
   - Banking information
   - Next of kin
   - Documents

**What Happens:**
- Click officer → Controller loads officer with relationships → Database query → View displays details

**Test:** Click any officer from the list and verify all details are shown

---

## Staff Orders Workflow

### Creating a Staff Order

**Workflow:**
1. Click **"Orders"** → **"Staff Orders"** in sidebar
2. Click **"Create Staff Order"** button
3. Fill in the form:
   - **Order Number:** Auto-generated (editable)
   - **Officer:** Search and select officer (from command auto-fills)
   - **From Command:** Auto-filled when officer selected
   - **To Command:** Select destination command
   - **Effective Date:** Select posting date
   - **Order Type:** Select type (POSTING, TRANSFER, etc.)
4. Click **"Create Staff Order"**

**What Happens:**
- Form submitted → Controller validates → StaffOrder created in DB → Redirect with success message

**Test:** Create a staff order and verify it appears in the list

### Publishing a Staff Order

**Workflow:**
1. View staff order details
2. Click **"Edit"**
3. Change status to **"PUBLISHED"**
4. Click **"Update"**

**What Happens:**
- Status changed to PUBLISHED → PostingWorkflowService triggered → Officer's `present_station` updated in DB → Logs activity

**Test:** 
1. Create staff order with status DRAFT
2. Edit and change to PUBLISHED
3. Verify officer's `present_station` is updated in database

### Viewing Staff Orders

**Workflow:**
1. Click **"Orders"** → **"Staff Orders"**
2. View list of all staff orders
3. Click on order number to view details

**What Happens:**
- Page loads → Controller fetches orders → Database query → View displays orders

**Test:** Navigate to staff orders list and verify orders are displayed

---

## Movement Orders Workflow

### Creating Movement Order from Manning Request

**Workflow:**
1. Click **"Orders"** → **"Movement Orders"**
2. Click **"Create Movement Order"**
3. Select **Manning Request** from dropdown
4. Fill in additional details
5. Click **"Create Movement Order"**

**What Happens:**
- Form submitted → Controller validates → MovementOrder created → Redirect with success

**Test:** Create movement order and verify it's saved

### Creating Movement Order with Criteria

**Workflow:**
1. Click **"Create Movement Order"**
2. Enter **Months at Station** criteria (e.g., 24 months)
3. System finds officers matching criteria
4. Select officers to post
5. Create order

**What Happens:**
- Criteria entered → Controller queries officers → Matches displayed → Order created

**Test:** Create movement order with criteria and verify matching officers

---

## Emolument Timeline Management

### Creating Emolument Timeline

**Workflow:**
1. Click **"Emolument Timeline"** in sidebar
2. Click **"Create Timeline"**
3. Fill in:
   - **Year:** Select year
   - **Start Date:** When officers can start submitting
   - **End Date:** Submission deadline
   - **Description:** Optional notes
   - **Active:** Check to activate
4. Click **"Create Timeline"**

**What Happens:**
- Form submitted → Controller validates → EmolumentTimeline created → If active, other timelines deactivated

**Test:** Create timeline and verify it appears in list

### Extending Emolument Timeline

**Workflow:**
1. From timeline list, click **"Extend"** on a timeline
2. Enter new end date
3. Enter reason for extension
4. Click **"Extend Timeline"**

**What Happens:**
- Extension submitted → Controller updates timeline → `is_extended` set to true → `extension_end_date` saved

**Test:** Extend a timeline and verify extension date is saved

### Automatic Extension (Cron Job)

**Workflow:**
- System automatically checks timelines daily at 8:00 AM
- If timeline ends within 3 days, automatically extends by 7 days
- Logs all extensions

**What Happens:**
- Cron job runs → ExtendEmolumentTimeline command executes → Timeline extended → Logged

**Test:** Run `php artisan emolument:extend-timeline` manually to test

---

## Promotion Management

### Setting Promotion Criteria

**Workflow:**
1. Click **"Promotions & Retirement"** → **"Promotion Criteria"**
2. Click **"Add Criteria"**
3. Fill in:
   - **Rank:** Select rank
   - **Years in Rank Required:** Enter number of years
   - **Active:** Check to activate
4. Click **"Save Criteria"**

**What Happens:**
- Form submitted → Controller validates → PromotionEligibilityCriterion created → Saved to DB

**Test:** Create criteria and verify it appears in list

### Generating Promotion Eligibility List

**Workflow:**
1. Click **"Promotions & Retirement"** → **"Promotion Eligibility"**
2. Click **"Generate Eligibility List"**
3. Enter **Year**
4. Click **"Generate List"**

**What Happens:**
- Year submitted → Controller checks criteria → Finds eligible officers (excludes interdicted, suspended, dismissed, deceased) → Creates list with items

**Test:** 
1. Create promotion criteria
2. Generate eligibility list
3. Verify only eligible officers are included
4. Verify interdicted officers are excluded

### Viewing Promotion Eligibility List

**Workflow:**
1. From eligibility list page, click on a list
2. View all officers in the list with their details

**What Happens:**
- List clicked → Controller loads list with items → View displays officers

**Test:** Click on a list and verify officers are displayed

### Deleting Empty Promotion List

**Workflow:**
1. From eligibility list page
2. Find list with 0 officers
3. Click **"Delete"** button
4. Confirm deletion

**What Happens:**
- Delete clicked → Controller checks if list has items → If empty, deletes → Redirect with success

**Test:** Create empty list and verify it can be deleted

---

## Retirement Management

### Generating Retirement List

**Workflow:**
1. Click **"Promotions & Retirement"** → **"Retirement List"**
2. Click **"Generate Retirement List"**
3. Enter **Year**
4. Click **"Generate List"**

**What Happens:**
- Year submitted → Controller finds officers reaching age 60 or 35 years service → Creates list with items → Calculates pre-retirement leave dates

**Test:** Generate retirement list and verify officers are included

### Viewing Retirement List

**Workflow:**
1. From retirement list page, click on a list
2. View all officers with retirement details

**What Happens:**
- List clicked → Controller loads list → View displays officers

**Test:** Click on a list and verify officers are displayed

### Automatic Pre-Retirement Status Activation

**Workflow:**
- System checks daily for officers whose pre-retirement date has arrived
- Automatically activates pre-retirement status
- Marks as notified

**What Happens:**
- Cron job runs → RetirementService checks lists → Activates status → Logs activity

**Test:** Create retirement list with past pre-retirement dates and verify status activation

---

## Leave Type Management

### Creating Leave Type

**Workflow:**
1. Click **"Leave Types"** in sidebar
2. Click **"Create Leave Type"**
3. Fill in:
   - **Name:** Leave type name
   - **Code:** Short code
   - **Max Duration:** Days or months
   - **Max Occurrences:** Per year
   - **Medical Certificate:** Required or not
   - **Approval Level:** Required approval
   - **Active:** Check to activate
4. Click **"Save Leave Type"**

**What Happens:**
- Form submitted → Controller validates → LeaveType created → Saved to DB

**Test:** Create leave type and verify it appears in list

### Editing Leave Type

**Workflow:**
1. From leave types list, click **"Edit"**
2. Modify fields
3. Click **"Update Leave Type"**

**What Happens:**
- Form submitted → Controller validates → LeaveType updated → Saved to DB

**Test:** Edit a leave type and verify changes are saved

### Deleting Leave Type

**Workflow:**
1. From leave types list, click **"Delete"**
2. Confirm deletion

**What Happens:**
- Delete clicked → Controller checks if leave type has applications → If none, deletes → Redirect

**Test:** Try deleting leave type with and without applications

---

## Manning Request Processing

### Viewing Approved Manning Requests

**Workflow:**
1. Click **"Manning Requests"** in sidebar
2. View list of approved requests
3. Click on request to view details

**What Happens:**
- Page loads → Controller fetches approved requests → View displays

**Test:** Navigate to manning requests and verify approved requests are shown

### Matching Officers to Manning Request

**Workflow:**
1. View manning request details
2. For each requirement item, click **"Find Matches"**
3. System displays matched officers (by rank, sex, qualification)
4. Select officers (up to quantity needed)
5. Click **"Generate Movement Order"**

**What Happens:**
- Match clicked → Controller queries officers → Filters by criteria → Displays matches → Movement order created

**Test:** 
1. View manning request
2. Click "Find Matches"
3. Verify matched officers are displayed
4. Select officers and generate order

---

## Course Nomination

### Nominating Officer for Course

**Workflow:**
1. Click **"Course Nominations"** in sidebar
2. Click **"Nominate Officer"**
3. Fill in:
   - **Officer:** Select officer
   - **Course Name:** Enter course name
   - **Course Type:** MANDATORY or OPTIONAL
   - **Start Date:** Course start
   - **End Date:** Course end
   - **Notes:** Optional
4. Click **"Nominate Officer"**

**What Happens:**
- Form submitted → Controller validates → OfficerCourse created → Saved to DB

**Test:** Nominate officer and verify course appears in list

### Marking Course as Completed

**Workflow:**
1. View course details
2. Fill in completion form:
   - **Completion Date:** When course completed
   - **Certificate URL:** Optional
   - **Notes:** Optional
3. Click **"Mark as Completed"**

**What Happens:**
- Form submitted → Controller updates course → `is_completed` set to true → Recorded in officer's record

**Test:** Mark course as completed and verify status changes

---

## Officer Onboarding

### Initiating Onboarding

**Workflow:**
1. Click **"Officer Onboarding"** in sidebar
2. View officers needing onboarding
3. Click **"Initiate"** on an officer
4. Enter **Email Address**
5. Click **"Initiate Onboarding"**

**What Happens:**
- Form submitted → Controller creates user account → Generates onboarding link → Sends email → Links officer to user → Assigns Officer role

**Test:** 
1. Initiate onboarding for an officer
2. Verify user account is created
3. Verify email is sent (check mailtrap/logs)
4. Verify officer is linked to user

### Resending Onboarding Link

**Workflow:**
1. From onboarded officers section
2. Click **"Resend Link"** on an officer
3. New link generated and emailed

**What Happens:**
- Resend clicked → Controller generates new password → Updates user → Sends email

**Test:** Resend link and verify new email is sent

---

## System Settings

### Viewing System Settings

**Workflow:**
1. Click **"System Settings"** in sidebar
2. View all system parameters grouped by category

**What Happens:**
- Page loads → Controller fetches settings → View displays with current values

**Test:** Navigate to settings and verify all settings are displayed

### Updating System Settings

**Workflow:**
1. Modify setting values
2. Click **"Save Settings"**

**What Happens:**
- Form submitted → Controller validates → Settings updated in DB → Redirect with success

**Test:** 
1. Change a setting value
2. Save
3. Refresh page and verify change persisted

---

## Reports Generation

### Generating Custom Report

**Workflow:**
1. Click **"Reports"** in sidebar
2. Select **Report Type** (Officers, Emoluments, Leave, etc.)
3. Select **Date Range** (optional)
4. Select **Format** (CSV, Excel)
5. Click **"Generate Report"**

**What Happens:**
- Form submitted → Controller queries data → Filters by date range → Generates file → Downloads

**Test:** Generate report and verify file downloads with correct data

---

## Testing Checklist

### Manual Testing Steps

1. **Dashboard**
   - [ ] Login as HRD
   - [ ] Verify dashboard loads with statistics
   - [ ] Verify no "Loading..." messages

2. **Officers**
   - [ ] View officers list
   - [ ] Test search filter
   - [ ] Test rank filter
   - [ ] Test command filter
   - [ ] View officer details

3. **Staff Orders**
   - [ ] Create staff order
   - [ ] Verify order number auto-generates
   - [ ] Verify from command auto-fills
   - [ ] Publish order
   - [ ] Verify officer's present_station updates

4. **Movement Orders**
   - [ ] Create movement order from manning request
   - [ ] Create movement order with criteria
   - [ ] Verify orders are saved

5. **Emolument Timeline**
   - [ ] Create timeline
   - [ ] Extend timeline
   - [ ] Verify timeline appears in list

6. **Promotion Criteria**
   - [ ] Create criteria
   - [ ] Edit criteria
   - [ ] Verify duplicate prevention

7. **Promotion Eligibility**
   - [ ] Generate eligibility list
   - [ ] Verify eligible officers included
   - [ ] Verify interdicted officers excluded
   - [ ] Delete empty list

8. **Retirement List**
   - [ ] Generate retirement list
   - [ ] Verify officers included
   - [ ] Verify pre-retirement dates calculated

9. **Leave Types**
   - [ ] Create leave type
   - [ ] Edit leave type
   - [ ] Delete leave type (without applications)

10. **Manning Requests**
    - [ ] View approved requests
    - [ ] Find matches
    - [ ] Generate movement order

11. **Course Nominations**
    - [ ] Nominate officer
    - [ ] Mark as completed
    - [ ] Verify completion status

12. **Onboarding**
    - [ ] Initiate onboarding
    - [ ] Verify email sent
    - [ ] Resend link
    - [ ] Verify user account created

13. **System Settings**
    - [ ] View settings
    - [ ] Update settings
    - [ ] Verify changes persist

14. **Reports**
    - [ ] Generate report
    - [ ] Verify file downloads

---

## Automated Testing

### Running Tests

```bash
# Run all HRD feature tests
php artisan test --filter HRDFeatureTest

# Run specific test
php artisan test --filter hrd_can_create_staff_order

# Run with coverage
php artisan test --coverage --filter HRDFeatureTest
```

### Test Data Seeding

```bash
# Seed HRD test data (does NOT delete existing data)
php artisan db:seed --class=HRDTestDataSeeder
```

### What Tests Cover

- ✅ All view pages load correctly
- ✅ All forms submit successfully
- ✅ All database operations work
- ✅ Workflow automations trigger correctly
- ✅ Data validation works
- ✅ Exclusions work (interdicted, suspended, etc.)
- ✅ Status updates work (PUBLISHED triggers workflow)

---

## Common Issues & Solutions

### Issue: Email Not Sending
**Solution:** Check `.env` email configuration and mailtrap/logs

### Issue: Cron Job Not Running
**Solution:** Add to server crontab: `* * * * * cd /path && php artisan schedule:run`

### Issue: Officer Not Updating on Publish
**Solution:** Check PostingWorkflowService logs and verify status is PUBLISHED

### Issue: Promotion List Includes Ineligible Officers
**Solution:** Verify exclusion checks in PromotionController

---

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review test results
3. Check database for data integrity
4. Verify all workflows are followed correctly

---

**Last Updated:** December 2024  
**Version:** 1.0

