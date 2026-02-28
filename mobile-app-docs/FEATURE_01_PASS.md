# Feature 01: Apply for Pass

> **Source studied:** `PassApplicationController.php` (365 lines), `PassApplication.php` model, `apply.blade.php` (165 lines), `NotificationService.php` (pass-related methods)

---

## 1. Feature Overview

A **Pass** allows officers to be absent from duty for short periods (1–5 days) after exhausting their annual leave. The mobile app must replicate the full pass lifecycle: submission, minute, approval/rejection, and status tracking.

---

## 2. Business Rules (from Controller Validation)

| Rule | Source | Implementation |
|------|--------|---------------|
| Maximum **5 days** per pass application | `PassApplicationController::store()` line 64 | Client-side + server-side validation |
| Maximum **2 passes per year** (APPROVED only) | `PassApplicationController::store()` line 85-92 | Server rejects; app checks before submit |
| **Annual leave must be exhausted** before applying | `PassApplicationController::store()` line 69-82 | Officer must have `≥ max_occurrences_per_year` APPROVED annual leave applications for current year |
| `start_date` must be **today or later** | Validation rule: `after_or_equal:today` | DatePicker minimum = today |
| `end_date` must be **after** `start_date` | Validation rule: `after:start_date` | DatePicker minimum = startDate + 1 |
| `reason` is **optional** (nullable in validation, but required in blade) | Validation: `nullable|string` vs blade: `required` | Match blade behavior — make it required in mobile |

---

## 3. Data Model

### `pass_applications` Table

```
┌──────────────────────┬────────────────┬──────────────────────────────┐
│ Column               │ Type           │ Notes                        │
├──────────────────────┼────────────────┼──────────────────────────────┤
│ id                   │ bigint PK      │ Auto-increment               │
│ officer_id           │ bigint FK      │ → officers.id                │
│ start_date           │ date           │ Must be ≥ today              │
│ end_date             │ date           │ Must be > start_date         │
│ number_of_days       │ integer        │ Calculated: diff + 1, max 5  │
│ reason               │ text (nullable)│ Why the pass is needed       │
│ status               │ string         │ PENDING / APPROVED / REJECTED│
│ submitted_at         │ timestamp      │ Set on creation              │
│ minuted_at           │ timestamp      │ Set by Staff Officer         │
│ approved_at          │ timestamp      │ Set by DC Admin              │
│ rejected_at          │ timestamp      │ Set by DC Admin              │
│ rejection_reason     │ text (nullable)│ Required on rejection        │
│ created_at           │ timestamp      │                              │
│ updated_at           │ timestamp      │                              │
└──────────────────────┴────────────────┴──────────────────────────────┘
```

---

## 4. Workflow — Status Flow

```
  ┌──────────┐
  │ Officer  │
  │ submits  │
  └────┬─────┘
       │
       ▼
  ┌──────────┐     ┌──────────────┐     ┌──────────────┐
  │ PENDING  │────▶│ Staff Officer │────▶│ DC Admin     │
  │          │     │ minutes      │     │ approves or  │
  │          │     │ (minuted_at) │     │ rejects      │
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

### Status Transitions

| From | To | Action By | Conditions |
|------|----|-----------|------------|
| — | PENDING | Officer | Submission passes all validation |
| PENDING | PENDING (+ minuted_at) | Staff Officer | Must be from same command |
| PENDING (minuted) | APPROVED | DC Admin | `minuted_at` must be set |
| PENDING (minuted) | REJECTED | DC Admin | `minuted_at` must be set, `rejection_reason` required |

---

## 5. Role-Based Access

| Role | Can Do | Scope |
|------|--------|-------|
| **Officer** | Submit pass, View own passes | Own passes only |
| **Staff Officer** | View command passes, Minute pending | Same command only (`present_station = command_id`) |
| **DC Admin** | View minuted passes, Approve, Reject | Must be minuted first |
| **Area Controller** | View all passes (read-only) | All commands |

---

## 6. API Endpoints Required

### Existing Endpoints (from `api.php`)
None directly for pass — currently web-only routes.

### New API Endpoints Needed

```
POST   /api/v1/pass-applications              → Submit pass application
GET    /api/v1/pass-applications              → List officer's own passes (paginated)
GET    /api/v1/pass-applications/{id}         → View pass detail
POST   /api/v1/pass-applications/{id}/minute  → Staff Officer minutes
POST   /api/v1/pass-applications/{id}/approve → DC Admin approves
POST   /api/v1/pass-applications/{id}/reject  → DC Admin rejects
GET    /api/v1/pass-applications/pending      → Staff Officer: command pending passes
GET    /api/v1/pass-applications/minuted      → DC Admin: minuted passes
GET    /api/v1/pass-applications/eligibility  → Check eligibility (annual leave status + pass count)
```

### API Request/Response Specs

#### `POST /api/v1/pass-applications` — Submit

**Request Body:**
```json
{
  "start_date": "2026-03-01",
  "end_date": "2026-03-04",
  "reason": "Family obligations"
}
```

**Validation (server-side):**
```php
'start_date' => 'required|date|after_or_equal:today',
'end_date'   => 'required|date|after:start_date',
'reason'     => 'required|string'
```

**Business Logic Checks (in order):**
1. Officer record must exist
2. `diffInDays + 1 ≤ 5`
3. APPROVED annual leave count ≥ `max_occurrences_per_year` (default 2)
4. APPROVED pass count for year < 2

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Pass application submitted successfully.",
  "data": {
    "id": 42,
    "officer_id": 15,
    "start_date": "2026-03-01",
    "end_date": "2026-03-04",
    "number_of_days": 4,
    "reason": "Family obligations",
    "status": "PENDING",
    "submitted_at": "2026-02-24T17:30:00Z"
  }
}
```

**Error Responses:**
```json
// 422 - Validation error
{"status": "error", "message": "Pass cannot exceed 5 days"}

// 422 - Annual leave not exhausted
{"status": "error", "message": "Annual leave must be exhausted before applying for pass. You must have at least 2 approved annual leave application(s) for this year."}

// 422 - Max passes reached
{"status": "error", "message": "Maximum 2 passes per year allowed"}
```

#### `POST /api/v1/pass-applications/{id}/minute` — Staff Officer Minutes

**No request body required.**

**Validation:**
- User must have `Staff Officer` role
- Application must be from same command (`present_station = command_id`)
- `minuted_at` must be null (not already minuted)
- Status must be `PENDING`

**Success Response:**
```json
{
  "status": "success",
  "message": "Application has been minuted to DC Admin for approval."
}
```

#### `POST /api/v1/pass-applications/{id}/approve` — DC Admin Approves

**No request body required.**

**Validation:**
- User must have `DC Admin` role
- `minuted_at` must be set
- Status must be `PENDING`

#### `POST /api/v1/pass-applications/{id}/reject` — DC Admin Rejects

**Request Body:**
```json
{
  "rejection_reason": "Insufficient staffing during requested period"
}
```

**Validation:**
```php
'rejection_reason' => 'required|string|max:500'
```

#### `GET /api/v1/pass-applications/eligibility` — Check Eligibility

**Response:**
```json
{
  "eligible": true,
  "annual_leave_exhausted": true,
  "annual_leave_count": 2,
  "annual_leave_required": 2,
  "passes_used_this_year": 1,
  "passes_remaining": 1,
  "max_passes_per_year": 2,
  "max_days_per_pass": 5
}
```

---

## 7. Notifications Triggered

| Event | Method | Recipients | Mobile Push |
|-------|--------|-----------|------------|
| Pass submitted | `notifyPassApplicationSubmitted()` | Staff Officers (same command) | ✅ |
| Pass approved | `notifyPassApplicationApproved()` | Officer (applicant) | ✅ |
| Pass rejected | `notifyPassApplicationRejected()` | Officer (applicant) | ✅ |

---

## 8. Mobile Screens

### Screen 8.1: Pass Application List (Officer)

```
┌─────────────────────────────────────┐
│  My Pass Applications               │
│  ─────────────────────────────────  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 01 Mar – 04 Mar 2026       │   │
│  │ 4 days · Family obligations │   │
│  │ ⏳ PENDING                  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 15 Jan – 18 Jan 2026       │   │
│  │ 4 days · Personal matters  │   │
│  │ ✅ APPROVED                 │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 10 Dec – 12 Dec 2025       │   │
│  │ 3 days · Health reasons    │   │
│  │ ❌ REJECTED                 │   │
│  │ Reason: Staffing shortage  │   │
│  └─────────────────────────────┘   │
│                                     │
│         [+ Apply for Pass]          │
└─────────────────────────────────────┘
```

### Screen 8.2: Apply for Pass Form

```
┌─────────────────────────────────────┐
│  ← Apply for Pass                   │
│  ─────────────────────────────────  │
│                                     │
│  ⚠️ Pass Eligibility:               │
│  Annual Leave: ✅ Exhausted (2/2)   │
│  Passes Used: 1 of 2 this year     │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Start Date                  │   │
│  │ [📅 Select date         ]  │   │
│  │ (must be today or later)    │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ End Date                    │   │
│  │ [📅 Select date         ]  │   │
│  │ (max 5 days from start)     │   │
│  └─────────────────────────────┘   │
│                                     │
│  Duration: 4 days                   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Reason for Pass             │   │
│  │ [                        ]  │   │
│  │ [                        ]  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Supporting Documents        │   │
│  │ [📎 Upload (JPEG/PDF)]     │   │
│  │ Max 5MB per file            │   │
│  └─────────────────────────────┘   │
│                                     │
│  [Cancel]        [Submit Application]│
└─────────────────────────────────────┘
```

### Screen 8.3: Pass Detail (Officer)

```
┌─────────────────────────────────────┐
│  ← Pass Application #42            │
│  ─────────────────────────────────  │
│                                     │
│  Status: ⏳ PENDING                 │
│                                     │
│  ┌─ Details ─────────────────────┐ │
│  │ Start Date:  01 Mar 2026      │ │
│  │ End Date:    04 Mar 2026      │ │
│  │ Duration:    4 days           │ │
│  │ Reason:      Family obligations│ │
│  │ Submitted:   24 Feb 2026     │ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌─ Timeline ────────────────────┐ │
│  │ ● Submitted      24 Feb 14:30│ │
│  │ ○ Minuted        ---         │ │
│  │ ○ Approved/Rej.  ---         │ │
│  └───────────────────────────────┘ │
└─────────────────────────────────────┘
```

### Screen 8.4: Staff Officer — Command Pass List

```
┌─────────────────────────────────────┐
│  Pass Applications (My Command)     │
│  [All] [Pending] [Approved] [Rej.]  │
│  ─────────────────────────────────  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ ASC II A.B. Smith           │   │
│  │ NCS/12345                   │   │
│  │ 01 Mar – 04 Mar · 4 days   │   │
│  │ ⏳ PENDING (Not minuted)    │   │
│  │            [Minute →]       │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Insp. C.D. Johnson          │   │
│  │ NCS/67890                   │   │
│  │ 10 Mar – 13 Mar · 4 days   │   │
│  │ ⏳ PENDING (Minuted ✓)     │   │
│  │  Awaiting DC Admin approval │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

### Screen 8.5: DC Admin — Approve/Reject

```
┌─────────────────────────────────────┐
│  ← Pass Application Review         │
│  ─────────────────────────────────  │
│                                     │
│  Officer: ASC II A.B. Smith         │
│  S/N:     NCS/12345                 │
│  Command: Lagos Command             │
│                                     │
│  Start:   01 Mar 2026              │
│  End:     04 Mar 2026              │
│  Days:    4                         │
│  Reason:  Family obligations        │
│                                     │
│  Minuted: ✓ 24 Feb 2026 15:00     │
│                                     │
│  ┌────────────┐  ┌────────────┐    │
│  │  Approve ✓ │  │  Reject ✗  │    │
│  └────────────┘  └────────────┘    │
│                                     │
│  (Reject shows reason text field)   │
└─────────────────────────────────────┘
```

---

## 9. React Native Implementation

### Component Structure
```
src/features/pass/
├── screens/
│   ├── PassListScreen.tsx         → List of officer's passes
│   ├── PassApplyScreen.tsx        → Apply for pass form
│   ├── PassDetailScreen.tsx       → View pass detail + timeline
│   ├── StaffOfficerPassListScreen.tsx → Command pass list
│   └── DcAdminPassReviewScreen.tsx    → Approve/reject view
├── components/
│   ├── PassCard.tsx               → Pass summary card
│   ├── PassStatusBadge.tsx        → Status indicator
│   ├── PassTimeline.tsx           → Progress timeline
│   └── EligibilityBanner.tsx      → Shows eligibility status
├── hooks/
│   ├── usePassApplications.ts     → RTK Query hooks
│   └── usePassEligibility.ts      → Check eligibility
├── api/
│   └── passApi.ts                 → RTK Query API slice
└── types/
    └── pass.ts                    → TypeScript types
```

### RTK Query API Slice
```typescript
// src/features/pass/api/passApi.ts
import { createApi } from '@reduxjs/toolkit/query/react';

export const passApi = createApi({
  reducerPath: 'passApi',
  tagTypes: ['Pass', 'PassEligibility'],
  endpoints: (builder) => ({
    getMyPasses: builder.query<PaginatedResponse<Pass>, { page?: number }>({
      query: ({ page = 1 }) => `/pass-applications?page=${page}`,
      providesTags: ['Pass'],
    }),
    getPassById: builder.query<Pass, number>({
      query: (id) => `/pass-applications/${id}`,
      providesTags: (result, error, id) => [{ type: 'Pass', id }],
    }),
    checkEligibility: builder.query<PassEligibility, void>({
      query: () => '/pass-applications/eligibility',
      providesTags: ['PassEligibility'],
    }),
    submitPass: builder.mutation<Pass, SubmitPassRequest>({
      query: (body) => ({ url: '/pass-applications', method: 'POST', body }),
      invalidatesTags: ['Pass', 'PassEligibility'],
    }),
    minutePass: builder.mutation<void, number>({
      query: (id) => ({ url: `/pass-applications/${id}/minute`, method: 'POST' }),
      invalidatesTags: ['Pass'],
    }),
    approvePass: builder.mutation<void, number>({
      query: (id) => ({ url: `/pass-applications/${id}/approve`, method: 'POST' }),
      invalidatesTags: ['Pass'],
    }),
    rejectPass: builder.mutation<void, { id: number; rejection_reason: string }>({
      query: ({ id, ...body }) => ({ url: `/pass-applications/${id}/reject`, method: 'POST', body }),
      invalidatesTags: ['Pass'],
    }),
  }),
});
```

### TypeScript Types
```typescript
// src/features/pass/types/pass.ts
export interface Pass {
  id: number;
  officer_id: number;
  start_date: string;
  end_date: string;
  number_of_days: number;
  reason: string | null;
  status: 'PENDING' | 'APPROVED' | 'REJECTED';
  submitted_at: string;
  minuted_at: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  rejection_reason: string | null;
  officer?: Officer;
}

export interface SubmitPassRequest {
  start_date: string;
  end_date: string;
  reason: string;
}

export interface PassEligibility {
  eligible: boolean;
  annual_leave_exhausted: boolean;
  annual_leave_count: number;
  annual_leave_required: number;
  passes_used_this_year: number;
  passes_remaining: number;
  max_passes_per_year: number;
  max_days_per_pass: number;
}
```

---

## 10. Client-Side Validation (Before API Call)

```typescript
// src/features/pass/utils/validation.ts
export function validatePassForm(values: SubmitPassRequest): Record<string, string> {
  const errors: Record<string, string> = {};

  const start = new Date(values.start_date);
  const end = new Date(values.end_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (!values.start_date) errors.start_date = 'Start date is required';
  if (!values.end_date) errors.end_date = 'End date is required';
  if (!values.reason?.trim()) errors.reason = 'Reason is required';

  if (start < today) errors.start_date = 'Start date must be today or later';
  if (end <= start) errors.end_date = 'End date must be after start date';

  const diffDays = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1;
  if (diffDays > 5) errors.end_date = 'Pass cannot exceed 5 days';

  return errors;
}
```

---

## 11. Edge Cases & Error Handling

| Edge Case | Handling |
|-----------|----------|
| Officer has no officer record | Show error: "Officer record not found. Contact HR" |
| Annual leave not exhausted | Show banner: "You must exhaust annual leave first" + disable submit |
| Max 2 passes reached | Show banner: "Maximum 2 passes this year reached" + disable submit |
| Pass > 5 days | Client-side date validation + server rejection |
| Staff Officer minutes application from different command | API returns 403 |
| DC Admin tries to approve un-minuted application | API returns error: "Not minuted yet" |
| Network failure during submission | Show retry dialog, keep form data |
| Double submission | Disable submit button after first tap |

---

## 12. Testing Checklist

- [ ] Submit pass with valid data → PENDING status
- [ ] Submit pass > 5 days → Error message
- [ ] Submit pass when annual leave not exhausted → Error message
- [ ] Submit 3rd pass in a year → Error message
- [ ] Staff Officer views command passes
- [ ] Staff Officer minutes pending pass
- [ ] Staff Officer tries to minute already-minuted pass → Error
- [ ] DC Admin approves minuted pass → APPROVED + notification
- [ ] DC Admin rejects minuted pass with reason → REJECTED + notification
- [ ] DC Admin tries to approve un-minuted pass → Error
- [ ] Officer views own pass detail with timeline
- [ ] Area Controller views all passes (read-only)
- [ ] Push notification received on approval
- [ ] Push notification received on rejection
- [ ] Eligibility check endpoint returns correct data
