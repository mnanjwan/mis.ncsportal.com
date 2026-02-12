# T&L System - Complete Roles List

## All Roles Involved in Transport & Logistics (T&L)

### 1. **CD** (Command Department)
- **Primary Function**: View/Create requests at Command level
- **Test User**: `tl_user_1@example.com`
- **Key Responsibilities**:
  - Create all types of requests
  - View requests and vehicles
  - Issue vehicles to officers
  - Process vehicle returns
  - Update vehicle service status
  - View returns reports

### 2. **Area Controller** (Unit Head)
- **Primary Function**: Approve/Forward Command requests
- **Test User**: `tl_user_2@example.com`
- **Key Responsibilities**:
  - Create New Vehicle requests
  - Create Re-allocation requests
  - Submit requests into workflow
  - Acknowledge receipt of released vehicles

### 3. **OC Workshop**
- **Primary Function**: Create Requisitions (Maintenance)
- **Test User**: `tl_user_3@example.com`
- **Key Responsibilities**:
  - Create Maintenance Requisitions
  - Upload bills/recommendations
  - Upload job completion receipts
  - View requisition status

### 4. **Staff Officer T&L**
- **Primary Function**: Create Repair/OPE requests
- **Test User**: `tl_user_4@example.com`
- **Key Responsibilities**:
  - Create Repair requests
  - Create OPE (Out of Pocket Expenses) requests
  - Create Vehicle Use requests
  - Approve basic requests (default workflow)

### 5. **CC T&L** (Comptroller T&L)
- **Primary Function**: Inventory Check (Propose) & Vehicle Release
- **Test User**: `tl_user_5@example.com`
- **Key Responsibilities**:
  - View vehicle inventory
  - Propose vehicle selection for New Vehicle requests
  - Approve & Release vehicles for Re-allocation requests
  - Release vehicles after approval chain for New Vehicle requests
  - Manage vehicle identifiers
  - View returns reports

### 6. **ACG TS**
- **Primary Function**: Approve Requisitions < ₦300k
- **Test User**: `tl_user_6@example.com`
- **Key Responsibilities**:
  - Approve Requisitions ≤ ₦300,000 (final approval)
  - Forward Requisitions > ₦300,000 to DCG FATS
  - Forward New Vehicle proposals (after CGC approval)
  - Approve/Reject/KIV decisions
  - Edit vehicle identifiers

### 7. **DCG FATS**
- **Primary Function**: Approve Requisitions < ₦500k
- **Test User**: `tl_user_7@example.com`
- **Key Responsibilities**:
  - Approve Requisitions > ₦300,000 and ≤ ₦500,000 (final approval)
  - Forward Requisitions > ₦500,000 to CGC
  - Forward New Vehicle proposals (after CGC approval)
  - Approve/Reject/KIV decisions
  - Edit vehicle identifiers

### 8. **CGC**
- **Primary Function**: Approve Requisitions > ₦500k
- **Test User**: `tl_user_8@example.com`
- **Key Responsibilities**:
  - Approve Requisitions > ₦500,000 (final approval)
  - Approve New Vehicle proposals (after CC T&L proposal)
  - Approve/Reject/KIV decisions
  - View all requests

### 9. **O/C T&L** (Officer Commanding T&L)
- **Primary Function**: Command-level T&L management
- **Key Responsibilities**:
  - View vehicles (command-scoped)
  - View requests
  - Access fleet dashboard

### 10. **Transport Store/Receiver**
- **Primary Function**: Vehicle intake and inventory management
- **Key Responsibilities**:
  - Intake new vehicles into system
  - Edit vehicle identifiers (Reg No, Engine No) — audit trail
  - View vehicles
  - View requests
  - Access fleet dashboard

---

## Role Categories

### **Request Creators**
- CD
- Area Controller
- OC Workshop
- Staff Officer T&L
- CC T&L

### **Approvers**
- CC T&L (Proposal & Release)
- CGC (High-level approval)
- DCG FATS (Mid-level approval)
- ACG TS (Low-level approval)
- Staff Officer T&L (Basic requests)

### **Forwarders**
- DCG FATS (Forwards to ACG TS)
- ACG TS (Forwards to CC T&L Release)

### **Vehicle Managers**
- CD (Issue/Return vehicles, edit identifiers)
- CC T&L (Inventory & Release, edit identifiers)
- Transport Store/Receiver (Intake, edit identifiers)
- O/C T&L (View vehicles)

### **Viewers**
- All roles can view requests
- CD, O/C T&L, Transport Store/Receiver, CC T&L, DCG FATS, ACG TS can view vehicles

---

## Workflow Participation

### **New Vehicle Request Flow**
1. Area Controller (Creates & Submits)
2. CC T&L (Proposes vehicles)
3. CGC (Approves proposal)
4. DCG FATS (Forwards)
5. ACG TS (Forwards)
6. CC T&L (Releases vehicles)

### **Re-allocation Request Flow**
1. Area Controller (Creates & Submits)
2. CC T&L (Approves & Releases)

### **Requisition Flow**
1. OC Workshop (Creates & Submits)
2. ACG TS (Approves ≤₦300k OR Forwards >₦300k)
3. DCG FATS (Approves ≤₦500k OR Forwards >₦500k)
4. CGC (Approves >₦500k)

### **Repair/OPE/Use Request Flow**
1. Staff Officer T&L (Creates & Submits)
2. Staff Officer T&L (Approves)

---

## Total Roles: **10**

1. CD
2. Area Controller
3. OC Workshop
4. Staff Officer T&L
5. CC T&L
6. ACG TS
7. DCG FATS
8. CGC
9. O/C T&L
10. Transport Store/Receiver

---

**Note**: All roles have access to view requests. Roles are command-scoped where applicable (CD, Area Controller, OC Workshop, Staff Officer T&L, O/C T&L, Transport Store/Receiver), while approval roles (CC T&L, ACG TS, DCG FATS, CGC) operate at the organizational level.
