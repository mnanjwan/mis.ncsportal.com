# APER Form Viewing Guide

## Where Form Data is Viewed After Saving

After the Reporting Officer saves or completes an APER form assessment, the data can be viewed in the following locations:

### 1. **Reporting Officer View** (Same Form)
- **Location**: The same form page where data was entered
- **URL Pattern**: `/officer/aper-forms/access/{officerId}`
- **Access**: After clicking "Save Assessment", the form remains accessible for further editing
- **Status**: Form status is `REPORTING_OFFICER`

### 2. **Officer View** (After Completion)
- **Location**: Officer's APER Forms list and detail view
- **URL Pattern**: `/officer/aper-forms/{id}`
- **Route Name**: `officer.aper-forms.show`
- **Access**: After Reporting Officer completes and forwards, form status becomes `COUNTERSIGNING_OFFICER`, then `OFFICER_REVIEW`
- **Features**: Officer can view, add comments, accept, or reject the assessment

### 3. **Countersigning Officer View**
- **Location**: Countersigning officer's search page
- **URL Pattern**: `/officer/aper-forms/countersigning/{id}`
- **Route Name**: `officer.aper-forms.countersigning`
- **Access**: After Reporting Officer completes and forwards the form
- **Status**: Form status is `COUNTERSIGNING_OFFICER`

### 4. **HRD View**
- **Location**: HRD dashboard - All APER Forms
- **URL Pattern**: `/hrd/aper-forms/{id}`
- **Route Name**: `officer.aper-forms.show` (same view, different access)
- **Access**: HRD staff can view all forms in the system
- **Status**: All statuses except DRAFT

### 5. **Staff Officer View**
- **Location**: Staff Officer review page (for rejected forms)
- **URL Pattern**: `/staff-officer/aper-forms/review/{id}`
- **Route Name**: `staff-officer.aper-forms.review.show`
- **Access**: When forms are rejected by officers
- **Status**: Form status is `STAFF_OFFICER_REVIEW`

### 6. **PDF Export**
- **Location**: Available from all view pages
- **URL Pattern**: `/officer/aper-forms/{id}/export`
- **Route Name**: `officer.aper-forms.export`
- **Access**: All authorized users can export PDF versions
- **Format**: PDF document with all form data

## Form Workflow States

1. **REPORTING_OFFICER** - Reporting Officer is filling/editing the form
2. **COUNTERSIGNING_OFFICER** - Form forwarded to Countersigning Officer
3. **OFFICER_REVIEW** - Form returned to Officer for review
4. **ACCEPTED** - Officer accepted the assessment
5. **REJECTED** - Officer rejected the assessment
6. **STAFF_OFFICER_REVIEW** - Rejected form sent to Staff Officer
7. **FINALIZED** - Form finalized by Staff Officer

## Database Storage

All form data is saved to the `aper_forms` table with the following key information:

- **Form Identification**: `id`, `officer_id`, `year`, `timeline_id`
- **Workflow**: `status`, `reporting_officer_id`, `countersigning_officer_id`
- **Assessment Data**: All grade fields (A-F) and comments for each assessment aspect
- **Final Sections**: Training needs, general remarks, suggestions, promotability
- **Timestamps**: `created_at`, `updated_at`, `reporting_officer_completed_at`, etc.

## Verification

To verify that data is being saved correctly:

1. Fill out the form and click "Save Assessment"
2. Check the success message appears
3. Refresh the page - data should persist
4. Check database directly: `SELECT * FROM aper_forms WHERE id = {form_id}`
5. View the form from the officer's perspective after completion

