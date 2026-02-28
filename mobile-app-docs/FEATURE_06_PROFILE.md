# Feature 06: My Profile

> **Source studied:** `Officer.php` model (480 lines, 68 fillable fields), `OfficerController.php`, profile blade views

---

## 1. Feature Overview

The **My Profile** screen is the officer's personal dashboard showing all their NCS records. On mobile, profile data is **read-only** for most fields (editable fields are limited to contact info, bank details, and profile picture). This is critical because Emolument pre-fills bank/PFA from the profile, and Chat uses profile info for member display.

---

## 2. Officer Data Fields

### Personal Information (Read-Only)
| Field | Type | Editable on Mobile? |
|-------|------|-------------------|
| `service_number` | string | ❌ System-managed |
| `appointment_number` | string | ❌ |
| `initials` | string | ❌ |
| `surname` | string | ❌ |
| `sex` | string | ❌ |
| `date_of_birth` | date | ❌ |
| `date_of_first_appointment` | date | ❌ |
| `date_of_present_appointment` | date | ❌ |
| `substantive_rank` | string | ❌ HR-managed |
| `salary_grade_level` | string | ❌ |
| `state_of_origin` | string | ❌ |
| `lga` | string | ❌ |
| `geopolitical_zone` | string | ❌ |
| `marital_status` | string | ❌ |
| `entry_qualification` | string | ❌ |
| `discipline` | string | ❌ |
| `additional_qualification` | string | ❌ |
| `present_station` | FK | ❌ HR-managed |
| `unit` | string | ❌ |

### Contact Information (Editable)
| Field | Editable? | Validation |
|-------|----------|------------|
| `phone_number` | ✅ | `required|string|max:20` |
| `personal_email` | ✅ | `required|email` |
| `residential_address` | ✅ | `required|string` |
| `permanent_home_address` | ✅ | `required|string` |

### Banking Information (Editable — used by Emolument)
| Field | Editable? | Validation |
|-------|----------|------------|
| `bank_name` | ✅ | `required|string|max:255` |
| `bank_account_number` | ✅ | `required|string|max:50` |
| `sort_code` | ✅ | `nullable|string|max:20` |
| `pfa_name` | ✅ | `required|string|max:255` |
| `rsa_number` | ✅ | `required|string|max:50` |

### Profile Picture
| Field | Editable? | Notes |
|-------|----------|-------|
| `profile_picture_url` | ✅ | Upload from camera or gallery |
| `profile_picture_updated_at` | Auto | Set on upload |
| `profile_picture_required_after_promotion_at` | System | Forces update after promotion |

### Computed/Derived Fields (Display Only)
| Field | Source |
|-------|--------|
| Full name | `{initials} {surname}` |
| Display rank | `substantive_rank` + `(T)` suffix if Transport unit |
| Time in service | Calculated from `date_of_first_appointment` |
| Time left in service | Calculated from retirement date |
| Retirement date | Min(age 60, 35 years service) |
| Days until retirement | Calculated |

### Status Fields (Read-Only, Display as Badges)
| Field | Display |
|-------|---------|
| `interdicted` | 🔴 Interdicted badge |
| `suspended` | 🔴 Suspended badge |
| `ongoing_investigation` | 🟡 Under Investigation badge |
| `dismissed` | 🔴 Dismissed badge |
| `quartered` | 🟢 Has quarters badge |
| `is_deceased` | — (wouldn't see own profile) |
| `preretirement_leave_status` | 🟡 On pre-retirement leave |

---

## 3. API Endpoints

```
GET    /api/v1/profile                    → Get complete officer profile
PUT    /api/v1/profile/contact            → Update contact info
PUT    /api/v1/profile/banking            → Update bank details
POST   /api/v1/profile/picture            → Upload profile picture
GET    /api/v1/profile/service-info       → Retirement/service calculations
GET    /api/v1/profile/documents          → Officer's documents
```

### `GET /api/v1/profile` Response
```json
{
  "status": "success",
  "data": {
    "id": 15,
    "service_number": "NCS/12345",
    "initials": "A.B.",
    "surname": "Smith",
    "full_name": "A.B. Smith",
    "display_rank": "ASC II",
    "sex": "Male",
    "date_of_birth": "1985-06-15",
    "date_of_first_appointment": "2010-03-01",
    "substantive_rank": "ASC II",
    "salary_grade_level": "GL 09",
    "present_station": { "id": 5, "name": "Lagos Command" },
    "unit": "Operations",
    "phone_number": "08012345678",
    "personal_email": "smith@email.com",
    "residential_address": "123 Victoria Island, Lagos",
    "bank_name": "First Bank",
    "bank_account_number": "0123456789",
    "pfa_name": "ARM Pension",
    "rsa_number": "PEN100012345678",
    "profile_picture_url": "/storage/profiles/officer_15.jpg",
    "needs_picture_update": false,
    "service_info": {
      "time_in_service": { "years": 16, "months": 0, "days": 0 },
      "time_left": { "years": 18, "months": 11, "days": 24 },
      "retirement_date": "2045-03-01",
      "retirement_type": "SVC",
      "days_until_retirement": 6940
    },
    "status_flags": {
      "interdicted": false,
      "suspended": false,
      "ongoing_investigation": false,
      "quartered": true,
      "preretirement_leave_status": null
    }
  }
}
```

---

## 4. Mobile Screens

### Screen 4.1: Profile Overview
```
┌─────────────────────────────────────┐
│  My Profile                         │
│  ─────────────────────────────────  │
│                                     │
│        ┌──────────┐                │
│        │  📷      │  ← Tap to change│
│        │  Photo   │                │
│        └──────────┘                │
│     ASC II A.B. Smith              │
│     NCS/12345 · Lagos Command      │
│     🟢 Active                      │
│                                     │
│  ┌─ Service Info ────────────────┐ │
│  │ In Service: 16 years          │ │
│  │ Time Left:  18 years 11 months│ │
│  │ Retirement: 01 Mar 2045 (SVC) │ │
│  └───────────────────────────────┘ │
│                                     │
│  [👤 Personal Info    →]           │
│  [📞 Contact Info     →]  ← editable│
│  [🏦 Banking Details  →]  ← editable│
│  [📋 Service Record   →]           │
│  [📄 My Documents     →]           │
│                                     │
└─────────────────────────────────────┘
```

---

## 5. React Native Implementation

### Component Structure
```
src/features/profile/
├── screens/
│   ├── ProfileOverviewScreen.tsx
│   ├── PersonalInfoScreen.tsx        → Read-only personal details
│   ├── ContactInfoScreen.tsx         → Editable contact info
│   ├── BankingDetailsScreen.tsx      → Editable bank/PFA
│   ├── ServiceRecordScreen.tsx       → Appointments, postings
│   └── DocumentsScreen.tsx           → Officer documents
├── components/
│   ├── ProfileHeader.tsx             → Photo + name + rank
│   ├── ServiceInfoCard.tsx           → Retirement countdown
│   ├── StatusBadge.tsx               → Interdicted/suspended etc.
│   └── ProfilePictureUploader.tsx    → Camera/gallery picker
├── api/
│   └── profileApi.ts
└── types/
    └── profile.ts
```

---

## 6. Testing Checklist

- [ ] Profile loads with all fields populated
- [ ] Profile picture upload from camera
- [ ] Profile picture upload from gallery
- [ ] Update contact information
- [ ] Update banking details → reflects in Emolument form
- [ ] Service info calculations correct
- [ ] Status badges display correctly
- [ ] Forced picture update after promotion
- [ ] Read-only fields cannot be edited
