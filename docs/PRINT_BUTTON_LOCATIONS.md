# Print Button Locations and Access Directions

## ✅ CONFIRMATION: All Print Pages Have Print Buttons

All print views have been confirmed to have:
- ✅ **Print Button**: Visible blue button with "Print Document" or "Print Report" text
- ✅ **Visibility**: Buttons are visible on screen but hidden when printing (using `.no-print` class)
- ✅ **Location**: Centered at the top of each print page
- ✅ **Functionality**: Uses `window.print()` to trigger browser print dialog

---

## 📍 DIRECTIONS TO EACH PRINT PAGE

### 1. **Leave Document Print**
**Route:** `/print/leave-document/{id}`  
**Route Name:** `print.leave-document`  
**View:** `resources/views/prints/leave-document.blade.php`

**How to Access:**
- **From Staff Officer Dashboard:**
  - Navigate to: Staff Officer → Leave Applications → View Details → Click "Print" button
  - Or from list: Staff Officer → Leave Applications → Click "Print" button on any application
- **From Area Controller Dashboard:**
  - Navigate to: Area Controller → Leave Applications → View Details → Click "Print" button
- **Legacy Route (redirects):** `/staff-officer/leave-applications/{id}/print` or `/area-controller/leave-applications/{id}/print`
- **Direct URL:** `/print/leave-document/{id}` (replace `{id}` with leave application ID)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Document"
- Hidden when printing (won't appear on printed page)

---

### 2. **Pass Document Print**
**Route:** `/print/pass-document/{id}`  
**Route Name:** `print.pass-document`  
**View:** `resources/views/prints/pass-document.blade.php`

**How to Access:**
- **From Staff Officer Dashboard:**
  - Navigate to: Staff Officer → Pass Applications → View Details → Click "Print" button
  - Or from list: Staff Officer → Pass Applications → Click "Print" button on any application
- **From Area Controller Dashboard:**
  - Navigate to: Area Controller → Pass Applications → View Details → Click "Print" button
- **Legacy Route (redirects):** `/staff-officer/pass-applications/{id}/print` or `/area-controller/pass-applications/{id}/print`
- **Direct URL:** `/print/pass-document/{id}` (replace `{id}` with pass application ID)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Document"
- Hidden when printing (won't appear on printed page)

---

### 3. **Internal Staff Order Print**
**Route:** `/print/internal-staff-order/{id}`  
**Route Name:** `print.internal-staff-order`  
**View:** `resources/views/prints/internal-staff-order.blade.php`

**How to Access:**
- **From Staff Orders List:**
  - Navigate to: Staff Orders → Internal Staff Orders → View Details → Click "Print" button
- **Direct URL:** `/print/internal-staff-order/{id}` (replace `{id}` with internal staff order ID)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Document"
- Hidden when printing (won't appear on printed page)

---

### 4. **Staff Order (HRD) Print**
**Route:** `/print/staff-order/{id}`  
**Route Name:** `print.staff-order`  
**View:** `resources/views/prints/staff-order.blade.php`

**How to Access:**
- **From HRD Staff Orders:**
  - Navigate to: HRD → Staff Orders → View Details → Click "Print" button
- **Direct URL:** `/print/staff-order/{id}` (replace `{id}` with staff order ID)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Document"
- Hidden when printing (won't appear on printed page)

---

### 5. **Deployment List Print**
**Route:** `/print/deployment?command_id={id}&date={date}`  
**Route Name:** `print.deployment`  
**View:** `resources/views/prints/deployment.blade.php`

**How to Access:**
- **From Deployment Management:**
  - Navigate to: Staff Officer → Deployment → View Deployment List → Click "Print" button
- **Direct URL:** `/print/deployment?command_id={id}&date={date}` (optional query parameters)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Document"
- Hidden when printing (won't appear on printed page)

---

### 6. **Retirement List Print**
**Route:** `/print/retirement-list?year={year}`  
**Route Name:** `print.retirement-list`  
**View:** `resources/views/prints/retirement-list.blade.php`

**How to Access:**
- **From Retirement Management:**
  - Navigate to: HRD/Establishment → Retirement Lists → View List → Click "Print" button
- **Direct URL:** `/print/retirement-list?year={year}` (optional year parameter)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Document"
- Hidden when printing (won't appear on printed page)

---

### 7. **Accommodation Report Print**
**Route:** `/print/accommodation-report?command_id={id}`  
**Route Name:** `print.accommodation-report`  
**View:** `resources/views/prints/report-template.blade.php`

**How to Access:**
- **From Building Unit:**
  - Navigate to: Building Unit → Accommodation Reports → Generate Report → Click "Print" button
- **Direct URL:** `/print/accommodation-report?command_id={id}` (optional command_id parameter)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Report"
- Hidden when printing (won't appear on printed page)

---

### 8. **Service Number Report Print**
**Route:** `/print/service-number-report?rank={rank}`  
**Route Name:** `print.service-number-report`  
**View:** `resources/views/prints/report-template.blade.php`

**How to Access:**
- **From Establishment Unit:**
  - Navigate to: Establishment → Service Number Reports → Generate Report → Click "Print" button
- **Direct URL:** `/print/service-number-report?rank={rank}` (optional rank parameter)

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Report"
- Hidden when printing (won't appear on printed page)

---

### 9. **Validated Officers Report Print**
**Route:** `/print/validated-officers-report`  
**Route Name:** `print.validated-officers-report`  
**View:** `resources/views/prints/report-template.blade.php`

**How to Access:**
- **From Accounts Unit:**
  - Navigate to: Accounts → Validated Officers → Generate Report → Click "Print" button
- **Direct URL:** `/print/validated-officers-report`

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Report"
- Hidden when printing (won't appear on printed page)

---

### 10. **Interdicted Officers Report Print**
**Route:** `/print/interdicted-officers-report`  
**Route Name:** `print.interdicted-officers-report`  
**View:** `resources/views/prints/report-template.blade.php`

**How to Access:**
- **From Accounts Unit:**
  - Navigate to: Accounts → Interdicted Officers → Generate Report → Click "Print" button
- **Direct URL:** `/print/interdicted-officers-report`

**Print Button Location:**
- Top center of the page
- Blue button labeled "Print Report"
- Hidden when printing (won't appear on printed page)

---

## 🎨 Print Button Styling

All print buttons use consistent styling:
```css
padding: 10px 20px;
font-size: 16px;
cursor: pointer;
background: #007bff; (blue)
color: white;
border: none;
border-radius: 5px;
```

## 🔒 Print Button Visibility

- **On Screen:** ✅ Visible (centered at top)
- **When Printing:** ❌ Hidden (using `.no-print` class with `@media print { display: none; }`)

## 📝 Notes

- All print routes require authentication (`auth` middleware)
- All print pages include the green watermark
- All print pages that require logos use `public/logo.jpg`
- Authorizing officer information is dynamically populated from authenticated user or approval records

