# Feature 03: Raise Emolument

> **Source studied:** `EmolumentController.php` (1,584 lines), `Emolument.php` model (67 lines), `EmolumentTimeline.php`, `EmolumentAssessment.php`, `EmolumentValidation.php`, `EmolumentAudit.php`, `raise.blade.php` (334 lines), `NotificationService.php` (8 emolument notification methods)

---

## 1. Feature Overview

**Emolument** is the most critical feature in the mobile app. It handles the annual salary/remuneration processing for officers. The web currently requires officers to contact their assessor manually with bank details — the mobile app **eliminates this by letting officers raise emoluments directly**, which then flow through a **6-step approval chain**:

```
Officer → Assessor → Validator/Area Controller → Auditor → Accounts
```

Each step can **approve** (advance to next step) or **reject** (back to officer with comments).

---

## 2. Data Models

### `emoluments` Table

```
┌──────────────────────┬────────────┬──────────────────────────────────────────┐
│ Column               │ Type       │ Notes                                    │
├──────────────────────┼────────────┼──────────────────────────────────────────┤
│ id                   │ bigint PK  │                                          │
│ officer_id           │ bigint FK  │ → officers.id                            │
│ timeline_id          │ bigint FK  │ → emolument_timelines.id                 │
│ year                 │ integer    │ Copied from timeline                     │
│ bank_name            │ string     │ Officer's bank name                      │
│ bank_account_number  │ string     │ Officer's account number                 │
│ pfa_name             │ string     │ Pension Fund Administrator name          │
│ rsa_pin              │ string     │ Retirement Savings Account PIN           │
│ notes                │ text       │ Optional additional notes                │
│ status               │ string     │ RAISED/ASSESSED/VALIDATED/AUDITED/PROCESSED/REJECTED │
│ submitted_at         │ timestamp  │ When officer raised it                   │
│ assessed_at          │ timestamp  │ When assessor processed it               │
│ validated_at         │ timestamp  │ When validator processed it              │
│ audited_at           │ timestamp  │ When auditor processed it                │
│ processed_at         │ timestamp  │ When accounts processed payment          │
│ created_at           │ timestamp  │                                          │
│ updated_at           │ timestamp  │                                          │
└──────────────────────┴────────────┴──────────────────────────────────────────┘
```

### `emolument_timelines` Table

```
┌──────────────┬──────────┬──────────────────────────────────┐
│ Column       │ Type     │ Notes                            │
├──────────────┼──────────┼──────────────────────────────────┤
│ id           │ bigint   │                                  │
│ year         │ integer  │ e.g. 2026                        │
│ start_date   │ date     │ Timeline open date               │
│ end_date     │ date     │ Timeline close date              │
│ is_active    │ boolean  │ Can officers submit?             │
│ created_at   │ timestamp│                                  │
└──────────────┴──────────┴──────────────────────────────────┘
```

### `emolument_assessments` Table

```
┌──────────────────┬──────────┬─────────────────────────┐
│ Column           │ Type     │ Notes                   │
├──────────────────┼──────────┼─────────────────────────┤
│ id               │ bigint   │                         │
│ emolument_id     │ bigint FK│ → emoluments.id         │
│ assessor_id      │ bigint FK│ → users.id (Assessor)   │
│ assessment_status│ string   │ APPROVED / REJECTED     │
│ comments         │ text     │ Required if REJECTED    │
│ created_at       │ timestamp│                         │
└──────────────────┴──────────┴─────────────────────────┘
```

### `emolument_validations` Table

```
┌──────────────────┬──────────┬─────────────────────────────┐
│ Column           │ Type     │ Notes                       │
├──────────────────┼──────────┼─────────────────────────────┤
│ id               │ bigint   │                             │
│ emolument_id     │ bigint FK│ → emoluments.id             │
│ assessment_id    │ bigint FK│ → emolument_assessments.id  │
│ validator_id     │ bigint FK│ → users.id (Validator / AC) │
│ validation_status│ string   │ APPROVED / REJECTED         │
│ comments         │ text     │ Required if REJECTED        │
│ created_at       │ timestamp│                             │
└──────────────────┴──────────┴─────────────────────────────┘
```

### `emolument_audits` Table

```
┌──────────────────┬──────────┬──────────────────────────────┐
│ Column           │ Type     │ Notes                        │
├──────────────────┼──────────┼──────────────────────────────┤
│ id               │ bigint   │                              │
│ emolument_id     │ bigint FK│ → emoluments.id              │
│ validation_id    │ bigint FK│ → emolument_validations.id   │
│ auditor_id       │ bigint FK│ → users.id (Auditor)         │
│ audit_status     │ string   │ APPROVED / REJECTED          │
│ comments         │ text     │ Required if REJECTED         │
│ created_at       │ timestamp│                              │
└──────────────────┴──────────┴──────────────────────────────┘
```

---

## 3. Business Rules (from Controller)

| Rule | Source | Implementation |
|------|--------|---------------|
| Timeline must be **active** (`is_active = true`) | Store line 374-377 | Only show active timelines |
| **One emolument per timeline** per officer | Store line 380-386 | Server check for duplicates |
| Bank details are **pre-filled** from officer profile | Blade: `readonly`, `bg-gray-100` | Read-only fields in form |
| Comments are **required when rejecting** at any step | Validation at each step | Make rejection reason mandatory |
| Assessor can only see officers **from their command** | Index line 40-42 | API filters by command |
| Validator can only process officers **from their command** | processValidation line 668-676 | API enforces command scope |
| Area Controller can validate **any** emolument | validateForm line 599 | No command restriction |
| Auditor processes **all validated** emoluments | processAudit — no command filter | Global scope |
| Accounts processes **all audited** emoluments | validated method after audit | Global scope |
| Rejected emolument can be **resubmitted** | Re-validation logic lines 717-729 | Officer resubmits, old records deleted |

---

## 4. Workflow — 6-Step Approval Chain

```
┌──────────┐     ┌──────────┐     ┌──────────────┐     ┌──────────┐     ┌──────────┐
│ RAISED   │────▶│ ASSESSED │────▶│  VALIDATED   │────▶│ AUDITED  │────▶│PROCESSED │
│ (Officer)│     │(Assessor)│     │(Validator/AC)│     │(Auditor) │     │(Accounts)│
└──────┬───┘     └──────┬───┘     └──────┬───────┘     └──────┬───┘     └──────────┘
       │                │                │                    │
       │         ┌──────▼───┐     ┌──────▼───────┐     ┌─────▼────┐
       │         │ REJECTED │     │  REJECTED    │     │ REJECTED │
       │◀────────│ (back to │◀────│  (back to    │◀────│ (back to │
       │         │ officer) │     │  officer)    │     │ officer) │
       │         └──────────┘     └──────────────┘     └──────────┘
       │
       │  (Officer can RESUBMIT rejected emolument)
       └──────────────────────────────────────────▶
```

### Status Transitions

| Step | From | To (Approved) | To (Rejected) | Action By | Scope |
|------|------|---------------|---------------|-----------|-------|
| 1 | — | RAISED | — | Officer | Own emolument |
| 2 | RAISED | ASSESSED | REJECTED | Assessor | Same command |
| 3 | ASSESSED | VALIDATED | REJECTED | Validator / Area Controller | Command / Global |
| 4 | VALIDATED | AUDITED | REJECTED | Auditor | Global |
| 5 | AUDITED | PROCESSED | — | Accounts | Global |

---

## 5. Role-Based Access Matrix

| Role | Can View | Can Act | Scope |
|------|----------|---------|-------|
| **Officer** | Own emoluments (all statuses) | Raise new, Resubmit rejected | Own only |
| **Assessor** | RAISED emoluments from command | Assess (approve/reject) | Same command |
| **Validator** | ASSESSED emoluments from command | Validate (approve/reject) | Same command |
| **Area Controller** | All ASSESSED emoluments | Validate (approve/reject) | Global |
| **Auditor** | All VALIDATED emoluments | Audit (approve/reject) | Global |
| **Accounts** | All AUDITED emoluments | Process payment | Global |
| **HRD** | All emoluments | View only | Global |

---

## 6. API Endpoints Required

### New API Endpoints

```
GET    /api/v1/emoluments                         → Officer's own emoluments
GET    /api/v1/emoluments/{id}                    → Emolument detail (all relationships)
POST   /api/v1/emoluments                         → Raise emolument
POST   /api/v1/emoluments/{id}/resubmit           → Resubmit rejected emolument
GET    /api/v1/emolument-timelines                → Active timelines
GET    /api/v1/emoluments/stats                   → Officer's emolument statistics

# Assessor endpoints
GET    /api/v1/assessor/emoluments                → RAISED emoluments in assessor's command
POST   /api/v1/assessor/emoluments/{id}/assess    → Assess (approve/reject)

# Validator endpoints
GET    /api/v1/validator/emoluments               → ASSESSED emoluments in validator's command
POST   /api/v1/validator/emoluments/{id}/validate → Validate (approve/reject)

# Area Controller endpoints (validation)
GET    /api/v1/area-controller/emoluments         → All ASSESSED emoluments
POST   /api/v1/area-controller/emoluments/{id}/validate → Area Controller validates

# Auditor endpoints
GET    /api/v1/auditor/emoluments                 → All VALIDATED emoluments
POST   /api/v1/auditor/emoluments/{id}/audit      → Audit (approve/reject)

# Accounts endpoints
GET    /api/v1/accounts/emoluments                → All AUDITED emoluments
POST   /api/v1/accounts/emoluments/{id}/process   → Process payment
```

### API Request/Response Specs

#### `POST /api/v1/emoluments` — Raise Emolument

**Request Body:**
```json
{
  "timeline_id": 3,
  "bank_name": "First Bank",
  "bank_account_number": "0123456789",
  "pfa_name": "ARM Pension",
  "rsa_pin": "PEN100012345678",
  "notes": "Optional notes"
}
```

**Validation:**
```php
'timeline_id'         => 'required|exists:emolument_timelines,id',
'bank_name'           => 'required|string|max:255',
'bank_account_number' => 'required|string|max:50',
'pfa_name'            => 'required|string|max:255',
'rsa_pin'             => 'required|string|max:50',
'notes'               => 'nullable|string'
```

**Business Logic:**
1. Officer record must exist
2. Timeline must be active
3. No existing emolument for this officer + timeline combination

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Emolument raised successfully",
  "data": {
    "id": 156,
    "officer_id": 15,
    "timeline_id": 3,
    "year": 2026,
    "bank_name": "First Bank",
    "bank_account_number": "0123456789",
    "pfa_name": "ARM Pension",
    "rsa_pin": "PEN100012345678",
    "status": "RAISED",
    "submitted_at": "2026-02-24T17:30:00Z"
  }
}
```

#### `POST /api/v1/assessor/emoluments/{id}/assess` — Assess

**Request Body:**
```json
{
  "assessment_status": "APPROVED",
  "comments": "Officer details verified against records"
}
```

**Validation:**
```php
'assessment_status' => 'required|in:APPROVED,REJECTED',
'comments'          => 'nullable|string' // REQUIRED if REJECTED
```

**Business Logic:**
- Emolument must be in RAISED status
- Assessor must be from same command as officer

#### `POST /api/v1/validator/emoluments/{id}/validate` — Validate

**Request Body:**
```json
{
  "validation_status": "APPROVED",
  "comments": "Assessment verified, bank details confirmed"
}
```

**Validation:**
```php
'validation_status' => 'required|in:APPROVED,REJECTED',
'comments'          => 'nullable|string|max:1000' // REQUIRED if REJECTED
```

#### `POST /api/v1/auditor/emoluments/{id}/audit` — Audit

**Request Body:**
```json
{
  "audit_status": "APPROVED",
  "comments": "All records in order"
}
```

**Validation:**
```php
'audit_status' => 'required|in:APPROVED,REJECTED',
'comments'     => 'nullable|string|max:1000' // REQUIRED if REJECTED
```

#### `GET /api/v1/emoluments/{id}` — Detail with All Relationships

**Response:**
```json
{
  "status": "success",
  "data": {
    "id": 156,
    "officer": {
      "id": 15,
      "service_number": "NCS/12345",
      "surname": "Smith",
      "initials": "A.B.",
      "substantive_rank": "ASC II",
      "present_station": { "id": 5, "name": "Lagos Command" }
    },
    "timeline": { "id": 3, "year": 2026, "start_date": "2026-01-01", "end_date": "2026-12-31" },
    "year": 2026,
    "bank_name": "First Bank",
    "bank_account_number": "0123456789",
    "pfa_name": "ARM Pension",
    "rsa_pin": "PEN100012345678",
    "status": "VALIDATED",
    "submitted_at": "2026-02-24T17:30:00Z",
    "assessed_at": "2026-02-25T10:00:00Z",
    "validated_at": "2026-02-26T14:00:00Z",
    "audited_at": null,
    "processed_at": null,
    "assessment": {
      "id": 50,
      "assessor": { "id": 8, "name": "Assessor User" },
      "assessment_status": "APPROVED",
      "comments": "Verified",
      "created_at": "2026-02-25T10:00:00Z"
    },
    "validation": {
      "id": 30,
      "validator": { "id": 12, "name": "Validator User" },
      "validation_status": "APPROVED",
      "comments": "Bank confirmed",
      "created_at": "2026-02-26T14:00:00Z"
    },
    "audit": null
  }
}
```

---

## 7. Notifications Triggered (8 Types)

| Step | Event | Method | Recipients | Push |
|------|-------|--------|-----------|------|
| 1 | Officer raises | `notifyEmolumentRaised()` | Assessors (same command) | ✅ |
| 2a | Assessor approves/rejects | `notifyEmolumentAssessed()` | Officer | ✅ |
| 2b | Assessor approves | `notifyEmolumentAssessedReadyForValidation()` | Validators + Area Controllers | ✅ |
| 3a | Validator approves/rejects | `notifyEmolumentValidated()` | Officer | ✅ |
| 3b | Validator approves | `notifyEmolumentValidatedReadyForAudit()` | Auditors | ✅ |
| 4a | Auditor approves/rejects | `notifyEmolumentAudited()` | Officer | ✅ |
| 4b | Auditor approves | `notifyEmolumentAuditedReadyForProcessing()` | Accounts team | ✅ |
| 5 | Accounts processes | `notifyEmolumentProcessed()` | Officer | ✅ |

---

## 8. Mobile Screens

### Screen 8.1: My Emoluments (Officer)

```
┌─────────────────────────────────────┐
│  My Emoluments                      │
│  ─────────────────────────────────  │
│                                     │
│  ┌─ Statistics ──────────────────┐ │
│  │ Raised: 1  Assessed: 0       │ │
│  │ Validated: 1  Audited: 0     │ │
│  │ Processed: 2                  │ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 📄 Emolument 2026           │   │
│  │ Timeline: Jan – Dec 2026    │   │
│  │ Bank: First Bank · ****6789 │   │
│  │ Status: 🔵 VALIDATED        │   │
│  │ ████████████░░░░ Step 3/5   │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 📄 Emolument 2025           │   │
│  │ Timeline: Jan – Dec 2025    │   │
│  │ Bank: First Bank · ****6789 │   │
│  │ Status: ✅ PROCESSED        │   │
│  │ ████████████████ Step 5/5   │   │
│  └─────────────────────────────┘   │
│                                     │
│        [+ Raise Emolument]          │
└─────────────────────────────────────┘
```

### Screen 8.2: Raise Emolument Form

```
┌─────────────────────────────────────┐
│  ← Raise Emolument                  │
│  ─────────────────────────────────  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Emolument Timeline          │   │
│  │ [🔽 2026 (Jan - Dec 2026)] │   │
│  │ (auto-selected if only 1)  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─ Bank Information ───────────┐  │
│  │ Bank Name (from profile)     │  │
│  │ 🔒 [First Bank         ]    │  │
│  │                              │  │
│  │ Account Number               │  │
│  │ 🔒 [0123456789         ]    │  │
│  └──────────────────────────────┘  │
│                                     │
│  ┌─ Pension Information ────────┐  │
│  │ PFA Name (from profile)      │  │
│  │ 🔒 [ARM Pension        ]    │  │
│  │                              │  │
│  │ RSA PIN                      │  │
│  │ 🔒 [PEN100012345678   ]    │  │
│  └──────────────────────────────┘  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Additional Notes (optional) │   │
│  │ [                        ]  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ⚠️ Bank & PFA details are pulled  │
│  from your profile. Update your     │
│  profile first if they're wrong.    │
│                                     │
│  [Cancel]        [Submit Emolument] │
└─────────────────────────────────────┘
```

### Screen 8.3: Emolument Detail (Officer — read-only tracking)

```
┌─────────────────────────────────────┐
│  ← Emolument 2026                  │
│  ─────────────────────────────────  │
│                                     │
│  Status: 🔵 VALIDATED (Step 3/5)   │
│                                     │
│  ┌─ Details ─────────────────────┐ │
│  │ Year:     2026                │ │
│  │ Bank:     First Bank          │ │
│  │ Account:  ****6789            │ │
│  │ PFA:      ARM Pension         │ │
│  │ RSA PIN:  PEN10001****        │ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌─ Approval Timeline ──────────┐ │
│  │                               │ │
│  │ ✅ Step 1: RAISED             │ │
│  │    24 Feb 2026, 17:30         │ │
│  │    You submitted this         │ │
│  │    │                          │ │
│  │ ✅ Step 2: ASSESSED           │ │
│  │    25 Feb 2026, 10:00         │ │
│  │    Approved by Assessor       │ │
│  │    "Officer details verified" │ │
│  │    │                          │ │
│  │ ✅ Step 3: VALIDATED          │ │
│  │    26 Feb 2026, 14:00         │ │
│  │    Approved by Validator      │ │
│  │    "Bank confirmed"           │ │
│  │    │                          │ │
│  │ ⏳ Step 4: AUDITED            │ │
│  │    Pending...                 │ │
│  │    │                          │ │
│  │ ○  Step 5: PROCESSED          │ │
│  │    Not yet reached            │ │
│  │                               │ │
│  └───────────────────────────────┘ │
└─────────────────────────────────────┘
```

### Screen 8.4: Assessor Review Screen

```
┌─────────────────────────────────────┐
│  ← Assess Emolument                │
│  ─────────────────────────────────  │
│                                     │
│  Officer: ASC II A.B. Smith         │
│  S/N:     NCS/12345                 │
│  Command: Lagos Command             │
│  Year:    2026                       │
│                                     │
│  ┌─ Bank Details ────────────────┐ │
│  │ Bank:    First Bank           │ │
│  │ Account: 0123456789           │ │
│  │ PFA:     ARM Pension          │ │
│  │ RSA PIN: PEN100012345678      │ │
│  └───────────────────────────────┘ │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Comments                    │   │
│  │ [                        ]  │   │
│  │ (Required if rejecting)     │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌────────────┐  ┌────────────┐    │
│  │ Approve ✓  │  │  Reject ✗  │    │
│  └────────────┘  └────────────┘    │
└─────────────────────────────────────┘
```

> **Validator, Auditor, and Accounts** screens follow the same pattern with increasing detail (showing previous step results).

---

## 9. React Native Implementation

### Component Structure
```
src/features/emolument/
├── screens/
│   ├── EmolumentListScreen.tsx        → My emoluments + stats
│   ├── EmolumentRaiseScreen.tsx       → Raise emolument form
│   ├── EmolumentDetailScreen.tsx      → Detail + approval timeline
│   ├── AssessorListScreen.tsx         → List RAISED in command
│   ├── AssessorReviewScreen.tsx       → Assess approve/reject
│   ├── ValidatorListScreen.tsx        → List ASSESSED in command
│   ├── ValidatorReviewScreen.tsx      → Validate approve/reject
│   ├── AuditorListScreen.tsx          → List VALIDATED (global)
│   ├── AuditorReviewScreen.tsx        → Audit approve/reject
│   └── AccountsListScreen.tsx         → List AUDITED (global)
├── components/
│   ├── EmolumentCard.tsx              → Summary card with progress bar
│   ├── EmolumentStatusBadge.tsx       → Color-coded status
│   ├── EmolumentProgressBar.tsx       → 5-step progress indicator
│   ├── EmolumentTimeline.tsx          → Vertical approval timeline
│   ├── EmolumentStats.tsx             → Stats dashboard cards
│   ├── BankInfoCard.tsx               → Read-only bank details display
│   └── ApprovalActionSheet.tsx        → Approve/Reject with comments
├── hooks/
│   ├── useEmoluments.ts
│   ├── useEmolumentTimelines.ts
│   └── useEmolumentStats.ts
├── api/
│   └── emolumentApi.ts
└── types/
    └── emolument.ts
```

### TypeScript Types
```typescript
export type EmolumentStatus = 'RAISED' | 'ASSESSED' | 'VALIDATED' | 'AUDITED' | 'PROCESSED' | 'REJECTED';

export interface Emolument {
  id: number;
  officer_id: number;
  timeline_id: number;
  year: number;
  bank_name: string;
  bank_account_number: string;
  pfa_name: string;
  rsa_pin: string;
  notes: string | null;
  status: EmolumentStatus;
  submitted_at: string;
  assessed_at: string | null;
  validated_at: string | null;
  audited_at: string | null;
  processed_at: string | null;
  officer?: Officer;
  timeline?: EmolumentTimeline;
  assessment?: EmolumentAssessment;
  validation?: EmolumentValidation;
  audit?: EmolumentAudit;
}

export interface EmolumentTimeline {
  id: number;
  year: number;
  start_date: string;
  end_date: string;
  is_active: boolean;
}

export interface EmolumentAssessment {
  id: number;
  assessor: { id: number; name: string };
  assessment_status: 'APPROVED' | 'REJECTED';
  comments: string | null;
  created_at: string;
}

export interface EmolumentValidation {
  id: number;
  validator: { id: number; name: string };
  validation_status: 'APPROVED' | 'REJECTED';
  comments: string | null;
  created_at: string;
}

export interface EmolumentAudit {
  id: number;
  auditor: { id: number; name: string };
  audit_status: 'APPROVED' | 'REJECTED';
  comments: string | null;
  created_at: string;
}

export interface EmolumentStats {
  raised: number;
  assessed: number;
  validated: number;
  audited: number;
  processed: number;
}

// Helper: Map status to step number
export const STATUS_STEP_MAP: Record<EmolumentStatus, number> = {
  RAISED: 1,
  ASSESSED: 2,
  VALIDATED: 3,
  AUDITED: 4,
  PROCESSED: 5,
  REJECTED: -1,
};
```

### RTK Query API Slice
```typescript
export const emolumentApi = createApi({
  reducerPath: 'emolumentApi',
  tagTypes: ['Emolument', 'EmolumentTimeline'],
  endpoints: (builder) => ({
    getMyEmoluments: builder.query<Emolument[], void>({
      query: () => '/emoluments',
      providesTags: ['Emolument'],
    }),
    getEmolumentById: builder.query<Emolument, number>({
      query: (id) => `/emoluments/${id}`,
      providesTags: (result, error, id) => [{ type: 'Emolument', id }],
    }),
    getActiveTimelines: builder.query<EmolumentTimeline[], void>({
      query: () => '/emolument-timelines',
      providesTags: ['EmolumentTimeline'],
    }),
    raiseEmolument: builder.mutation<Emolument, RaiseEmolumentRequest>({
      query: (body) => ({ url: '/emoluments', method: 'POST', body }),
      invalidatesTags: ['Emolument'],
    }),
    assessEmolument: builder.mutation<void, AssessmentRequest>({
      query: ({ id, ...body }) => ({
        url: `/assessor/emoluments/${id}/assess`,
        method: 'POST',
        body,
      }),
      invalidatesTags: ['Emolument'],
    }),
    validateEmolument: builder.mutation<void, ValidationRequest>({
      query: ({ id, ...body }) => ({
        url: `/validator/emoluments/${id}/validate`,
        method: 'POST',
        body,
      }),
      invalidatesTags: ['Emolument'],
    }),
    auditEmolument: builder.mutation<void, AuditRequest>({
      query: ({ id, ...body }) => ({
        url: `/auditor/emoluments/${id}/audit`,
        method: 'POST',
        body,
      }),
      invalidatesTags: ['Emolument'],
    }),
    processEmolument: builder.mutation<void, number>({
      query: (id) => ({
        url: `/accounts/emoluments/${id}/process`,
        method: 'POST',
      }),
      invalidatesTags: ['Emolument'],
    }),
  }),
});
```

---

## 10. Key UX Considerations

| Consideration | Implementation |
|---------------|---------------|
| **Bank details are read-only** | Pre-filled from officer profile, cannot edit in emolument form |
| **Profile must be updated first** | If bank/PFA details are empty, show "Update your profile first" CTA |
| **Progress bar is critical** | 5-step visual indicator showing current position |
| **Rejection shows comments** | When REJECTED status, show rejection reason prominently with "Resubmit" button |
| **Real-time status updates** | Push notifications for every step change |
| **Masking sensitive data** | Mask account numbers (****6789) and RSA PINs in list views |

---

## 11. Edge Cases

| Edge Case | Handling |
|-----------|----------|
| No active timelines | Show "No active timelines available" + disable form |
| Already submitted for timeline | Show "Already submitted" badge on timeline |
| Officer has no bank details | Show "Update your profile first" with link to profile |
| Rejected emolument — resubmit | Show "Resubmit" button, clears previous rejection records |
| Assessor views officer from different command | API returns 403 |
| Double assessment attempt | API returns error: "Already assessed" |
| Missing assessment/validation records (data inconsistency) | Backend auto-creates retroactively |
| Concurrent approval | Optimistic locking — server rejects if status changed |

---

## 12. Testing Checklist

- [ ] Raise emolument with valid data → RAISED
- [ ] Raise second emolument for same timeline → Error
- [ ] Verify bank/PFA pre-filled from profile
- [ ] Assessor views only their command's emoluments
- [ ] Assessor approves → ASSESSED + officer notified + validators notified
- [ ] Assessor rejects without comments → Error
- [ ] Assessor rejects with comments → REJECTED + officer notified
- [ ] Validator approves → VALIDATED + officer notified + auditors notified
- [ ] Validator rejects → REJECTED + officer notified
- [ ] Area Controller validates (no command restriction)
- [ ] Auditor approves → AUDITED + officer notified + accounts notified
- [ ] Auditor rejects → REJECTED + officer notified
- [ ] Accounts processes payment → PROCESSED + officer notified
- [ ] Officer views 5-step progress timeline
- [ ] Rejected emolument shows rejection reason + resubmit button
- [ ] Push notification at every step change
- [ ] Stats dashboard shows correct counts per status
