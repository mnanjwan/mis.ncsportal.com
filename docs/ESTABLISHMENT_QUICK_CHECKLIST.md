# Establishment Service Number Workflow - Quick Checklist

## ✅ Step-by-Step Action Checklist

### 📝 STEP 1: Create New Recruits

**Location**: Sidebar → **New Recruits** → Click **"Add New Recruit"**

**Fill Form**:
- [ ] Initials: `___`
- [ ] Surname: `___`
- [ ] Sex: `M` or `F`
- [ ] Date of Birth: `___`
- [ ] Date of First Appointment: `___`
- [ ] **Substantive Rank**: Select from dropdown
  - [ ] For **CDT**: Select **ASC II** or **DSC**
  - [ ] For **RCT**: Select **IC** or **AIC**
- [ ] **Salary Grade Level**: Select from dropdown
  - [ ] ASC II → Select **GL 08** or higher (gets CDT)
  - [ ] IC → Select **GL 07** or lower (gets RCT)
  - [ ] AIC → Any GL (gets RCT)
  - [ ] DSC → Any GL (gets CDT)
- [ ] Email: `___`
- [ ] Phone: `___` (optional)

**Click**: "Create Recruit"

---

### 🎯 STEP 2: Assign Appointment Numbers

**Location**: **New Recruits** page

**Option A - Single Assignment**:
1. [ ] Find recruit in list
2. [ ] Click **three dots** (action menu)
3. [ ] Click **"Assign Appointment"**
4. [ ] System shows: "Prefix will be automatically determined"
5. [ ] Click **"Assign"**
6. [ ] ✅ Appointment number assigned (CDT or RCT)

**Option B - Bulk Assignment**:
1. [ ] Select multiple recruits (checkboxes)
2. [ ] Click **"Assign Appointment Numbers"** button
3. [ ] System auto-determines prefixes for each
4. [ ] Click **"Assign to Selected"**
5. [ ] ✅ All appointment numbers assigned

**What You'll See**:
- IC recruits → RCT00001, RCT00002, etc.
- ASC II recruits → CDT00001, CDT00002, etc.
- AIC recruits → RCT00005, RCT00006, etc.
- DSC recruits → CDT00007, CDT00008, etc.

---

### 🎓 STEP 3: Officers Train

**No Action Needed** - Officers use their appointment numbers during training

---

### 📤 STEP 4: TRADOC Uploads Results

**Location**: TRADOC → Upload Training Results

**CSV Format** (TRADOC prepares this):
```csv
Appointment Number,Officer Name,Training Score,Status
RCT00001,John Doe,95,PASS
RCT00002,Jane Smith,90,PASS
CDT00003,Mike Johnson,88,PASS
...
```

**What Happens**:
- [ ] TRADOC uploads CSV file
- [ ] System automatically groups by rank
- [ ] System sorts by performance within each rank
- [ ] Results appear in **Training Results** page

---

### 🔢 STEP 5: Assign Service Numbers

**Location**: Sidebar → **Training Results**

**What You'll See**:
```
Rank-Based Assignment Preview:
- IC: 2 officer(s) (Last: NCS65000) → Will start from: NCS65001
- ASC II: 2 officer(s) (Last: NCS65200) → Will start from: NCS65201
- AIC: 2 officer(s) (Last: NCS65100) → Will start from: NCS65101
- DSC: 2 officer(s) (Last: NCS65300) → Will start from: NCS65301
```

**Actions**:
1. [ ] Review the rank-based preview
2. [ ] Click **"Assign Service Numbers by Rank"** button
3. [ ] Confirm in modal
4. [ ] ✅ Service numbers assigned automatically

**Result**:
- Each rank gets its own sequence
- Highest scorer in each rank gets first number for that rank
- Next scorer gets next number for that rank

---

## 🎯 Example Workflow

### Creating 4 Recruits:

**Recruit 1**: IC, GL 07
- Appointment: **RCT00001** (auto)
- Training Score: 95%
- Service Number: **NCS65001** (if last IC was NCS65000)

**Recruit 2**: ASC II, GL 08
- Appointment: **CDT00001** (auto)
- Training Score: 88%
- Service Number: **NCS65201** (if last ASC II was NCS65200)

**Recruit 3**: AIC, GL 06
- Appointment: **RCT00002** (auto)
- Training Score: 92%
- Service Number: **NCS65101** (if last AIC was NCS65100)

**Recruit 4**: DSC, GL 10
- Appointment: **CDT00002** (auto)
- Training Score: 89%
- Service Number: **NCS65301** (if last DSC was NCS65300)

---

## ⚡ Quick Tips

1. **Appointment Numbers**: System auto-assigns CDT/RCT - you don't need to calculate
2. **Service Numbers**: System auto-assigns by rank - you don't need to track manually
3. **CSV Upload**: Can include all ranks together - system groups automatically
4. **Performance Sorting**: Automatic within each rank
5. **Sequencing**: Automatic per rank and per prefix

---

## 🆘 Need Help?

- **See full guide**: `docs/ESTABLISHMENT_SERVICE_NUMBER_WORKFLOW_GUIDE.md`
- **Check system specification**: `docs/SYSTEM_SPECIFICATION.md`

