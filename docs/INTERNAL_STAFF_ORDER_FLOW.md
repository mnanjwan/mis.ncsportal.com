# Internal Staff Order - Complete Flow Documentation

## 📋 Overview

**Internal Staff Orders** are command-level staff orders prepared by Staff Officers for internal operations within their specific command. Unlike HRD-level Staff Orders (which are system-wide and involve officer postings between commands), Internal Staff Orders are used for:

- **Command-level operations**: Internal reassignments, temporary postings, or operational directives within a single command
- **Local documentation**: Official documentation of internal staff movements or assignments
- **Print-ready documents**: Generate official printed documents for distribution

---

## 🔑 Key Concepts

### Internal Staff Order vs HRD Staff Order

| Feature | Internal Staff Order | HRD Staff Order |
|---------|---------------------|-----------------|
| **Scope** | Command-level only | System-wide |
| **Created By** | Staff Officer | HRD |
| **Purpose** | Internal command operations | Officer postings between commands |
| **Order Number Format** | `ISO-YYYY-MMDD-XXX` | Different format (HRD managed) |
| **Officer Posting** | Can reference officer but not system posting | Creates actual system posting |
| **Visibility** | Only visible to Staff Officer's command | Visible system-wide |

---

## 📊 Data Structure

### Database Table: `internal_staff_orders`

```php
- id (primary key)
- command_id (foreign key → commands) - The command this order belongs to
- order_number (string, unique) - Format: ISO-YYYY-MMDD-XXX
- order_date (date) - Date of the order
- prepared_by (foreign key → users) - Staff Officer who created it
- description (text, nullable) - Description/details of the order
- created_at, updated_at (timestamps)
```

### Model Relationships

```php
InternalStaffOrder
├── command() → Command (belongsTo)
└── preparedBy() → User (belongsTo)
```

---

## 🔄 Complete Workflow

### 1. **Access Control & Authorization**

**Who Can Access:**
- ✅ **Staff Officer** role only
- ❌ Other roles cannot access

**Command Restriction:**
- Staff Officer must be assigned to a command
- Can only create/view/edit Internal Staff Orders for their assigned command
- System checks: `getStaffOfficerCommandId()` method

**Routes (All require `auth` + `role:Staff Officer` middleware):**
```
GET    /staff-officer/internal-staff-orders              → index
GET    /staff-officer/internal-staff-orders/create       → create
POST   /staff-officer/internal-staff-orders              → store
GET    /staff-officer/internal-staff-orders/{id}         → show
GET    /staff-officer/internal-staff-orders/{id}/edit    → edit
PUT    /staff-officer/internal-staff-orders/{id}         → update
DELETE /staff-officer/internal-staff-orders/{id}         → destroy
GET    /print/internal-staff-order/{id}                  → print (PrintController)
```

---

### 2. **Creating an Internal Staff Order**

#### Step-by-Step Process:

**A. Navigate to Create Page**
- Staff Officer → Internal Staff Orders → Click "Create Internal Staff Order"

**B. System Auto-Generates Order Number**
- Format: `ISO-YYYY-MMDD-XXX`
  - `ISO` = Internal Staff Order prefix
  - `YYYY` = Current year (e.g., 2025)
  - `MMDD` = Current month and day (e.g., 1230 for Dec 30)
  - `XXX` = Sequential number (001, 002, 003...)
- Example: `ISO-2025-1230-001`
- System checks for duplicates and increments if needed

**C. Fill in Form Fields:**
- **Order Number**: Pre-filled (can be edited, but must be unique)
- **Order Date**: Required date field
- **Description**: Optional text field for order details

**D. Submit Form**
- System validates:
  - Order number is unique
  - Order date is valid
  - Command ID is set (from Staff Officer's assignment)
  - Prepared by is set (current authenticated user)

**E. Success**
- Redirects to Internal Staff Orders list
- Shows success message
- New order appears in the list

---

### 3. **Viewing Internal Staff Orders**

#### List View (`index`)

**Features:**
- Shows all Internal Staff Orders for Staff Officer's command
- **Search**: By order number or description
- **Sorting**: By order number, order date, or created date (ascending/descending)
- **Pagination**: 20 orders per page
- **Actions per order**: View, Edit, Print

**Displayed Information:**
- Order Number
- Order Date
- Description (truncated to 50 chars)
- Created At timestamp
- Action buttons (View, Edit, Print)

#### Detail View (`show`)

**Shows:**
- Full order details
- Command information
- Prepared by information (with officer details if linked)
- All order fields

---

### 4. **Editing an Internal Staff Order**

**Process:**
1. Click "Edit" button on any order
2. Form pre-filled with existing data
3. Can modify:
   - Order Number (must remain unique)
   - Order Date
   - Description
4. Submit changes
5. Redirects to detail view with success message

**Restrictions:**
- Can only edit orders from Staff Officer's command
- Order number must remain unique (except for current order)

---

### 5. **Deleting an Internal Staff Order**

**Process:**
1. Click "Delete" button (if available in UI)
2. System confirms deletion
3. Order is permanently removed
4. Redirects to list with success message

**Restrictions:**
- Can only delete orders from Staff Officer's command

---

### 6. **Printing Internal Staff Orders**

**Access:**
- Click "Print" button from list or detail view
- Opens in new tab/window
- Route: `/print/internal-staff-order/{id}`

**Print View Features:**
- **Official Document Format**: RESTRICTED header
- **NCS Header**: Nigeria Customs Service branding
- **Command Name**: Shows command name
- **Order Details**:
  - File Reg. No (Order Number)
  - Staff Order Date
  - Service No (from officer if linked)
  - Rank (from officer if linked)
  - Name (from officer if linked)
  - New Posting (if specified)
- **Signature Section**: 
  - Staff Officer information (dynamically populated)
  - Service No, Rank/Designation
  - Date & Stamp
  - "For: Comptroller, [COMMAND NAME]"
- **Watermark**: Green "NCS Management Information System (MIS)" watermark
- **Print Button**: Visible on screen, hidden when printing

**Print Controller Logic:**
```php
public function internalStaffOrder(Request $request, $id)
{
    // Get the internal staff order
    $internalStaffOrder = InternalStaffOrder::with(['command', 'preparedBy.officer'])->findOrFail($id);
    
    // Get command
    $command = $internalStaffOrder->command;
    
    // Get staff officer (authenticated user or from command)
    $currentUser = Auth::user();
    $staffOfficer = null;
    
    // Try to get staff officer from authenticated user
    if ($currentUser && $command) {
        $staffOfficerRole = Role::where('name', 'Staff Officer')->first();
        if ($staffOfficerRole) {
            $isStaffOfficer = $currentUser->roles()
                ->where('roles.id', $staffOfficerRole->id)
                ->where('user_roles.command_id', $command->id)
                ->where('user_roles.is_active', true)
                ->exists();
            
            if ($isStaffOfficer) {
                $staffOfficer = $currentUser->officer;
            }
        }
    }
    
    // Fallback: get from command or default
    if (!$staffOfficer) {
        $staffOfficer = $this->getStaffOfficerForCommand($command->id ?? null);
    }
    
    // Get officer if linked (from description or other source)
    $officer = null; // Currently not directly linked in model
    
    return view('prints.internal-staff-order', compact(
        'internalStaffOrder', 
        'command', 
        'staffOfficer',
        'officer'
    ));
}
```

---

## 🎯 Use Cases

### Use Case 1: Internal Reassignment
**Scenario:** Staff Officer needs to reassign an officer within the command temporarily.

**Steps:**
1. Create Internal Staff Order
2. Fill in order details (description includes officer and new assignment)
3. Print the order
4. Distribute to relevant parties

### Use Case 2: Operational Directive
**Scenario:** Staff Officer needs to issue an official directive for command operations.

**Steps:**
1. Create Internal Staff Order
2. Describe the directive in description field
3. Set appropriate order date
4. Print for official records

### Use Case 3: Temporary Posting
**Scenario:** Staff Officer needs to document a temporary internal posting.

**Steps:**
1. Create Internal Staff Order
2. Include officer details and temporary posting in description
3. Print document
4. File for records

---

## 🔐 Security & Access Control

### Middleware Protection
```php
$this->middleware('auth');           // Must be authenticated
$this->middleware('role:Staff Officer'); // Must have Staff Officer role
```

### Command Isolation
- Staff Officers can only see/manage orders for their assigned command
- System checks `command_id` matches Staff Officer's assigned command
- Prevents cross-command access

### Validation Rules
```php
'order_number' => 'required|string|max:100|unique:internal_staff_orders,order_number'
'order_date' => 'required|date'
'description' => 'nullable|string'
```

---

## 📝 Order Number Generation Logic

```php
// Format: ISO-YYYY-MMDD-XXX
$year = date('Y');           // 2025
$monthDay = date('md');      // 1230 (Dec 30)
$sequence = 1;                // Starts at 001

// Get last order for this command
$lastOrder = InternalStaffOrder::where('command_id', $commandId)
    ->orderBy('created_at', 'desc')
    ->first();

// Extract sequence from last order if exists
if ($lastOrder && preg_match('/-(\d{3})$/', $lastOrder->order_number, $matches)) {
    $sequence = (int)$matches[1] + 1;
}

// Generate order number
$orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

// Check for duplicates and increment if needed
while (InternalStaffOrder::where('order_number', $orderNumber)->exists()) {
    $sequence++;
    $orderNumber = 'ISO-' . $year . '-' . $monthDay . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
}
```

**Example Sequence:**
- First order of day: `ISO-2025-1230-001`
- Second order: `ISO-2025-1230-002`
- Next day: `ISO-2025-1231-001` (resets sequence)

---

## 🖨️ Print Document Structure

The printed document includes:

1. **Header Section**
   - "RESTRICTED" watermark
   - "NIGERIA CUSTOMS SERVICE" title
   - Command name

2. **File Reference**
   - File Reg. No: Order Number

3. **Order Title**
   - "INTERNAL STAFF ORDER" (centered, underlined)

4. **Order Details Table**
   - Staff Order Date
   - Service No (if officer linked)
   - Rank (if officer linked)
   - Name (if officer linked)
   - New Posting (if specified)

5. **Remark Section**
   - Standard text about posting taking immediate effect

6. **Signature Section**
   - Staff Officer name, service number, rank
   - Date & Stamp
   - "For: Comptroller, [COMMAND]"

7. **Footer**
   - "RESTRICTED" watermark

---

## 🔄 Integration Points

### Current Integration
- ✅ **Print System**: Integrated with PrintController
- ✅ **Authentication**: Uses Laravel Auth
- ✅ **Role-Based Access**: Uses role middleware
- ✅ **Command Management**: Linked to Command model

### Potential Future Enhancements
- Link to Officer model for automatic officer details
- Link to OfficerPosting for actual posting changes
- Notification system for order creation
- Workflow approval (if needed)
- Integration with HRD Staff Orders

---

## 📊 Summary

**Internal Staff Orders** are a command-level document management system that allows Staff Officers to:

1. ✅ Create official internal staff orders for their command
2. ✅ Auto-generate unique order numbers
3. ✅ Manage (view, edit, delete) their orders
4. ✅ Print official documents with proper formatting
5. ✅ Maintain command-level isolation and security

**Key Characteristics:**
- Command-scoped (not system-wide)
- Staff Officer role only
- Simple CRUD operations
- Print-ready official documents
- Auto-numbered with ISO prefix


