# Feature 02: Apply for Leave

> **Source studied:** `LeaveApplicationController.php` (381 lines), `LeaveApplication.php` model, `LeaveType.php` model, `apply.blade.php` (342 lines), `NotificationService.php` (leave-related methods)

---

## 1. Feature Overview

Officers can apply for various types of leave. The leave system is more complex than pass — it supports multiple leave types (Annual, Sick, Maternity, Casual, Compassionate, Study, Pre-Retirement), each with different duration limits, file requirements, and validation rules. The approval workflow mirrors pass: Officer → Staff Officer minute → DC Admin approve/reject.

---

## 2. Leave Types (from LeaveType Model)

### `leave_types` Table

| Field | Type | Purpose |
|-------|------|---------|
| `name` | string | Display name (e.g., "Annual Leave") |
| `code` | string | System code: `ANNUAL_LEAVE`, `SICK_LEAVE`, `MATERNITY_LEAVE`, `CASUAL_LEAVE`, `COMPASSIONATE_LEAVE`, `STUDY_LEAVE`, `PRE_RETIREMENT_LEAVE` |
| `max_duration_days` | integer (nullable) | Maximum days per application |
| `max_duration_months` | integer (nullable) | Alternative duration limit |
| `max_occurrences_per_year` | integer (nullable) | How many times per year (default 2 for annual) |
| `requires_medical_certificate` | boolean | Is medical cert required? |
| `requires_approval_level` | string | Approval level required |
| `is_active` | boolean | Available for selection? |
| `description` | text | Description of the leave type |

### Leave Type Specific Rules

| Leave Type | Max Days | Max Per Year | Special Requirements |
|-----------|----------|-------------|---------------------|
| Annual Leave | 28–30 days (GL 07 below: 28, Level 08+: 30) | 2 times/year | None |
| Sick Leave | Varies | — | Medical certificate upload |
| Maternity Leave | 120 days | — | Expected Date of Delivery (EDD) required |
| Casual Leave | Varies | — | None |
| Compassionate Leave | Varies | — | None |
| Study Leave | Varies | — | None |
| Pre-Retirement Leave | — | — | **BLOCKED** — cannot be applied by officers (CGC-managed only) |

---

## 3. Business Rules (from Controller Validation)

| Rule | Source | Implementation |
|------|--------|---------------|
| Leave type must exist and be valid | `required\|exists:leave_types,id` | Dropdown from API |
| `start_date` must be today or later | `after_or_equal:today` | DatePicker min = today |
| `end_date` must be after `start_date` | `after:start_date` | DatePicker min = startDate + 1 |
| Duration cannot exceed `max_duration_days` | Controller line 79 | Client shows max, server enforces |
| Annual leave: max `max_occurrences_per_year` applications/year | Controller line 84-93 | Check before showing form |
| Expected Date of Delivery required for maternity (leave_type_id = 6) | `required_if:leave_type_id,6` | Conditional field visibility |
| Medical certificate optional (JPEG/PDF/PNG, max 5MB) | `nullable\|file\|mimes:jpeg,jpg,png,pdf\|max:5120` | File picker |
| **Pre-retirement leave BLOCKED** | Controller line 69-71 | Hide from dropdown |

---

## 4. Data Model

### `leave_applications` Table

```
┌───────────────────────────┬────────────────┬──────────────────────────────────┐
│ Column                    │ Type           │ Notes                            │
├───────────────────────────┼────────────────┼──────────────────────────────────┤
│ id                        │ bigint PK      │ Auto-increment                   │
│ officer_id                │ bigint FK      │ → officers.id                    │
│ leave_type_id             │ bigint FK      │ → leave_types.id                 │
│ start_date                │ date           │ Must be ≥ today                  │
│ end_date                  │ date           │ Must be > start_date             │
│ number_of_days            │ integer        │ Calculated: diff + 1             │
│ reason                    │ text (nullable)│ Reason for leave                 │
│ expected_date_of_delivery │ date (nullable)│ Required for Maternity only      │
│ medical_certificate_url   │ string         │ Stored file path                 │
│ status                    │ string         │ PENDING / APPROVED / REJECTED    │
│ submitted_at              │ timestamp      │ Set on creation                  │
│ minuted_at                │ timestamp      │ Set by Staff Officer             │
│ approved_at               │ timestamp      │ Set by DC Admin                  │
│ rejected_at               │ timestamp      │ Set by DC Admin                  │
│ rejection_reason          │ text (nullable)│ Required on rejection            │
│ created_at                │ timestamp      │                                  │
│ updated_at                │ timestamp      │                                  │
└───────────────────────────┴────────────────┴──────────────────────────────────┘
```

---

## 5. Workflow — Status Flow

```
  ┌──────────┐
  │ Officer  │
  │ submits  │
  │ leave    │
  └────┬─────┘
       │
       ▼
  ┌──────────┐     ┌──────────────┐     ┌──────────────┐
  │ PENDING  │────▶│ Staff Officer │────▶│ DC Admin     │
  │          │     │ minutes      │     │ approves or  │
  │          │     │ (minuted_at) │     │ rejects      │
  │          │     │ + notifies   │     │              │
  │          │     │ Officer +    │     │              │
  │          │     │ DC Admin     │     │              │
  └──────────┘     └──────────────┘     └──────┬───────┘
                                              │
                                    ┌─────────┴──────────┐
                                    │                    │
                                    ▼                    ▼
                              ┌──────────┐        ┌──────────┐
                              │ APPROVED │        │ REJECTED │
                              │          │        │ + reason │
                              └──────────┘        └──────────┘
```

### Key difference from Pass:
When Staff Officer minutes a leave application, **two notifications** are sent:
1. `notifyLeaveApplicationMinuted()` → Officer (your leave has been minuted)
2. `notifyLeaveApplicationMinutedToDcAdmin()` → DC Admins in the command (requires your approval)

---

## 6. API Endpoints Required

### New API Endpoints Needed

```
GET    /api/v1/leave-types                           → List active leave types
POST   /api/v1/leave-applications                    → Submit leave application
GET    /api/v1/leave-applications                    → List officer's own leaves (paginated)
GET    /api/v1/leave-applications/{id}               → View leave detail
POST   /api/v1/leave-applications/{id}/minute        → Staff Officer minutes
POST   /api/v1/leave-applications/{id}/approve       → DC Admin approves
POST   /api/v1/leave-applications/{id}/reject        → DC Admin rejects
GET    /api/v1/leave-applications/pending             → Staff Officer: command pending
GET    /api/v1/leave-applications/minuted             → DC Admin: minuted awaiting approval
GET    /api/v1/leave-applications/balance             → Officer's leave balance/usage
```

### API Request/Response Specs

#### `GET /api/v1/leave-types` — List Leave Types

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Annual Leave",
      "code": "ANNUAL_LEAVE",
      "max_duration_days": 30,
      "max_occurrences_per_year": 2,
      "requires_medical_certificate": false,
      "description": "Standard annual leave entitlement"
    },
    {
      "id": 2,
      "name": "Sick Leave",
      "code": "SICK_LEAVE",
      "max_duration_days": null,
      "max_occurrences_per_year": null,
      "requires_medical_certificate": true,
      "description": "Leave due to illness"
    },
    {
      "id": 6,
      "name": "Maternity Leave",
      "code": "MATERNITY_LEAVE",
      "max_duration_days": 120,
      "max_occurrences_per_year": null,
      "requires_medical_certificate": false,
      "description": "Maternity leave with EDD requirement"
    }
  ]
}
```

> **Note:** `PRE_RETIREMENT_LEAVE` is excluded from this list — it cannot be applied by officers.

#### `POST /api/v1/leave-applications` — Submit Leave

**Request Body (multipart/form-data for file upload):**
```
leave_type_id: 1
start_date: 2026-03-01
end_date: 2026-03-15
reason: Annual family vacation
expected_date_of_delivery: null (only for maternity)
medical_certificate: [file] (only for sick leave)
```

**Validation:**
```php
'leave_type_id'             => 'required|exists:leave_types,id',
'start_date'                => 'required|date|after_or_equal:today',
'end_date'                  => 'required|date|after:start_date',
'reason'                    => 'nullable|string',
'expected_date_of_delivery' => 'required_if:leave_type_id,6|nullable|date',
'medical_certificate'       => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120'
```

**Business Logic Checks:**
1. Officer record must exist
2. Leave type is not `PRE_RETIREMENT_LEAVE`
3. Duration ≤ `max_duration_days` for the selected leave type
4. For annual leave: count < `max_occurrences_per_year`
5. If maternity: `expected_date_of_delivery` must be provided

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Leave application submitted successfully.",
  "data": {
    "id": 88,
    "officer_id": 15,
    "leave_type_id": 1,
    "leave_type": { "id": 1, "name": "Annual Leave", "code": "ANNUAL_LEAVE" },
    "start_date": "2026-03-01",
    "end_date": "2026-03-15",
    "number_of_days": 15,
    "reason": "Annual family vacation",
    "status": "PENDING",
    "submitted_at": "2026-02-24T17:30:00Z"
  }
}
```

#### `GET /api/v1/leave-applications/balance` — Leave Balance

**Response:**
```json
{
  "status": "success",
  "data": {
    "annual_leave": {
      "max_days": 30,
      "max_applications_per_year": 2,
      "applications_used": 1,
      "applications_remaining": 1,
      "days_used": 15,
      "days_remaining": 15
    },
    "sick_leave": {
      "applications_used": 0
    },
    "maternity_leave": {
      "applications_used": 0,
      "max_days": 120
    },
    "pass_eligible": false
  }
}
```

#### `POST /api/v1/leave-applications/{id}/reject` — DC Admin Rejects

**Request Body:**
```json
{
  "rejection_reason": "Insufficient coverage during period"
}
```

**Validation:**
```php
'rejection_reason' => 'required|string|max:500'
```

---

## 7. Notifications Triggered

| Event | Method | Recipients | Mobile Push |
|-------|--------|-----------|------------|
| Leave submitted | `notifyLeaveApplicationSubmitted()` | Staff Officers (same command) | ✅ |
| Leave minuted (to officer) | `notifyLeaveApplicationMinuted()` | Officer (applicant) | ✅ |
| Leave minuted (to DC Admin) | `notifyLeaveApplicationMinutedToDcAdmin()` | DC Admins (same command) | ✅ |
| Leave approved | `notifyLeaveApplicationApproved()` | Officer (applicant) | ✅ |
| Leave rejected | `notifyLeaveApplicationRejected()` | Officer (applicant) | ✅ |

---

## 8. Mobile Screens

### Screen 8.1: Leave Application List (Officer)

```
┌─────────────────────────────────────┐
│  My Leave Applications              │
│  ─────────────────────────────────  │
│                                     │
│  Leave Balance: 15/30 days left     │
│  Applications: 1/2 annual used      │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Annual Leave                │   │
│  │ 01 Mar – 15 Mar 2026       │   │
│  │ 15 days · Family vacation  │   │
│  │ ⏳ PENDING                  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Sick Leave                  │   │
│  │ 05 Jan – 08 Jan 2026       │   │
│  │ 4 days · Medical treatment │   │
│  │ ✅ APPROVED  📎 Med. cert  │   │
│  └─────────────────────────────┘   │
│                                     │
│         [+ Apply for Leave]         │
└─────────────────────────────────────┘
```

### Screen 8.2: Apply for Leave Form

```
┌─────────────────────────────────────┐
│  ← Apply for Leave                  │
│  ─────────────────────────────────  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Leave Type                  │   │
│  │ [🔽 Select Leave Type    ] │   │
│  │ • Annual Leave              │   │
│  │ • Sick Leave                │   │
│  │ • Maternity Leave           │   │
│  │ • Casual Leave              │   │
│  │ • Compassionate Leave       │   │
│  │ • Study Leave               │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌──────────┐  ┌──────────────┐   │
│  │Start Date│  │ End Date     │   │
│  │[📅 Pick ]│  │ [📅 Pick   ]│   │
│  └──────────┘  └──────────────┘   │
│                                     │
│  Duration: 15 days (max 30)         │
│                                     │
│  ╔═══════════════════════════════╗  │
│  ║ 📅 Expected Date of Delivery ║  │  ← Only for Maternity
│  ║ [📅 Pick date            ]   ║  │
│  ╚═══════════════════════════════╝  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Reason for Leave            │   │
│  │ [                        ]  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Medical Certificate         │   │
│  │ [📎 Upload JPEG/PDF]       │   │
│  │ Max 5MB                     │   │
│  └─────────────────────────────┘   │
│                                     │
│  [Cancel]        [Submit Application]│
└─────────────────────────────────────┘
```

### Screen 8.3: Leave Detail (Officer)

Same pattern as pass detail but with:
- Leave type badge
- Medical certificate preview/download (if attached)
- EDD field (if maternity)
- Timeline showing minuted_at step

---

## 9. React Native Implementation

### Component Structure
```
src/features/leave/
├── screens/
│   ├── LeaveListScreen.tsx
│   ├── LeaveApplyScreen.tsx
│   ├── LeaveDetailScreen.tsx
│   ├── StaffOfficerLeaveListScreen.tsx
│   └── DcAdminLeaveReviewScreen.tsx
├── components/
│   ├── LeaveCard.tsx
│   ├── LeaveTypePicker.tsx           → Searchable leave type selector
│   ├── LeaveStatusBadge.tsx
│   ├── LeaveBalanceBanner.tsx        → Shows remaining leave days
│   ├── MaternityFields.tsx           → Conditional EDD field
│   └── MedicalCertUploader.tsx       → File picker for medical cert
├── hooks/
│   ├── useLeaveApplications.ts
│   ├── useLeaveTypes.ts
│   └── useLeaveBalance.ts
├── api/
│   └── leaveApi.ts
└── types/
    └── leave.ts
```

### RTK Query API Slice
```typescript
// src/features/leave/api/leaveApi.ts
export const leaveApi = createApi({
  reducerPath: 'leaveApi',
  tagTypes: ['Leave', 'LeaveBalance', 'LeaveType'],
  endpoints: (builder) => ({
    getLeaveTypes: builder.query<LeaveType[], void>({
      query: () => '/leave-types',
      providesTags: ['LeaveType'],
    }),
    getMyLeaves: builder.query<PaginatedResponse<LeaveApplication>, { page?: number }>({
      query: ({ page = 1 }) => `/leave-applications?page=${page}`,
      providesTags: ['Leave'],
    }),
    getLeaveBalance: builder.query<LeaveBalance, void>({
      query: () => '/leave-applications/balance',
      providesTags: ['LeaveBalance'],
    }),
    submitLeave: builder.mutation<LeaveApplication, FormData>({
      query: (formData) => ({
        url: '/leave-applications',
        method: 'POST',
        body: formData,
        headers: { 'Content-Type': 'multipart/form-data' },
      }),
      invalidatesTags: ['Leave', 'LeaveBalance'],
    }),
    minuteLeave: builder.mutation<void, number>({
      query: (id) => ({ url: `/leave-applications/${id}/minute`, method: 'POST' }),
      invalidatesTags: ['Leave'],
    }),
    approveLeave: builder.mutation<void, number>({
      query: (id) => ({ url: `/leave-applications/${id}/approve`, method: 'POST' }),
      invalidatesTags: ['Leave'],
    }),
    rejectLeave: builder.mutation<void, { id: number; rejection_reason: string }>({
      query: ({ id, ...body }) => ({
        url: `/leave-applications/${id}/reject`,
        method: 'POST',
        body,
      }),
      invalidatesTags: ['Leave'],
    }),
  }),
});
```

### TypeScript Types
```typescript
export interface LeaveType {
  id: number;
  name: string;
  code: string;
  max_duration_days: number | null;
  max_duration_months: number | null;
  max_occurrences_per_year: number | null;
  requires_medical_certificate: boolean;
  is_active: boolean;
  description: string;
}

export interface LeaveApplication {
  id: number;
  officer_id: number;
  leave_type_id: number;
  leave_type?: LeaveType;
  start_date: string;
  end_date: string;
  number_of_days: number;
  reason: string | null;
  expected_date_of_delivery: string | null;
  medical_certificate_url: string | null;
  status: 'PENDING' | 'APPROVED' | 'REJECTED';
  submitted_at: string;
  minuted_at: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  rejection_reason: string | null;
  officer?: Officer;
}

export interface LeaveBalance {
  annual_leave: {
    max_days: number;
    max_applications_per_year: number;
    applications_used: number;
    applications_remaining: number;
    days_used: number;
    days_remaining: number;
  };
  sick_leave: { applications_used: number };
  maternity_leave: { applications_used: number; max_days: number };
  pass_eligible: boolean;
}
```

---

## 10. Client-Side Validation

```typescript
export function validateLeaveForm(
  values: SubmitLeaveRequest,
  leaveType: LeaveType | null
): Record<string, string> {
  const errors: Record<string, string> = {};

  if (!values.leave_type_id) errors.leave_type_id = 'Leave type is required';
  if (!values.start_date) errors.start_date = 'Start date is required';
  if (!values.end_date) errors.end_date = 'End date is required';

  const start = new Date(values.start_date);
  const end = new Date(values.end_date);
  const today = new Date(); today.setHours(0, 0, 0, 0);

  if (start < today) errors.start_date = 'Start date must be today or later';
  if (end <= start) errors.end_date = 'End date must be after start date';

  if (leaveType) {
    const days = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1;
    if (leaveType.max_duration_days && days > leaveType.max_duration_days) {
      errors.end_date = `Maximum ${leaveType.max_duration_days} days for ${leaveType.name}`;
    }

    // Maternity: EDD required
    if (leaveType.code === 'MATERNITY_LEAVE' && !values.expected_date_of_delivery) {
      errors.expected_date_of_delivery = 'Expected Date of Delivery is required for Maternity Leave';
    }
  }

  return errors;
}
```

---

## 11. Edge Cases

| Edge Case | Handling |
|-----------|----------|
| Officer selects Pre-Retirement Leave | Not shown in dropdown (filtered from API) |
| Maternity Leave selected | Show conditional EDD date field |
| Sick Leave selected | Highlight medical certificate upload |
| Duration exceeds leave type max | Client-side warning + server rejection |
| Annual leave max applications reached | Show banner + disable annual leave option |
| File upload > 5MB | Client-side rejection with error message |
| Unsupported file format | Client accepts only JPEG, JPG, PNG, PDF |
| Staff Officer minutes → 2 notifications | Officer + DC Admins both get push notifications |

---

## 12. Testing Checklist

- [ ] Fetch leave types (exclude PRE_RETIREMENT_LEAVE)
- [ ] Submit annual leave → PENDING
- [ ] Submit maternity leave without EDD → Error
- [ ] Submit maternity leave with EDD → Success
- [ ] Submit with medical certificate → File uploaded
- [ ] Duration exceeds max → Error
- [ ] Annual leave max applications reached → Error
- [ ] Leave balance endpoint returns correct data
- [ ] Staff Officer minutes → Officer + DC Admin notified
- [ ] DC Admin approves → Officer notified
- [ ] DC Admin rejects with reason → Officer notified
- [ ] Medical cert preview/download in detail view
- [ ] Conditional field visibility (EDD for maternity)
