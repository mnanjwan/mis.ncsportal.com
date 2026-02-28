# Feature 08: Requests & Approvals Dashboard

> **Source studied:** All approval workflows from Pass, Leave, Emolument, Manning, Quarters, Fleet, Pharmacy modules

---

## 1. Feature Overview

The **Requests & Approvals Dashboard** is a **unified inbox** that aggregates all pending actions across every module. Instead of navigating to each feature separately, approvers see a single screen of everything that needs their attention. This is **the most important screen for Staff Officers, DC Admins, Assessors, Validators, Auditors, and other approval roles**.

---

## 2. Aggregated Request Types

| Module | Request Type | Approver Roles |
|--------|-------------|---------------|
| Pass | Pass applications | Staff Officer (minute), DC Admin (approve/reject) |
| Leave | Leave applications | Staff Officer (minute), DC Admin (approve/reject) |
| Emolument | Emolument processing | Assessor, Validator, Area Controller, Auditor, Accounts |
| Manning | Manning requests | HRD, CGC, Area Controller |
| Quarters | Quarter allocation requests | Quarters Officer, Area Controller |
| Fleet | Fleet requests | CGC, DCG FATS, ACG TS |
| Pharmacy | Pharmacy requisitions | Pharmacy Officer, Medical Officer |
| Postings | Posting orders | HRD, CGC |
| Queries | Query responses | Staff Officer, DC Admin |

---

## 3. API Endpoints

```
GET    /api/v1/approvals/dashboard         → Aggregated counts per module
GET    /api/v1/approvals/pending           → All pending items (paginated, filterable)
GET    /api/v1/approvals/history           → Completed approvals (paginated)
GET    /api/v1/approvals/stats             → Personal approval statistics
```

### `GET /api/v1/approvals/dashboard` Response

```json
{
  "status": "success",
  "data": {
    "total_pending": 12,
    "by_module": {
      "pass": { "pending": 3, "label": "Pass Applications" },
      "leave": { "pending": 2, "label": "Leave Applications" },
      "emolument": { "pending": 4, "label": "Emoluments" },
      "manning": { "pending": 1, "label": "Manning Requests" },
      "quarters": { "pending": 0, "label": "Quarter Requests" },
      "fleet": { "pending": 2, "label": "Fleet Requests" },
      "pharmacy": { "pending": 0, "label": "Pharmacy Requisitions" }
    },
    "recent_actions": [
      {
        "type": "leave_approved",
        "summary": "Approved leave for ASC II A.B. Smith",
        "timestamp": "2026-02-24T15:30:00Z"
      }
    ]
  }
}
```

### `GET /api/v1/approvals/pending` Response

```json
{
  "status": "success",
  "data": [
    {
      "id": 42,
      "module": "pass",
      "type": "pass_application",
      "action_required": "minute",
      "officer": { "name": "A.B. Smith", "rank": "ASC II", "service_number": "NCS/12345" },
      "summary": "Pass: 01 Mar – 04 Mar 2026 (4 days)",
      "submitted_at": "2026-02-24T14:00:00Z",
      "priority": "normal",
      "deep_link": "ncsapp://pass/42"
    },
    {
      "id": 156,
      "module": "emolument",
      "type": "emolument",
      "action_required": "assess",
      "officer": { "name": "C.D. Johnson", "rank": "Insp", "service_number": "NCS/67890" },
      "summary": "Emolument 2026 – First Bank ****6789",
      "submitted_at": "2026-02-23T10:00:00Z",
      "priority": "high",
      "deep_link": "ncsapp://emolument/156"
    }
  ],
  "pagination": { "current_page": 1, "total": 12, "per_page": 20 }
}
```

---

## 4. Mobile Screens

### Screen 4.1: Approvals Dashboard

```
┌─────────────────────────────────────┐
│  📋 Approvals                       │
│  ─────────────────────────────────  │
│                                     │
│  Total Pending: 12                  │
│                                     │
│  ┌──────┐ ┌──────┐ ┌──────┐       │
│  │Pass  │ │Leave │ │Emol. │       │
│  │  3   │ │  2   │ │  4   │       │
│  └──────┘ └──────┘ └──────┘       │
│  ┌──────┐ ┌──────┐ ┌──────┐       │
│  │Fleet │ │Mann. │ │Pharm.│       │
│  │  2   │ │  1   │ │  0   │       │
│  └──────┘ └──────┘ └──────┘       │
│                                     │
│  ── PENDING ACTIONS ──              │
│  [All] [Pass] [Leave] [Emolument]   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 🎫 Pass Application        │   │
│  │ ASC II A.B. Smith           │   │
│  │ 01-04 Mar · 4 days         │   │
│  │ Action: [Minute →]          │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 💰 Emolument (2026)        │   │
│  │ Insp C.D. Johnson           │   │
│  │ First Bank ****6789         │   │
│  │ Action: [Assess →]          │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

---

## 5. React Native Structure

```
src/features/approvals/
├── screens/
│   ├── ApprovalsDashboardScreen.tsx
│   ├── PendingListScreen.tsx
│   └── ApprovalHistoryScreen.tsx
├── components/
│   ├── ModuleCountCard.tsx
│   ├── PendingActionCard.tsx
│   ├── ApprovalFilterBar.tsx
│   └── QuickActionButton.tsx
├── api/
│   └── approvalsApi.ts
└── types/
    └── approvals.ts
```

---

## 6. Testing Checklist

- [ ] Dashboard shows correct counts per module
- [ ] Pending list shows all pending items across modules
- [ ] Filter by module type works
- [ ] Tapping item navigates to correct feature detail
- [ ] Quick actions work (minute, approve, reject)
- [ ] History shows completed approvals
- [ ] Badge count on tab bar matches total pending
- [ ] Counts update after an action (real-time)
