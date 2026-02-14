# CD Role - Complete Access Guide

## Overview

The **CD (Chief Driver)** role is responsible for managing vehicle requests and fleet operations at the command level. This document provides a complete guide to all pages, functions, and features accessible to CD users.

## Dashboard Access

### Fleet Dashboard
**Route**: `/fleet/dashboard/cd`  
**Middleware**: `role:CD`

**Dashboard Cards**:
- **My Draft Requests**: Count of requests in DRAFT status
- **Submitted Requests**: Count of requests in SUBMITTED status
- **KIV Requests**: Count of requests on Keep In View
- **Released Requests**: Count of successfully released requests
- **Command Pool Vehicles**: Count of vehicles available in command pool
- **Active Assignments**: Count of vehicles currently assigned to officers
- **Pending Returns**: Count of vehicles awaiting return from officers

**Quick Links**:
- Create Request
- View Requests
- Command Vehicles
- Returns Report

## Request Management

### Create Request
**Route**: `/fleet/requests/create`  
**Method**: GET  
**Middleware**: `role:CD|Area Controller|OC Workshop|Staff Officer T&L|CC T&L`

**Available Request Types**:
1. **New Vehicle Request** (`FLEET_NEW_VEHICLE`)
   - Specify vehicle type (SALOON, SUV, BUS)
   - Specify quantity
   - Add notes/description
   - Upload supporting documents

2. **Re-allocation** (`FLEET_RE_ALLOCATION`)
   - Select existing vehicle from dropdown
   - Add notes/description
   - Upload supporting documents

3. **OPE Request** (`FLEET_OPE`) - Out of Pocket Expenses
   - Select vehicle
   - Enter estimated amount
   - Add notes/description
   - Upload bills/receipts

4. **Repair Request** (`FLEET_REPAIR`)
   - Select vehicle
   - Add notes/description
   - Upload supporting documents

5. **Request for Use** (`FLEET_USE`)
   - Select vehicle
   - Add notes/description
   - Upload supporting documents

6. **Maintenance Requisition** (`FLEET_REQUISITION`)
   - Select vehicle
   - Enter estimated amount
   - Add notes/description
   - Upload bills/requisitions

**Form Features**:
- Dynamic field visibility based on request type
- File upload support (PDF, JPG, PNG, max 5MB)
- Notes/description textarea
- Save as DRAFT functionality

### Store Request
**Route**: `/fleet/requests`  
**Method**: POST  
**Middleware**: `role:CD|Area Controller|OC Workshop|Staff Officer T&L|CC T&L`

Creates a new request in DRAFT status.

### View Requests
**Route**: `/fleet/requests`  
**Method**: GET  
**Middleware**: `role:CD|O/C T&L|Transport Store/Receiver|Area Controller|OC Workshop|Staff Officer T&L|CC T&L|DCG FATS|ACG TS|CGC`

**Two Sections**:

1. **Inbox**: Requests awaiting your action
   - Shows requests where current step role matches your roles
   - Displays: ID, Origin Command, Request Type, Details, Status
   - "Open" button to view details

2. **My Requests**: Requests you created
   - Shows all requests created by you
   - Displays: ID, Request Type, Details, Status
   - "Submit" button for DRAFT requests
   - "View" button for all requests

### View Request Details
**Route**: `/fleet/requests/{fleetRequest}`  
**Method**: GET  
**Middleware**: `role:CD|O/C T&L|Transport Store/Receiver|Area Controller|OC Workshop|Staff Officer T&L|CC T&L|DCG FATS|ACG TS|CGC`

**Displayed Information**:
- Request ID and Type
- Origin Command
- Status (color-coded badge)
- Created By
- Requested Type/Quantity (for New Vehicle)
- Target Vehicle (for Re-allocation/Repair)
- Amount (for Requisitions/OPE)
- Notes
- Attachments (clickable download link)
- Workflow Progress Table (with current step highlighting)
- Action Panel (if you're at current step)

### Submit Request
**Route**: `/fleet/requests/{fleetRequest}/submit`  
**Method**: POST  
**Middleware**: `role:CD|Area Controller|OC Workshop|Staff Officer T&L|CC T&L`

Submits a DRAFT request into the workflow. Only the creator can submit.

## Vehicle Management

### View Vehicles
**Route**: `/fleet/vehicles`  
**Method**: GET  
**Middleware**: `role:CD|O/C T&L|Transport Store/Receiver|CC T&L|DCG FATS|ACG TS`

**View Scope**: Command-scoped - shows vehicles assigned to your command

**Vehicle Information Displayed**:
- Registration Number
- Make and Model
- Vehicle Type
- Lifecycle Status
- Current Command
- Current Officer (if assigned)

### View Vehicle Details
**Route**: `/fleet/vehicles/{vehicle}`  
**Method**: GET  
**Middleware**: `role:CD|O/C T&L|Transport Store/Receiver|CC T&L|DCG FATS|ACG TS`

Shows complete vehicle information including:
- Identifiers (Reg No, Chassis No, Engine No)
- Specifications (Make, Model, Year)
- Current Status
- Assignment History
- Service Records

### Update Vehicle Service Status
**Route**: `/fleet/vehicles/{vehicle}/service-status`  
**Method**: PUT  
**Middleware**: `role:CD`

Allows CD to update vehicle service/maintenance status.

## Vehicle Issuance & Returns

### Issue Vehicle to Officer
**Route**: `/fleet/vehicles/{vehicle}/issue`  
**Method**: GET (form) / POST (submit)  
**Middleware**: `role:CD`

**Process**:
1. Select vehicle from command pool
2. Select officer to assign vehicle to
3. Add notes/remarks
4. Submit to create assignment

**Accessible Vehicles**: Only vehicles in `AT_COMMAND_POOL` status for your command

### Process Vehicle Return
**Route**: `/fleet/vehicles/{vehicle}/return`  
**Method**: GET (form) / POST (submit)  
**Middleware**: `role:CD`

**Process**:
1. View vehicle assignment details
2. Confirm return conditions
3. Add return notes
4. Submit to process return

**Can Process**: Returns for vehicles assigned to officers in your command

## Reports

### Returns Report
**Route**: `/fleet/reports/returns`  
**Method**: GET  
**Middleware**: `role:CC T&L|CD`

View comprehensive report of vehicle returns including:
- Return dates
- Vehicle details
- Officer information
- Return conditions
- Processing status

## Workflow Participation

### As Request Creator

**New Vehicle Request Flow**:
1. Create request → DRAFT
2. Submit request → Goes to CC T&L for proposal
3. CC T&L proposes vehicles → Goes to CGC
4. CGC approves → Goes to DCG FATS
5. DCG FATS forwards → Goes to ACG TS
6. ACG TS forwards → Goes back to CC T&L
7. CC T&L releases → RELEASED

**Re-allocation Request Flow**:
1. Create request → DRAFT
2. Submit request → Goes directly to CC T&L
3. CC T&L approves & releases → RELEASED

**Requisition Flow**:
1. Create request → DRAFT
2. Submit request → Goes to ACG TS
3. Routing based on amount:
   - ≤ ₦300k: Approved by ACG TS
   - > ₦300k: Goes to DCG FATS
   - > ₦500k: Goes to CGC

**Repair/OPE/Use Request Flow**:
1. Create request → DRAFT
2. Submit request → Goes to Staff Officer T&L
3. Staff Officer T&L approves → RELEASED

### Notifications

CD users receive notifications when:
- Requests they created change status
- Requests from their command are updated
- Vehicles are released to their command

## UI/UX Features

### Request Show Page Elements

**Always Visible**:
- ✅ Request ID and Type
- ✅ Origin Command
- ✅ Status badge
- ✅ Created By
- ✅ Workflow Progress Table (with current step highlighted)

**Conditionally Visible**:
- ✅ **Amount**: Shows when request has amount (Requisitions, OPE)
- ✅ **Vehicle**: Shows when request has associated vehicle
- ✅ **Requested Type/Quantity**: Shows for New Vehicle requests
- ✅ **Notes**: Shows when notes are provided
- ✅ **Attachments**: Shows when documents are uploaded

**Workflow Progress Table**:
- Shows all steps in order
- **Current step highlighted** with blue background and bold text
- Displays: Order, Role, Action, Decision, Actor, Timestamp
- Pending steps show "Pending" in gray italic

**Action Panels**:
- Appear only when CD is at the current workflow step
- Show appropriate action buttons based on step action type
- Include comment fields for notes

### Request Index Page

**Inbox Section**:
- Table layout with sortable columns
- Color-coded status badges
- Quick "Open" action button
- Empty state message when no items

**My Requests Section**:
- Table layout
- "Submit" button for DRAFT requests
- "View" button for all requests
- Status indicators

## Best Practices

1. **Request Creation**:
   - Fill all required fields accurately
   - Upload supporting documents when available
   - Add clear notes/descriptions

2. **Request Submission**:
   - Review DRAFT requests before submitting
   - Ensure all information is correct
   - Submit only when ready for workflow processing

3. **Vehicle Management**:
   - Regularly check command pool for available vehicles
   - Process returns promptly
   - Update service status as needed

4. **Monitoring**:
   - Check dashboard regularly for updates
   - Monitor inbox for requests requiring action
   - Track "My Requests" for status updates

## Access Summary

| Function | Route | Method | Status |
|----------|-------|--------|--------|
| Dashboard | `/fleet/dashboard/cd` | GET | ✅ |
| Create Request | `/fleet/requests/create` | GET | ✅ |
| Store Request | `/fleet/requests` | POST | ✅ |
| View Requests | `/fleet/requests` | GET | ✅ |
| View Request Details | `/fleet/requests/{id}` | GET | ✅ |
| Submit Request | `/fleet/requests/{id}/submit` | POST | ✅ |
| View Vehicles | `/fleet/vehicles` | GET | ✅ |
| View Vehicle Details | `/fleet/vehicles/{id}` | GET | ✅ |
| Update Service Status | `/fleet/vehicles/{id}/service-status` | PUT | ✅ |
| Issue Vehicle | `/fleet/vehicles/{id}/issue` | GET/POST | ✅ |
| Process Return | `/fleet/vehicles/{id}/return` | GET/POST | ✅ |
| Returns Report | `/fleet/reports/returns` | GET | ✅ |

All pages are accessible, functional, and provide clean UI/UX with proper element visibility and workflow tracking.
