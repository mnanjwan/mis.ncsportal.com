# Establishment Service Number Workflow - Step-by-Step Guide

## Overview
This guide explains exactly how to perform the service number workflow as specified. The system automatically handles CDT/RCT prefix assignment and rank-based service number allocation.

---

## Step 1: Create New Recruits

### Location: **New Recruits** → **Add New Recruit**

1. **Navigate to New Recruits**
   - Click on "New Recruits" in the sidebar
   - Click the "Add New Recruit" button

2. **Fill in Recruit Information**
   - **Initials**: Enter officer's initials
   - **Surname**: Enter officer's surname
   - **Sex**: Select Male or Female
   - **Date of Birth**: Select date of birth
   - **Date of First Appointment**: Select appointment date
   - **Substantive Rank**: **Select from dropdown** (same ranks as manning requests):
     - For **CDT prefix**: Select ASC II, DSC
     - For **RCT prefix**: Select IC, AIC
   - **Salary Grade Level**: **Select from dropdown**:
     - For ASC II: Select **GL 08** or higher → Will get **CDT** prefix
     - For IC: Select **GL 07** or lower → Will get **RCT** prefix
     - For AIC: Any GL level → Will get **RCT** prefix
     - For DSC: Any GL level → Will get **CDT** prefix
   - **Email**: Enter personal email
   - **Phone Number**: (Optional)

3. **Submit Form**
   - Click "Create Recruit"
   - System creates the recruit record
   - **Note**: Appointment number is NOT assigned yet at this stage

---

## Step 2: Assign Appointment Numbers (CDT/RCT)

### Location: **New Recruits** page

### Option A: Single Assignment
1. Find the recruit in the list
2. Click the action menu (three dots) next to the recruit
3. Click "Assign Appointment"
4. System automatically determines prefix:
   - **ASC II GL 08+** → **CDT** (e.g., CDT00001, CDT00002)
   - **IC GL 07-** → **RCT** (e.g., RCT00001, RCT00002)
   - **AIC** → **RCT** (e.g., RCT00005, RCT00006)
   - **DSC** → **CDT** (e.g., CDT00007, CDT00008)
5. Click "Assign"
6. Appointment number is assigned automatically

### Option B: Bulk Assignment
1. Select multiple recruits using checkboxes
2. Click "Assign Appointment Numbers" button
3. System automatically assigns prefixes based on each recruit's rank and GL level
4. Each prefix maintains its own sequence:
   - IC recruits get RCT00001, RCT00002, etc.
   - ASC II recruits get CDT00003, CDT00004, etc.
   - AIC recruits get RCT00005, RCT00006, etc.
   - DSC recruits get CDT00007, CDT00008, etc.

### How It Works:
- **CDT prefix** is automatically assigned for:
  - ASC II with GL 08 and above
  - DSC ranks
- **RCT prefix** is automatically assigned for:
  - IC with GL 07 and below
  - AIC ranks
- Numbers are sequential **per prefix** (not global)
- Example sequences:
  - IC: RCT00001, RCT00002, RCT00003...
  - ASC II: CDT00001, CDT00002, CDT00003...
  - AIC: RCT00005, RCT00006, RCT00007...
  - DSC: CDT00007, CDT00008, CDT00009...

---

## Step 3: Officers Undergo Training

### What Happens:
- Officers use their appointment numbers (CDT/RCT) throughout training
- These numbers are their identifiers during the training period
- No action needed from Establishment at this stage

---

## Step 4: TRADOC Uploads Training Results

### Location: **TRADOC** → **Upload Training Results**

**Important**: TRADOC must upload CSV files **grouped by rank**

### CSV Format:
```csv
Appointment Number,Officer Name,Training Score,Status
RCT00001,John Doe,95,PASS
RCT00002,Jane Smith,90,PASS
CDT00003,Mike Johnson,88,PASS
CDT00004,Sarah Brown,85,PASS
RCT00005,Tom Wilson,92,PASS
RCT00006,Emma Davis,87,PASS
CDT00007,Chris Lee,89,PASS
CDT00008,Lisa Garcia,86,PASS
```

### What System Does:
- System automatically extracts **substantive rank** from officer record based on appointment number
- System groups results by rank
- System sorts by performance (highest to lowest) **within each rank**

---

## Step 5: Assign Service Numbers (Rank-Based)

### Location: **Training Results** page

1. **Navigate to Training Results**
   - Click "Training Results" in the sidebar
   - View training results grouped by rank

2. **Review Rank-Based Preview**
   - System shows:
     - Each rank with count of officers
     - Last service number for each rank
     - Preview of where numbering will start for each rank
   - Example display:
     ```
     IC: 2 officer(s) (Last: NCS65000) → Will start from: NCS65001
     ASC II: 2 officer(s) (Last: NCS65200) → Will start from: NCS65201
     AIC: 2 officer(s) (Last: NCS65100) → Will start from: NCS65101
     DSC: 2 officer(s) (Last: NCS65300) → Will start from: NCS65301
     ```

3. **Assign Service Numbers**
   - Click "Assign Service Numbers by Rank" button
   - Confirm in the modal
   - System processes assignment

### How Service Numbers Are Assigned:

**Within Each Rank:**
- Highest scorer gets first available number for that rank
- Next scorer gets next number for that rank
- And so on...

**Example Assignment:**

**IC Rank** (Last was NCS65000):
- Officer with 95% → **NCS65001**
- Officer with 90% → **NCS65002**

**ASC II Rank** (Last was NCS65200):
- Officer with 88% → **NCS65201**
- Officer with 85% → **NCS65202**

**AIC Rank** (Last was NCS65100):
- Officer with 92% → **NCS65101**
- Officer with 87% → **NCS65102**

**DSC Rank** (Last was NCS65300):
- Officer with 89% → **NCS65301**
- Officer with 86% → **NCS65302**

### Key Points:
- ✅ Each rank maintains **independent sequence**
- ✅ Service numbers continue from **last number for that specific rank**
- ✅ Within each rank, assignment is **performance-based** (highest to lowest)
- ✅ Different ranks have **different sequences** (not global)

---

## Complete Workflow Example

### Scenario: Creating 4 New Recruits

**Step 1: Create Recruits**
1. Create IC recruit (GL 07) → No appointment number yet
2. Create ASC II recruit (GL 08) → No appointment number yet
3. Create AIC recruit (GL 06) → No appointment number yet
4. Create DSC recruit (GL 10) → No appointment number yet

**Step 2: Assign Appointment Numbers**
- IC (GL 07) → **RCT00001** (auto-assigned)
- ASC II (GL 08) → **CDT00001** (auto-assigned)
- AIC (GL 06) → **RCT00002** (auto-assigned)
- DSC (GL 10) → **CDT00002** (auto-assigned)

**Step 3: Training**
- Officers train using: RCT00001, CDT00001, RCT00002, CDT00002

**Step 4: TRADOC Uploads Results**
- CSV contains all 4 officers with their scores
- System groups by rank automatically

**Step 5: Assign Service Numbers**

**If last service numbers were:**
- IC: NCS65000
- ASC II: NCS65200
- AIC: NCS65100
- DSC: NCS65300

**Assignment (by performance within each rank):**
- IC (95%) → **NCS65001**
- IC (90%) → **NCS65002**
- ASC II (88%) → **NCS65201**
- ASC II (85%) → **NCS65202**
- AIC (92%) → **NCS65101**
- AIC (87%) → **NCS65102**
- DSC (89%) → **NCS65301**
- DSC (86%) → **NCS65302**

---

## Important Notes

### Appointment Number Assignment:
- ✅ **Automatic** - System determines CDT vs RCT based on rank and GL level
- ✅ **Sequential per prefix** - Each prefix (CDT/RCT) has its own counter
- ✅ **No manual input needed** - Just select rank and GL level

### Service Number Assignment:
- ✅ **Rank-based** - Each rank has its own sequence
- ✅ **Performance-based within rank** - Highest scorer gets first number for that rank
- ✅ **Automatic tracking** - System tracks last service number per rank
- ✅ **No manual entry needed** - System handles everything automatically

### CSV Upload Requirements:
- ✅ Must include appointment numbers (CDT/RCT)
- ✅ System automatically groups by rank
- ✅ System automatically sorts by performance within each rank
- ✅ Results can be uploaded all together (system groups them)

---

## Troubleshooting

### Q: How do I know which prefix will be assigned?
**A**: The system automatically determines based on:
- **CDT**: ASC II (GL 08+), DSC
- **RCT**: IC (GL 07-), AIC

### Q: What if I upload CSV with mixed ranks?
**A**: System automatically groups them by rank and processes each rank separately.

### Q: How does the system know the last service number per rank?
**A**: System automatically queries the database to find the last service number assigned to officers with that specific rank.

### Q: Can I manually override the prefix?
**A**: Yes, there's an option to manually specify prefix, but auto-assignment is recommended.

### Q: What happens if two officers in the same rank have the same score?
**A**: System uses appointment number as secondary sort (alphabetical order).

---

## Quick Reference

| Action | Location | Auto/Manual |
|--------|----------|-------------|
| Create Recruit | New Recruits → Add New Recruit | Manual (form) |
| Assign Appointment Number | New Recruits → Select → Assign | **Auto** (prefix determined) |
| Upload Training Results | TRADOC → Upload CSV | Manual (TRADOC does this) |
| Assign Service Numbers | Training Results → Assign | **Auto** (rank-based) |

---

## Summary

The workflow is now **fully automated**:
1. ✅ Create recruits with rank and GL level
2. ✅ System auto-assigns CDT/RCT appointment numbers
3. ✅ Officers train using appointment numbers
4. ✅ TRADOC uploads CSV (system groups by rank)
5. ✅ System auto-assigns service numbers by rank (performance-based)

**No manual calculation needed** - the system handles all sequencing automatically!

