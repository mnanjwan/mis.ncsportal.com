# Feature 09: My Reports

> **Source studied:** Report generation across all modules, Officer model relationships, print routes from `web.php`

---

## 1. Feature Overview

**My Reports** provides officers with downloadable/viewable reports about their own records. Unlike approvals (which are for action), reports are **read-only document views** that officers can generate, view, and share. On mobile, reports are rendered as **in-app views** with an option to download as PDF.

---

## 2. Available Reports

| Report | Description | Data Source |
|--------|------------|------------|
| **Service Record** | Complete career history | Officer model + postings + promotions |
| **Leave History** | All leave applications & balances | LeaveApplication model |
| **Pass History** | All pass applications | PassApplication model |
| **Emolument History** | All emoluments with status | Emolument model |
| **Posting History** | All posting orders | Posting model |
| **Course History** | Training courses attended | Course model |
| **Duty Roster** | Current duty assignments | DutyRoster model |
| **Internal Staff Orders** | ISO documents affecting officer | InternalStaffOrder model |
| **Quarter Allocation** | Current/past quarters | Quarter model |
| **Query History** | All queries received/responded | Query model |

---

## 3. API Endpoints

```
GET    /api/v1/reports/service-record          → Complete service record
GET    /api/v1/reports/leave-history            → Leave applications history
GET    /api/v1/reports/pass-history             → Pass applications history
GET    /api/v1/reports/emolument-history        → Emolument payment history
GET    /api/v1/reports/posting-history          → Posting orders
GET    /api/v1/reports/course-history           → Courses attended
GET    /api/v1/reports/duty-roster              → Current duty assignments
GET    /api/v1/reports/quarter-history          → Quarter allocations
GET    /api/v1/reports/query-history            → Query records
GET    /api/v1/reports/{type}/download          → Download as PDF
```

### `GET /api/v1/reports/service-record` Response

```json
{
  "status": "success",
  "data": {
    "officer": {
      "service_number": "NCS/12345",
      "full_name": "A.B. Smith",
      "rank": "ASC II",
      "date_of_first_appointment": "2010-03-01",
      "current_station": "Lagos Command"
    },
    "promotions": [
      { "from_rank": "AI", "to_rank": "Insp", "effective_date": "2015-01-01" },
      { "from_rank": "Insp", "to_rank": "ASC II", "effective_date": "2020-01-01" }
    ],
    "postings": [
      { "command": "Abuja Command", "from": "2010-03-01", "to": "2015-06-30" },
      { "command": "Lagos Command", "from": "2015-07-01", "to": null }
    ],
    "courses": [
      { "name": "Basic Training", "year": 2010, "result": "Passed" }
    ],
    "leave_summary": {
      "total_leave_days": 120,
      "total_pass_days": 15,
      "current_year_leave": 15
    }
  }
}
```

---

## 4. Mobile Screens

### Screen 4.1: Reports Menu

```
┌─────────────────────────────────────┐
│  📊 My Reports                      │
│  ─────────────────────────────────  │
│                                     │
│  [📋 Service Record              →]│
│  [🏖️ Leave History               →]│
│  [🎫 Pass History                →]│
│  [💰 Emolument History           →]│
│  [📮 Posting History             →]│
│  [📚 Course History              →]│
│  [📋 Duty Roster                 →]│
│  [🏠 Quarter History             →]│
│  [❓ Query History               →]│
│                                     │
└─────────────────────────────────────┘
```

### Screen 4.2: Report View

```
┌─────────────────────────────────────┐
│  ← Leave History        [📥 PDF]   │
│  ─────────────────────────────────  │
│                                     │
│  Summary: 120 leave days used       │
│  Current Year: 15/30 days used      │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Annual Leave · ✅ APPROVED  │   │
│  │ 01/03/2026 – 15/03/2026    │   │
│  │ 15 days                     │   │
│  └─────────────────────────────┘   │
│  ┌─────────────────────────────┐   │
│  │ Sick Leave · ✅ APPROVED    │   │
│  │ 05/01/2026 – 08/01/2026    │   │
│  │ 4 days                      │   │
│  └─────────────────────────────┘   │
│  ...more items...                   │
└─────────────────────────────────────┘
```

---

## 5. React Native Structure

```
src/features/reports/
├── screens/
│   ├── ReportsMenuScreen.tsx
│   ├── ServiceRecordScreen.tsx
│   ├── LeaveHistoryScreen.tsx
│   ├── PassHistoryScreen.tsx
│   ├── EmolumentHistoryScreen.tsx
│   ├── PostingHistoryScreen.tsx
│   └── CourseHistoryScreen.tsx
├── components/
│   ├── ReportMenuItem.tsx
│   ├── ReportHeader.tsx
│   ├── HistoryCard.tsx
│   └── PdfDownloadButton.tsx
├── api/
│   └── reportsApi.ts
└── types/
    └── reports.ts
```

---

## 6. Testing Checklist

- [ ] Service record loads with complete career data
- [ ] Leave history shows all applications with status
- [ ] Pass history shows all passes
- [ ] Emolument history with step progression
- [ ] PDF download works for each report type
- [ ] Reports display correctly offline (cached)
- [ ] Empty state when no data exists
