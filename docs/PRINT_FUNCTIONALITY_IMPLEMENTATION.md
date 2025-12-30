# Print Functionality Implementation

This document describes the print functionality implementation based on the system specification and the provided document samples.

## Overview

All print functions have been implemented with views that match the exact structure and format of the official NCS documents. The system supports both document printing (leave, pass, staff orders) and report generation.

## Implemented Print Functions

### 1. Document Prints (Based on Provided Images)

#### 1.1 Internal Staff Order
- **Route**: `/print/internal-staff-order/{id}`
- **View**: `resources/views/prints/internal-staff-order.blade.php`
- **Format**: Matches the official Internal Staff Order format
- **Data**: Officer details, posting information, command details, staff officer signature
- **Usage**: Staff Officer prints internal staff orders for officer postings within a command

#### 1.2 Staff Order (General Duty)
- **Route**: `/print/staff-order/{id}`
- **View**: `resources/views/prints/staff-order.blade.php`
- **Format**: Matches the official HRD Staff Order format
- **Data**: Officer details, from/to commands, effective date, DCG signature
- **Usage**: HRD prints staff orders for officer postings

#### 1.3 Deployment List
- **Route**: `/print/deployment?command_id={id}&date={date}`
- **View**: `resources/views/prints/deployment.blade.php`
- **Format**: Tabular format showing officer deployments
- **Data**: Service numbers, ranks, names, new postings
- **Usage**: Command-level deployment listings

#### 1.4 Leave Document
- **Route**: `/print/leave-document/{id}`
- **View**: `resources/views/prints/leave-document.blade.php`
- **Format**: Official leave approval document format
- **Data**: Officer details, leave type, dates, area controller signature
- **Usage**: Staff Officer prints approved leave documents for distribution

#### 1.5 Pass Document
- **Route**: `/print/pass-document/{id}`
- **View**: `resources/views/prints/pass-document.blade.php`
- **Format**: Official NCS Pass form format with emblem
- **Data**: Officer details, pass duration, destination, staff officer signature
- **Usage**: Staff Officer prints approved pass documents

#### 1.6 Retirement List
- **Route**: `/print/retirement-list?year={year}`
- **View**: `resources/views/prints/retirement-list.blade.php`
- **Format**: Tabular format showing officers scheduled for retirement
- **Data**: Service numbers, ranks, names, retirement dates, retirement types
- **Usage**: HRD generates retirement lists for planning

### 2. Report Prints

#### 2.1 Accommodation Report
- **Route**: `/print/accommodation-report?command_id={id}`
- **View**: `resources/views/prints/report-template.blade.php`
- **Data**: Officers with quartered status, command assignments
- **Usage**: Building Unit generates accommodation reports

#### 2.2 Service Number Assignment Report
- **Route**: `/print/service-number-report?rank={rank}`
- **View**: `resources/views/prints/report-template.blade.php`
- **Data**: Service numbers, appointment numbers, ranks, DOFA
- **Usage**: Establishment generates service number assignment reports by rank

#### 2.3 Validated Officers Report
- **Route**: `/print/validated-officers-report`
- **View**: `resources/views/prints/report-template.blade.php`
- **Data**: Officers with validated emoluments, bank details, PFA, RSA
- **Usage**: Accounts generates list of validated officers for payment processing

#### 2.4 Interdicted Officers Report
- **Route**: `/print/interdicted-officers-report`
- **View**: `resources/views/prints/report-template.blade.php`
- **Data**: Officers on interdiction status
- **Usage**: Accounts generates list of interdicted officers

## Controller

**File**: `app/Http/Controllers/PrintController.php`

All print functions are handled by the `PrintController` which:
- Fetches necessary data from models
- Prepares data for views
- Handles relationships (officers, commands, approvals, etc.)
- Provides dynamic data to match document formats

## Routes

All print routes are defined in `routes/web.php` under the `/print` prefix:
- Protected by `auth` middleware
- Named routes for easy reference
- Supports query parameters for filtering

## Views

### Document Views
Located in `resources/views/prints/`:
- `internal-staff-order.blade.php` - Internal Staff Order format
- `staff-order.blade.php` - HRD Staff Order format
- `deployment.blade.php` - Deployment list format
- `leave-document.blade.php` - Official leave document
- `pass-document.blade.php` - Official pass document
- `retirement-list.blade.php` - Retirement list format

### Report Template
- `report-template.blade.php` - Generic report template for tabular data

## Features

1. **Exact Format Matching**: All document prints match the exact structure of the provided official documents
2. **Dynamic Data**: All data is pulled from the database and dynamically populated
3. **Print-Friendly**: All views include print-specific CSS with `@media print` styles
4. **Responsive**: Views work on screen and when printed
5. **Signature Support**: Documents include signature sections with officer names and ranks
6. **Command Context**: Documents automatically include command information where relevant
7. **Watermark**: All documents include a lemon-colored watermark with "NCS Management Information System (MIS)" text
8. **NCS Logo**: Official documents (Pass Document) include the NCS emblem/logo at the top

## Integration Points

### Existing Controllers Updated
- `LeaveApplicationController::print()` - Now redirects to official format
- `PassApplicationController::print()` - Now redirects to official format

### Data Models Used
- `Officer` - Officer information
- `Command` - Command/station information
- `LeaveApplication` - Leave application data
- `PassApplication` - Pass application data
- `StaffOrder` - Staff order data
- `InternalStaffOrder` - Internal staff order data
- `OfficerPosting` - Posting information
- `LeaveApproval` - Leave approval data
- `PassApproval` - Pass approval data
- `Emolument` - Emolument data for reports
- `Role` - Role information for finding staff officers

## Usage Examples

### Print Leave Document
```php
// From Staff Officer dashboard
Route::get('/staff-officer/leave-applications/{id}/print', ...)
// Redirects to: /print/leave-document/{id}
```

### Print Pass Document
```php
// From Staff Officer dashboard
Route::get('/staff-officer/pass-applications/{id}/print', ...)
// Redirects to: /print/pass-document/{id}
```

### Generate Reports
```php
// Accommodation Report
GET /print/accommodation-report?command_id=1

// Service Number Report
GET /print/service-number-report?rank=SC

// Validated Officers Report
GET /print/validated-officers-report

// Retirement List
GET /print/retirement-list?year=2026
```

## Print Styling

All print views include:
- Print-specific CSS with `@media print` queries
- A4 page size settings
- Proper margins and spacing
- Hidden print buttons when printing
- Official document formatting
- **Watermark**: Lemon-colored (#FFFACD) diagonal watermark with text "NCS Management Information System (MIS)" on all documents
- **NCS Logo**: SVG-based NCS emblem/logo on documents that require it (Pass Document)

### Watermark Details
- Color: Lemon (#FFFACD)
- Text: "NCS Management Information System (MIS)"
- Position: Centered, rotated -45 degrees
- Opacity: 15% on screen, 12% when printing
- Applied to: All print documents

### Logo Details
- Format: SVG (scalable vector graphics)
- Elements: Red eagle, green/white striped bar, five-pointed star with all-seeing eye, staff and rifle, green base with yellow flowers, banner with "NIGERIA CUSTOMS SERVICE" and "JUSTICE AND HONESTY"
- Applied to: Pass Document (and can be added to other official documents as needed)

## Future Enhancements

The following print functions from the specification can be added:
- Payment reports and reconciliations
- Assessment reports
- Preretirement leave management reports
- Welfare reports
- Training reports
- Email account reports
- Training performance reports
- Command-level reports and statistics
- Comprehensive system reports (HRD)

These can be implemented using the same `report-template.blade.php` or by creating specific views if a custom format is required.

