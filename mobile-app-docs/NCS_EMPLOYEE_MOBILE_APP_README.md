# NCS Employee Mobile App — Full Development Plan

> **Document version:** 1.0  
> **Date:** 24 February 2026  
> **Status:** Planning  
> **Web Codebase Reference:** `/Users/macintosh/Developer/pisportal`

---

## Table of Contents

1. [Introduction & Purpose](#1-introduction--purpose)
2. [Technology Stack](#2-technology-stack)
3. [Authentication & Security](#3-authentication--security)
4. [Existing API Endpoints (Backend Ready)](#4-existing-api-endpoints-backend-ready)
5. [Feature 1 — Apply for Pass](#5-feature-1--apply-for-pass)
6. [Feature 2 — Apply for Leave](#6-feature-2--apply-for-leave)
7. [Feature 3 — Raise Emolument](#7-feature-3--raise-emolument)
8. [Feature 4 — Command Chat Room (Auto-Join)](#8-feature-4--command-chat-room-auto-join)
9. [Feature 5 — Management Chat Room (AC+ Rank)](#9-feature-5--management-chat-room-ac-rank)
10. [Feature 6 — Staff Officer Chat Admin](#10-feature-6--staff-officer-chat-admin)
11. [Feature 7 — Group Creation (WhatsApp-Style)](#11-feature-7--group-creation-whatsapp-style)
12. [Feature 8 — Push Notifications](#12-feature-8--push-notifications)
13. [Feature 9 — Additional Features](#13-feature-9--additional-features)
    - [My Profile](#a-my-profile)
    - [Transport (T&L Officer View)](#b-transport--tl-officer-view)
    - [Requests & Approvals Dashboard](#c-requests--approvals-dashboard)
    - [My Reports](#d-my-reports)
    - [Health & Pharmacy (Officer View)](#e-health--pharmacy-officer-view)
14. [Role-Based Access Matrix](#14-role-based-access-matrix)
15. [Screen-by-Screen Breakdown](#15-screen-by-screen-breakdown)
16. [API Endpoints Required per Feature](#16-api-endpoints-required-per-feature)
17. [Development Phases](#17-development-phases)
18. [Folder Structure](#18-folder-structure)
19. [Testing Strategy](#19-testing-strategy)
20. [Deployment & Distribution](#20-deployment--distribution)

---

## 1. Introduction & Purpose

The **NCS Employee Mobile App** is a responsive, native mobile application that provides officers of the Nigeria Customs Service with secure, role-based access to essential personal services, request workflows, command communications, and real-time notifications — all from their smartphones.

### What This App Is

- **"My MIS on my phone"** — a pocket version of the MIS web portal for daily operational needs.
- Officers can perform personal access, quick actions, requests & approvals, official communication, and view transport information.
- **No new backend functionality** — every feature already exists in the web app. The mobile app simply consumes the existing Laravel API endpoints.

### What This App Is NOT

- It is **not** a full admin panel — sensitive administrative controls (officer deletion, bulk promotions, retirement processing, establishment management) remain web-only.
- It does **not** replace the web app — it complements it for field use.

### Key Problems Solved

| Problem | Solution |
|---------|----------|
| Officers send bank details to others to raise emolument → officers go unpaid for a year | Officers raise their own emolument directly from their phone |
| Missed official communications via WhatsApp | Formal command chat rooms with audit trail |
| No visibility into request status while in the field | Real-time push notifications at every workflow step |
| Officers cannot update profile after promotion while away | Upload passport photo directly from phone camera |

---

## 2. Technology Stack

| Layer | Technology | Rationale |
|-------|-----------|-----------|
| **Framework** | React Native (Expo) | Single codebase for iOS & Android; fast iteration with OTA updates |
| **Language** | TypeScript | Type safety, better developer experience |
| **State Management** | Redux Toolkit + RTK Query | Automatic caching, background refetching, mutation handling |
| **Navigation** | React Navigation v6 (Stack + Bottom Tabs) | Native feel, deep-linking support for notifications |
| **Real-time Chat** | WebSocket via Laravel Echo + Pusher/Soketi | Existing backend already uses Pusher-compatible events |
| **Push Notifications** | Firebase Cloud Messaging (FCM) + Expo Notifications | Cross-platform, reliable delivery |
| **Secure Storage** | expo-secure-store | Encrypted token storage on device |
| **Biometrics** | expo-local-authentication | Face ID / Fingerprint on supported devices |
| **HTTP Client** | Axios (via RTK Query base query) | Consistent with existing web app patterns |
| **Backend** | Existing Laravel 10 + Sanctum API (`/api/v1/*`) | Already built — 187 lines of API routes with full CRUD |
| **Database** | Existing MySQL (no changes) | 80 models already defined |

---

## 3. Authentication & Security

### 3.1 Login Flow

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│  Login Screen │────▶│ POST /api/v1 │────▶│ Token stored │
│  (email +     │     │ /auth/login  │     │ in Secure    │
│   password)   │     │              │     │ Store        │
└──────────────┘     └──────────────┘     └──────┬───────┘
                                                  │
                                           ┌──────▼───────┐
                                           │ Enable       │
                                           │ Biometric    │
                                           │ for next     │
                                           │ login        │
                                           └──────────────┘
```

**Existing Web Route:** `POST /api/v1/auth/login` → returns Sanctum token  
**Existing Web Route:** `GET /api/v1/auth/me` → returns authenticated user + officer data  
**Existing Web Route:** `POST /api/v1/auth/logout` → invalidates token

### 3.2 Security Features

| Feature | Implementation | Details |
|---------|---------------|---------|
| **Biometric Login** | `expo-local-authentication` | Face ID / Fingerprint after initial credential login |
| **Auto-Logout** | Inactivity timer (10 min) | Clears auth token, returns to login screen |
| **Device Binding** | Unique device ID (`expo-device`) | Backend validates only one active device per officer |
| **Remote Deactivation** | Backend token invalidation | If phone is lost, admin revokes token → app detects 401 → force logout |
| **Encrypted Attachments** | HTTPS + `expo-file-system` encrypted storage | Downloaded files never stored in plaintext |
| **Two-Factor Auth** | Existing 2FA routes | `POST /two-factor/verify` already supported on web |

### 3.3 New API Endpoints Needed (Backend Additions)

| Endpoint | Purpose |
|----------|---------|
| `POST /api/v1/auth/register-device` | Register FCM token + device fingerprint |
| `POST /api/v1/auth/biometric-challenge` | Issue a challenge for biometric re-auth |
| `DELETE /api/v1/auth/deactivate-device` | Admin remote wipe / deactivate |

---

## 4. Existing API Endpoints (Backend Ready)

The Laravel backend already exposes **65+ API endpoints** under `/api/v1/` secured with `auth:sanctum`. Below is a categorized summary:

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/v1/auth/login` | Login, returns Sanctum token |
| `POST` | `/v1/auth/logout` | Logout, invalidates token |
| `POST` | `/v1/auth/refresh` | Refresh token |
| `GET` | `/v1/auth/me` | Get authenticated user + officer profile |

### Officers
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/officers` | List officers (role-scoped) |
| `GET` | `/v1/officers/{id}` | Get officer details |
| `PATCH` | `/v1/officers/{id}` | Update officer (limited edits) |

### Emoluments
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/emoluments/my-emoluments` | Officer's own emoluments |
| `POST` | `/v1/emoluments` | Raise new emolument |
| `GET` | `/v1/emoluments/{id}` | View emolument details |
| `POST` | `/v1/emoluments/{id}/assess` | Assessor action |
| `POST` | `/v1/emoluments/{id}/validate` | Validator action |

### Leave Applications
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/leave-applications` | List leave applications |
| `GET` | `/v1/leave-applications/{id}` | View leave application |
| `POST` | `/v1/officers/{id}/leave-applications` | Submit new leave |
| `POST` | `/v1/leave-applications/{id}/minute` | Staff Officer minute |
| `POST` | `/v1/leave-applications/{id}/approve` | DC Admin approve |

### Pass Applications
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/pass-applications` | List pass applications |
| `POST` | `/v1/officers/{id}/pass-applications` | Submit new pass |
| `POST` | `/v1/pass-applications/{id}/approve` | Approve pass |

### Chat
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/chat/rooms` | List user's chat rooms |
| `POST` | `/v1/chat/rooms` | Create new chat room |
| `GET` | `/v1/chat/rooms/{id}/messages` | Get room messages (paginated) |
| `POST` | `/v1/chat/rooms/{id}/messages` | Send message |

### Notifications
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/notifications` | List all notifications |
| `PATCH` | `/v1/notifications/{id}/read` | Mark single as read |
| `PATCH` | `/v1/notifications/read-all` | Mark all as read |

### Quarters (Accommodation)
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/quarters/my-requests` | Officer's quarter requests |
| `GET` | `/v1/quarters/my-allocations` | Officer's allocations |
| `POST` | `/v1/quarters/allocations/{id}/accept` | Accept allocation |
| `POST` | `/v1/quarters/allocations/{id}/reject` | Reject allocation |

### Other
| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/v1/commands` | List all commands |
| `GET` | `/v1/zones` | List all zones |
| `GET` | `/v1/roles` | List all roles |
| `GET` | `/v1/leave-types` | List leave types |
| `GET` | `/v1/duty-rosters` | List duty rosters |

---

## 5. Feature 1 — Apply for Pass

### Web Flow (Studied from `PassApplicationController`)

```
Officer fills form          Staff Officer         DC Admin
      │                         │                    │
      ▼                         │                    │
 POST /pass/apply              │                    │
 (start_date, end_date,        │                    │
  reason, number_of_days)      │                    │
      │                         │                    │
      ▼                         │                    │
 Status: PENDING ──────────────▶│                    │
      │                    Minute (add              │
      │                    recommendation)          │
      │                         │                    │
      │                    Status: MINUTED ─────────▶│
      │                         │              Approve / Reject
      │                         │                    │
      │                         │              Status: APPROVED
      │                         │              or REJECTED
      │◀─────── Notification ──────────────────────│
```

### Data Model (`pass_applications` table)

| Field | Type | Notes |
|-------|------|-------|
| `officer_id` | FK → officers | Auto-filled from auth |
| `start_date` | date | Required |
| `end_date` | date | Required |
| `number_of_days` | integer | Calculated |
| `reason` | text | Required |
| `status` | enum | `PENDING` → `MINUTED` → `APPROVED` / `REJECTED` |
| `minuted_at` | datetime | When Staff Officer minuted |
| `rejection_reason` | text | If rejected |
| `expiry_alert_sent` | boolean | 72h before expiry alert |

### Mobile Screens

1. **Pass List** — Shows all of the officer's pass applications with status badges
2. **Apply for Pass Form** — Date pickers, reason textarea, auto-calculated days
3. **Pass Detail** — Full details, approval timeline, print option

### API Endpoints Used

- `POST /api/v1/officers/{id}/pass-applications` — Submit
- `GET /api/v1/pass-applications` — List
- `POST /api/v1/pass-applications/{id}/approve` — For Staff Officer / DC Admin roles

---

## 6. Feature 2 — Apply for Leave

### Web Flow (Studied from `LeaveApplicationController`)

```
Officer fills form          Staff Officer         DC Admin
      │                         │                    │
      ▼                         │                    │
 POST /leave/apply             │                    │
 (leave_type_id, start_date,   │                    │
  end_date, reason,            │                    │
  medical_certificate,         │                    │
  expected_date_of_delivery)   │                    │
      │                         │                    │
      ▼                         │                    │
 Status: PENDING ──────────────▶│                    │
      │                    Minute (add              │
      │                    recommendation)          │
      │                         │                    │
      │                    Status: MINUTED ─────────▶│
      │                         │              Approve / Reject
      │                         │                    │
      │◀─────── Notification ──────────────────────│
```

### Data Model (`leave_applications` table)

| Field | Type | Notes |
|-------|------|-------|
| `officer_id` | FK → officers | Auto-filled |
| `leave_type_id` | FK → leave_types | Dropdown selection |
| `start_date` | date | Required |
| `end_date` | date | Required |
| `number_of_days` | integer | Calculated |
| `reason` | text | Required |
| `expected_date_of_delivery` | date | For maternity leave only |
| `medical_certificate_url` | string | File upload for sick leave |
| `status` | enum | `PENDING` → `MINUTED` → `APPROVED` / `REJECTED` |
| `rejection_reason` | text | If rejected |
| `alert_sent_72h` | boolean | Auto-alert before end of leave |

### Leave Types (from `leave_types` table)

| Type | Max Days | Conditions |
|------|----------|------------|
| Annual Leave | 30 | Standard |
| Sick Leave | Varies | Requires medical certificate upload |
| Maternity Leave | 90 | Requires expected date of delivery |
| Casual Leave | 10 | Short-term |
| Compassionate Leave | 14 | Bereavement/family emergency |
| Study Leave | Varies | Course-related |
| Exam Leave | Varies | Examination period |

### Mobile Screens

1. **Leave History** — List of all leave applications with colored status badges
2. **Apply for Leave Form** — Leave type dropdown (auto-fetched via `GET /api/v1/leave-types`), date pickers, conditional fields (medical cert for sick leave, EDD for maternity), reason
3. **Leave Detail** — Full timeline, approval status, ability to print document

### API Endpoints Used

- `GET /api/v1/leave-types` — Populate dropdown
- `POST /api/v1/officers/{id}/leave-applications` — Submit
- `GET /api/v1/leave-applications` — List
- `GET /api/v1/leave-applications/{id}` — Detail
- `POST /api/v1/leave-applications/{id}/minute` — Staff Officer action
- `POST /api/v1/leave-applications/{id}/approve` — DC Admin action

---

## 7. Feature 3 — Raise Emolument

### The Problem This Solves

> *"So many officers have ended up not being paid for a whole year because they sent their bank details to other officers to help raise their emolument."*

With the mobile app, **every officer raises their own emolument form directly** — no intermediary, no lost data, no unpaid salaries.

### Web Flow (Studied from `EmolumentController` — 1,584 lines)

The emolument has a **6-step workflow** with notifications at every step:

```
Officer                     Assessor              Validator
   │                            │                     │
   ▼                            │                     │
POST /emolument/raise          │                     │
(bank_name, account_number,    │                     │
 pfa_name, rsa_pin)            │                     │
   │                            │                     │
   ▼                            │                     │
Status: SUBMITTED ─────────────▶│                     │
   │                       Assess (compare           │
   │                       with officer records)     │
   │                            │                     │
   │                       Status: ASSESSED ─────────▶│
   │                            │                Validate (verify
   │                            │                 bank details)
   │                            │                     │
   │                            │                     ▼
   │                            │              Status: VALIDATED
   │                            │                     │
   ▼                            │                     ▼
Area Controller ◀──────────────────────────────────────│
   │ (Validate for area)                              │
   ▼                                                  │
Auditor ◀──────────────────────────────────────────────│
   │ (Audit check)                                    │
   ▼                                                  │
Accounts ◀─────────────────────────────────────────────│
   │ (Process payment)                                │
   ▼                                                  │
Status: PROCESSED ──── Notification to Officer ────────│
```

### Full Status Chain

`SUBMITTED` → `ASSESSED` → `VALIDATED` → `AUDITED` → `PROCESSED`

At **any step**, the form can be **REJECTED** with reasons, and the officer is notified immediately. The officer can then **resubmit** the form.

### Data Model (`emoluments` table)

| Field | Type | Notes |
|-------|------|-------|
| `officer_id` | FK → officers | Auto-filled |
| `timeline_id` | FK → emolument_timelines | Active timeline period |
| `year` | integer | e.g. 2026 |
| `bank_name` | string | Auto-populated from officer profile, editable |
| `bank_account_number` | string | Auto-populated, editable |
| `pfa_name` | string | Auto-populated, editable |
| `rsa_pin` | string | Auto-populated, editable |
| `status` | enum | Full chain above |
| `submitted_at` | datetime | |
| `assessed_at` | datetime | |
| `validated_at` | datetime | |
| `audited_at` | datetime | |
| `processed_at` | datetime | |

### Related Models

- `EmolumentAssessment` — Assessor's findings
- `EmolumentValidation` — Validator's verification
- `EmolumentAudit` — Auditor's review
- `EmolumentTimeline` — Defines the active submission window

### Mobile Screens

1. **Emolument Dashboard** — List of all officer's emoluments with status badges and year
2. **Raise Emolument Form** — Pre-populated from officer profile (bank, PFA), editable, submit
3. **Emolument Detail** — Shows full workflow timeline with timestamps at each stage
4. **Resubmit Form** — If rejected, shows rejection reason and allows correction

### Push Notification Events (per emolument)

| Event | Recipient | Message |
|-------|-----------|---------|
| Form submitted | Assessor | "New emolument form from [Officer Name]" |
| Assessed | Validator | "Emolument form assessed, awaiting validation" |
| Validated | Area Controller | "Emolument form validated, awaiting area check" |
| Area Controller validated | Auditor | "Emolument form awaiting audit" |
| Audited | Accounts | "Emolument form audited, awaiting payment processing" |
| Processed | Officer | "Your emolument has been processed for payment" |
| Rejected (any step) | Officer | "Your emolument form was rejected: [reason]" |

### API Endpoints Used

- `GET /api/v1/emoluments/my-emoluments` — Officer's emoluments
- `POST /api/v1/emoluments` — Raise new emolument
- `GET /api/v1/emoluments/{id}` — View details
- `POST /api/v1/emoluments/{id}/assess` — Assessor action
- `POST /api/v1/emoluments/{id}/validate` — Validator action
- `POST /emolument/{id}/resubmit` (web route, needs API equivalent)

---

## 8. Feature 4 — Command Chat Room (Auto-Join)

### Web Flow (Studied from `ChatController` + `ChatRoom` model)

The `ChatRoom` model has a `command_id` and `room_type` field:

```php
// ChatRoom fillable fields:
'command_id', 'room_type', 'name', 'description', 'is_active'
```

**Room types:**
- `command` — Every officer in a command is automatically a member
- `management` — Only officers of rank AC (Assistant Comptroller) and above
- `custom` — User-created groups (Feature 7)

### Auto-Join Logic

When an officer is **accepted into a command** (via `StaffOfficer\PostingController@acceptOfficer`):

1. The officer's `present_station` is updated to the new command
2. A `ChatRoomMember` record is created for the command's chat room
3. If the officer's rank is AC or above, they are also added to the management chat room

### Mobile Implementation

- On login, fetch `GET /api/v1/chat/rooms` — returns all rooms the officer is a member of
- Command chat room appears automatically — no join action needed
- Real-time messages via WebSocket (Laravel Echo + Pusher)

### API Endpoints Used

- `GET /api/v1/chat/rooms` — List rooms
- `GET /api/v1/chat/rooms/{id}/messages` — Get messages (paginated, 50 per page)
- `POST /api/v1/chat/rooms/{id}/messages` — Send message (max 5000 chars)

---

## 9. Feature 5 — Management Chat Room (AC+ Rank)

### Rank-Based Auto-Join

Officers of the rank of **Assistant Comptroller (AC) and above** automatically enter the **Management Chat Room** of their command, in addition to the regular command chat room.

### NCS Rank Hierarchy (for reference)

| Rank | Abbreviation | Management Chat? |
|------|-------------|-------------------|
| Customs Assistant (CA) | CA | ❌ |
| Assistant Inspector of Customs (AIC) | AIC | ❌ |
| Inspector of Customs (IC) | IC | ❌ |
| Superintendent of Customs (SC) | SC | ❌ |
| Chief Superintendent of Customs (CSC) | CSC | ❌ |
| Assistant Comptroller of Customs (ACC) | ACC | ✅ |
| Deputy Comptroller of Customs (DCC) | DCC | ✅ |
| Comptroller of Customs (CC) | CC | ✅ |
| Assistant Comptroller General (ACG) | ACG | ✅ |
| Deputy Comptroller General (DCG) | DCG | ✅ |
| Comptroller General of Customs (CGC) | CGC | ✅ |

### Mobile Screens

- Both chat rooms appear in the **Chat Rooms** tab
- Visual distinction: management rooms have a 🏛️ badge; command rooms have a 🏢 badge
- Same messaging interface for both

---

## 10. Feature 6 — Staff Officer Chat Admin

### Admin Capabilities (Staff Officer Role)

The Staff Officer is the **admin** of the chat rooms for their command. They can:

| Action | Description |
|--------|-------------|
| **Remove members** | Remove any officer from the command or management chat room |
| **Add officers of any rank** | Add officers of **any rank** into the Management Chat Room (overrides the AC+ rule) — e.g., unit heads who are below AC rank |
| **View member list** | See all members of any room in their command |

### New API Endpoints Needed

| Endpoint | Purpose |
|----------|---------|
| `DELETE /api/v1/chat/rooms/{id}/members/{userId}` | Remove member from room |
| `POST /api/v1/chat/rooms/{id}/members` | Add member to room |
| `GET /api/v1/chat/rooms/{id}/members` | List all members |

---

## 11. Feature 7 — Group Creation (WhatsApp-Style)

### Existing API

The `POST /api/v1/chat/rooms` endpoint already supports creating custom groups:

```json
POST /api/v1/chat/rooms
{
    "name": "Zone D Logistics Team",
    "description": "Coordination group for Zone D logistics",
    "member_ids": [12, 45, 78, 90]
}
```

### Features

- **Create groups** — Any officer can create a group, specify name, description, and invite members
- **Share documents** — Attach files to messages (needs new attachment support)
- **Group management** — Creator can add/remove members
- **Document sharing** — PDFs, images, circulars can be shared within the group

### Mobile Screens

1. **Chat Rooms List** — Shows all rooms: command (auto), management (auto), custom (user-created)
2. **Create Group** — Name, description, member search/select
3. **Chat Room** — WhatsApp-style message bubbles, send text/attachments
4. **Room Info** — Member list, add/remove (if admin), leave group

### New API Endpoints Needed

| Endpoint | Purpose |
|----------|---------|
| `POST /api/v1/chat/rooms/{id}/messages` (with file) | Send message with attachment |
| `PUT /api/v1/chat/rooms/{id}` | Update room name/description |
| `DELETE /api/v1/chat/rooms/{id}/leave` | Leave a custom group |

---

## 12. Feature 8 — Push Notifications

> **RULE: Every single notification that fires on the web MUST also fire as a push notification on the mobile app. No exceptions.**

### Notification Architecture

```
┌────────────┐     ┌────────────┐     ┌────────────┐     ┌────────────┐
│  Laravel    │────▶│  FCM /     │────▶│  Device    │────▶│  App opens │
│  Backend    │     │  APNs      │     │  receives  │     │  deep-link │
│  (event     │     │            │     │  push      │     │  to detail │
│   triggers  │     │            │     │  notif.    │     │  screen    │
│   notif.)   │     │            │     │            │     │            │
└────────────┘     └────────────┘     └────────────┘     └────────────┘
```

### How It Works (Single Source of Truth)

The existing `NotificationService.php` (3,229 lines, 82 notification methods) is the **single source of truth** for all notifications. Currently it:

1. Creates an in-app `Notification` record in the database
2. Sends an email (via queued job)

**For mobile, we add a 3rd channel:** Every time `notify()` is called, it also dispatches a push notification via FCM to the officer's registered device. This ensures **zero notification duplication** — one method call = web + email + mobile push.

### COMPLETE Notification Catalog (All 82 Types from the Web App)

Every notification below is sourced directly from `NotificationService.php`. **ALL of these go to the mobile app.**

---

#### 📋 LEAVE APPLICATIONS (5 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 1 | `notifyLeaveApplicationSubmitted` | `leave_application_submitted` | New Leave Application | Staff Officers (command-scoped) | "Officer [Name] ([SN]) has submitted a [leave_type] application from [date] to [date] ([N] days)" |
| 2 | `notifyLeaveApplicationMinuted` | `leave_application_minuted` | Leave Application Minuted | Officer (applicant) | "Your [leave_type] application from [date] to [date] has been minuted and forwarded to DC Admin" |
| 3 | `notifyLeaveApplicationMinutedToDcAdmin` | `leave_application_minuted` | Leave Application Minuted - Requires Approval | DC Admins (command-scoped) | "Leave application for Officer [Name] has been minuted and requires your approval" |
| 4 | `notifyLeaveApplicationApproved` | `leave_application_approved` | Leave Application Approved | Officer (applicant) | "Your leave application from [date] to [date] ([N] days) has been approved" |
| 5 | `notifyLeaveApplicationRejected` | `leave_application_rejected` | Leave Application Rejected | Officer (applicant) | "Your leave application has been rejected. Reason: [reason]" |

---

#### 🎫 PASS APPLICATIONS (3 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 6 | `notifyPassApplicationSubmitted` | `pass_application_submitted` | New Pass Application | Staff Officers (command-scoped) | "Officer [Name] ([SN]) has submitted a pass application from [date] to [date] ([N] days)" |
| 7 | `notifyPassApplicationApproved` | `pass_application_approved` | Pass Application Approved | Officer (applicant) | "Your pass application has been approved" |
| 8 | `notifyPassApplicationRejected` | `pass_application_rejected` | Pass Application Rejected | Officer (applicant) | "Your pass application has been rejected. Reason: [reason]" |

---

#### 💰 EMOLUMENT (8 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 9 | `notifyEmolumentRaised` | `emolument_raised` | New Emolument Raised | Assessors (command-scoped) | "Officer [Name] ([SN]) has raised an emolument for [year]" |
| 10 | `notifyEmolumentAssessed` | `emolument_assessed` | Emolument Assessed | Officer (raiser) | "Your emolument for [year] has been assessed: [APPROVED/REJECTED]" |
| 11 | `notifyEmolumentAssessedReadyForValidation` | `emolument_assessed` | Emolument Assessed - Ready for Validation | Validators + Area Controllers | "Emolument for [Name] ([SN]) has been assessed and requires validation" |
| 12 | `notifyEmolumentValidated` | `emolument_validated` | Emolument Validated | Officer (raiser) | "Your emolument for [year] has been validated: [APPROVED/REJECTED]" |
| 13 | `notifyEmolumentValidatedReadyForAudit` | `emolument_validated` | Emolument Validated - Ready for Audit | Auditors | "Emolument for [Name] ([SN]) has been validated and requires audit" |
| 14 | `notifyEmolumentAudited` | `emolument_audited` | Emolument Audited | Officer (raiser) | "Your emolument for [year] has been audited: [APPROVED/REJECTED]" |
| 15 | `notifyEmolumentAuditedReadyForProcessing` | `emolument_audited` | Emolument Audited - Ready for Processing | Accounts team | "Emolument for [Name] has been audited and is ready for payment processing" |
| 16 | `notifyEmolumentProcessed` | `emolument_processed` | Emolument Processed | Officer (raiser) | "Your emolument for [year] has been processed for payment" |

---

#### 🏢 MANNING REQUESTS (7 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 17 | `notifyManningRequestSubmitted` | `manning_request_submitted` | New Manning Request Submitted | DC Admins + Area Controllers | "A manning request for [Command] has been submitted by [Name]. [N] positions, [N] officers needed" |
| 18 | `notifyManningRequestApproved` | `manning_request_approved` | Manning Request Approved | Staff Officer (requester) | "Your manning request for [Command] has been approved" |
| 19 | `notifyManningRequestRejected` | `manning_request_rejected` | Manning Request Rejected | Staff Officer (requester) | "Your manning request has been rejected. Reason: [reason]" |
| 20 | `notifyManningRequestApprovedToHrd` | `manning_request_approved` | Manning Request Approved - Ready for Matching | HRD team | "Manning request for [Command] approved. [N] positions, [N] officers total" |
| 21 | `notifyManningRequestApprovedToZoneCoordinators` | `manning_request_approved` | Manning Request Approved - Ready for Processing | Zone Coordinators (same zone) | "Zone manning request for [Command] approved. Please proceed with movement orders" |
| 22 | `notifyManningRequestFulfilled` | `manning_request_fulfilled` | Manning Request Fulfilled | Staff Officer (requester) | "Your manning request for [Command] has been fulfilled. [N] officers matched" |
| 23 | `notifyCommandOfficerRelease` | `officer_release` | Officer Release Notification | Staff Officers (from-command) | "Officer [Name] is being released from [Command] to [Command]" |

---

#### 📮 POSTINGS & TRANSFERS (5 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 24 | `notifyOfficerPosted` | `officer_posted` | Officer Posted | Officer (being posted) | "You have been posted from [Command] to [Command]" |
| 25 | `notifyOfficerTransfer` | `officer_transfer` | Officer Transfer | Officer (being transferred) | "Your transfer from [Command] to [Command] has been processed" |
| 26 | `notifyStaffOfficerPendingArrival` | `pending_arrival` | Pending Officer Arrival | Staff Officers (to-command) | "Officer [Name] is being transferred to your command. Please prepare for arrival" |
| 27 | `notifyOfficerAccepted` | `officer_accepted` | Accepted into Command | Officer (accepted) | "You have been accepted into [Command]" |
| 28 | `notifyStaffOrderCreated` | `staff_order_created` | Staff Order Created | Officer (being posted) | "A new staff order has been created. You are being posted from [Command] to [Command]. Order: [Number]" |

---

#### 👤 OFFICER STATUS CHANGES (8 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 29 | `notifyRankChanged` | `rank_changed` | Rank Changed | Officer | "Your rank has been changed from [Old] to [New]" |
| 30 | `notifyInterdictionStatusChanged` | `interdiction_status_changed` | Interdiction Status Changed | Officer | "Your interdiction status has been [applied/lifted]" |
| 31 | `notifySuspensionStatusChanged` | `suspension_status_changed` | Suspension Status Changed | Officer | "Your suspension has been [applied/lifted]" |
| 32 | `notifyOfficerDismissed` | `officer_dismissed` | Officer Dismissed | Officer | "You have been dismissed from service" |
| 33 | `notifyActiveStatusChanged` | `active_status_changed` | Active Status Changed | Officer | "Your account has been [activated/deactivated]" |
| 34 | `notifyCommandChanged` | `command_changed` | Command Changed | Officer | "Your command has been changed from [Old] to [New]" |
| 35 | `notifyDatePostedChanged` | `date_posted_changed` | Date Posted to Station Changed | Officer | "Your date posted to station has been changed from [Old] to [New]" |
| 36 | `notifyUnitChanged` | `unit_changed` | Unit Assignment Changed | Officer | "Your unit has been changed from [Old] to [New]" |

---

#### ❓ QUERIES (6 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 37 | `notifyQueryIssued` | `query_issued` | Query Issued | Officer (queried) | "A query has been issued to you by [Issuer]. Subject: [Subject]. Deadline: [Date]" |
| 38 | `notifyQueryResponseSubmitted` | `query_response_submitted` | Query Response Submitted | Staff Officer (issuer) | "Officer [Name] has responded to query: [Subject]" |
| 39 | `notifyQueryAccepted` | `query_accepted` | Query Response Accepted | Officer (queried) | "Your response to query '[Subject]' has been accepted" |
| 40 | `notifyAuthoritiesQueryAccepted` | `query_accepted` | Query Accepted - Authorities Notified | Area Controller + DC Admin + HRD | "Query for Officer [Name] has been accepted. Status: [outcome]" |
| 41 | `notifyQueryRejected` | `query_rejected` | Query Response Rejected | Officer (queried) | "Your response to query '[Subject]' has been rejected" |
| 42 | `notifyQueryExpired` | `query_expired` | Query Expired | Officer (queried) | "Your query '[Subject]' has expired and been automatically accepted" |
| 43 | `notifyQueryDeadlineReminder` | `query_deadline_reminder` | Query Deadline Approaching | Officer (queried) | "⚠️ Your query '[Subject]' deadline is in [N] hours. Please respond" |

---

#### 🏠 QUARTERS / ACCOMMODATION (8 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 44 | `notifyQuarterAllocated` | `quarter_allocated` | Quarter Allocated | Officer | "A quarter has been allocated to you at [Quarter]" |
| 45 | `notifyQuarterDeallocated` | `quarter_deallocated` | Quarter Deallocated | Officer | "Your quarter [Quarter] has been deallocated" |
| 46 | `notifyQuarteredStatusUpdated` | `quartered_status_updated` | Quartered Status Updated | Officer | "Your quartered status has been [updated]" |
| 47 | `notifyQuarterCreated` | `quarter_created` | New Quarter Created | Building Unit users | "A new quarter has been created in [Command]" |
| 48 | `notifyQuarterRequestSubmitted` | `quarter_request_submitted` | Quarter Request Submitted | Building Unit users (command-scoped) | "Officer [Name] has submitted a quarter request" |
| 49 | `notifyQuarterRequestApproved` | `quarter_request_approved` | Quarter Request Approved | Officer (requester) | "Your quarter request has been approved. Quarter: [Details]" |
| 50 | `notifyQuarterRequestRejected` | `quarter_request_rejected` | Quarter Request Rejected | Officer (requester) | "Your quarter request has been rejected. Reason: [reason]" |
| 51 | `notifyQuarterAllocationAccepted` | `quarter_allocation_accepted` | Quarter Allocation Accepted | Building Unit users | "Officer [Name] has accepted the allocated quarter" |
| 52 | `notifyQuarterAllocationRejected` | `quarter_allocation_rejected` | Quarter Allocation Rejected | Building Unit users | "Officer [Name] has rejected the allocated quarter. Reason: [reason]" |

---

#### 📚 COURSES & TRAINING (3 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 53 | `notifyCourseNominationCreated` | `course_nomination_created` | Course Nomination | Officer (nominated) | "You have been nominated for the course: [Course Name]" |
| 54 | `notifyCourseCompleted` | `course_completed` | Course Completed | Officer | "Your course [Course Name] has been marked as completed" |
| 55 | `notifyCourseCompletionSubmitted` | `course_completion_submitted` | Course Completion Document Submitted | HRD + Staff Officer | "Officer [Name] has submitted a completion document for [Course] for review" |

---

#### 📋 DUTY ROSTER (7 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 56 | `notifyDutyRosterAssigned` | `duty_roster_assigned` | Duty Roster Assignment | Officers (assigned) | "You have been assigned to Duty Roster: [Roster Name], Duty: [Type], Period: [Date Range]" |
| 57 | `notifyOfficerReassignedFromRoster` | `officer_reassigned_roster` | Roster Reassignment | Officer (reassigned) + Staff Officer | "Officer [Name] has been reassigned from [Previous Roster] to [New Roster]" |
| 58 | `notifyDutyRosterSubmitted` | `duty_roster_submitted` | Duty Roster Submitted | DC Admins (command-scoped) | "A duty roster for [Period] has been submitted for approval" |
| 59 | `notifyDutyRosterSubmittedToAreaController` | `duty_roster_submitted` | Duty Roster Submitted | Area Controllers | "A duty roster for [Command] [Period] has been submitted for area approval" |
| 60 | `notifyDutyRosterSubmittedToCd` | `duty_roster_submitted` | Duty Roster Submitted (Transport) | CD (Fleet) | "A duty roster with Transport officers has been submitted. CD approval required" |
| 61 | `notifyDutyRosterApproved` | `duty_roster_approved` | Duty Roster Approved | Staff Officer (creator) + All assigned officers | "Duty roster for [Period] has been approved by [Approver]. Assignment emails sent" |
| 62 | `notifyDutyRosterRejected` | `duty_roster_rejected` | Duty Roster Rejected | Staff Officer (creator) | "Duty roster for [Period] has been rejected. Reason: [reason]" |

---

#### 📄 INTERNAL STAFF ORDERS (3 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 63 | `notifyInternalStaffOrderSubmitted` | `internal_staff_order_submitted` | Internal Staff Order Submitted | DC Admins (command-scoped) | "An internal staff order has been submitted for your approval" |
| 64 | `notifyInternalStaffOrderApproved` | `internal_staff_order_approved` | Internal Staff Order Approved | Staff Officer + affected officers | "Internal staff order approved. [Officer] reassigned from [Old Duty] to [New Duty]" |
| 65 | `notifyInternalStaffOrderRejected` | `internal_staff_order_rejected` | Internal Staff Order Rejected | Staff Officer (creator) | "Internal staff order has been rejected. Reason: [reason]" |

---

#### 🚗 FLEET / TRANSPORT (via FleetWorkflowService — workflow-level notifications)

| # | Type | Title | Recipient | Message Summary |
|---|------|-------|-----------|----------------|
| 66 | `fleet_request_submitted` | Fleet Request Submitted | Next approver in chain | "A fleet request [Type] has been submitted for review" |
| 67 | `fleet_request_approved` | Fleet Request Approved | Requester | "Your fleet request has been approved at [Step]" |
| 68 | `fleet_request_rejected` | Fleet Request Rejected | Requester | "Your fleet request has been rejected. Reason: [reason]" |
| 69 | `fleet_request_completed` | Fleet Request Completed | Requester | "Your fleet request has been fully processed" |
| 70 | `fleet_vehicle_assigned` | Vehicle Assigned | Officer/Command | "Vehicle [Plate] has been assigned to [Command/Officer]" |
| 71 | `fleet_vehicle_received` | Vehicle Received | Area Controller | "Vehicle [Plate] has been received at [Command]" |
| 72 | `fleet_vehicle_intake` | Vehicle Intake Processed | CC T&L / OC T&L | "New vehicle intake processed: [Vehicle Type] [Plate]" |
| 73 | `fleet_service_status_changed` | Vehicle Service Status Changed | CD / OC T&L | "Vehicle [Plate] service status changed to [Status]" |

---

#### 💊 PHARMACY (via PharmacyWorkflowService — workflow-level notifications)

| # | Type | Title | Recipient | Message Summary |
|---|------|-------|-----------|----------------|
| 74 | `pharmacy_procurement_submitted` | Procurement Submitted | Comptroller Pharmacy | "A new procurement request has been submitted for approval" |
| 75 | `pharmacy_procurement_approved` | Procurement Approved | Comptroller Procurement | "Your procurement request has been approved by Comptroller Pharmacy" |
| 76 | `pharmacy_procurement_received` | Procurement Received | Comptroller Pharmacy + Comptroller Procurement | "Procurement items have been received by Central Medical Store" |
| 77 | `pharmacy_requisition_submitted` | Requisition Submitted | Comptroller Pharmacy | "A new requisition from [Command] has been submitted" |
| 78 | `pharmacy_requisition_approved` | Requisition Approved | Command Pharmacist | "Your requisition has been approved" |
| 79 | `pharmacy_requisition_issued` | Requisition Issued | Command Pharmacist | "Items for your requisition have been issued by Central Medical Store" |

---

#### 🆕 RECRUIT / ONBOARDING (7 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 80 | `notifyRecruitCreated` | `recruit_created` | New Recruit Added | Establishment user | "A new recruit [Name] has been added to the system" |
| 81 | `notifyAppointmentAssigned` | `appointment_assigned` | Appointment Number Assigned | Establishment user | "Appointment number [Number] assigned to [Name]" |
| 82 | `notifyNewRecruit` | `new_recruit_created` | New Recruit Created | TRADOC users | "A new recruit [Name] has been created" |
| 83 | `notifyRecruitOnboardingCompleted` | `recruit_onboarding_completed` | Recruit Onboarding Completed | Establishment users | "Recruit [Name] has completed onboarding and is ready for verification" |
| 84 | `notifyRecruitsReadyForTraining` | `recruits_ready_training` | Recruits Ready for Training | TRADOC users | "[N] new recruit(s) with appointment numbers are ready for training results upload" |
| 85 | `notifyTrainingResultsUploaded` | `training_results_uploaded` | Training Results Uploaded | Establishment user | "[N] training result(s) uploaded and ready for service number assignment" |
| 86 | `notifyOnboardingInitiated` | `onboarding_initiated` | Onboarding Initiated | Officer (new recruit) | "Your onboarding process has been initiated. Check your email" |

---

#### 🔧 SYSTEM / ROLE / IDENTITY (5 notification types)

| # | Method | `notification_type` | Title | Recipient | Message Summary |
|---|--------|---------------------|-------|-----------|----------------|
| 87 | `notifyRoleAssigned` | `role_assigned` | Role Assigned | User | "You have been assigned the role of [Role] for [Command]" |
| 88 | `notifyServiceNumberAssigned` | `service_number_assigned` | Service Number Assigned | Establishment user | "Service number [SN] assigned to [Name]" |
| 89 | `notifyServiceNumberAssignedToOfficer` | `service_number_assigned` | Service Number Assigned | Officer | "Your service number [SN] has been assigned" |
| 90 | `notifyServiceNumbersForEmail` | `service_numbers_ready_email` | Service Numbers Ready for Email | ICT users | "[N] officer(s) assigned service numbers and ready for email creation" |
| 91 | `notifyEmailChanged` | `email_changed` | Email Address Changed | Officer (both old & new email) | "Your email address has been changed from [Old] to [New]" |
| 92 | `notifyOfficerDeceased` | `officer_deceased` | Officer Deceased | Accounts + Welfare + HRD users | "Officer [Name] ([SN]) has been reported deceased. Date: [Date]" |

---

### Total: 92 Notification Types → ALL go to Mobile

**Implementation approach:** Modify the `notify()` method in `NotificationService.php` to add a 3rd delivery channel (FCM push) alongside the existing in-app record + email. This way, **every notification in the system automatically goes to the mobile app** with zero individual changes needed.

```php
// In NotificationService::notify() — add after line 38:
// 3. Send push notification to mobile device
if ($user->fcm_token) {
    SendMobilePushNotificationJob::dispatch($user, $notification);
}
```

### Notification Data Model (Existing — No Changes)

```
notifications:
  - user_id (FK → users)
  - notification_type (string — one of 92 types listed above)
  - title (string)
  - message (text)
  - entity_type (string: 'leave_application', 'emolument', 'pass_application', etc.)
  - entity_id (integer)
  - is_read (boolean)
  - created_at / updated_at
```

### Existing API Endpoints

- `GET /api/v1/notifications` — Fetch all notifications for the authenticated user
- `PATCH /api/v1/notifications/{id}/read` — Mark single notification as read
- `PATCH /api/v1/notifications/read-all` — Mark all notifications as read

### Deep-Link Routing (Notification → Screen)

When a push notification is tapped, the app deep-links to the relevant screen:

| `entity_type` | Maps to Mobile Screen |
|---------------|----------------------|
| `leave_application` | Leave Application Detail |
| `pass_application` | Pass Application Detail |
| `emolument` | Emolument Detail |
| `manning_request` | Manning Request Detail |
| `staff_order` | Staff Order Detail / Posting History |
| `officer` | Officer Profile |
| `quarter` | Quarter / Accommodation Detail |
| `quarter_request` | Quarter Request Detail |
| `query` | Query Detail |
| `duty_roster` | Duty Roster Detail |
| `internal_staff_order` | Internal Staff Order Detail |
| `fleet_request` | Fleet Request Detail |
| `fleet_vehicle` | Vehicle Detail |
| `role_assignment` | Profile / Roles |
| `training_result` | Training Record |
| `course` | Course Detail |
| `pharmacy_procurement` | Pharmacy Procurement Detail |
| `pharmacy_requisition` | Pharmacy Requisition Detail |

---

## 13. Feature 9 — Additional Features

### A. My Profile

#### Read-Only Fields (from `Officer` model — 68 fields)

| Category | Fields |
|----------|--------|
| **Identity** | Service number, appointment number, initials, surname, sex |
| **Rank & Grade** | Substantive rank, salary grade level, display rank (with T suffix for Transport) |
| **Dates** | Date of birth, date of first appointment, date of present appointment |
| **Location** | Present station (command), date posted to station, state of origin, LGA, geopolitical zone |
| **Personal** | Marital status, residential address, permanent home address |
| **Qualifications** | Entry qualification, discipline, additional qualification |
| **Banking** | Bank name, account number, sort code, PFA name, RSA number |
| **Status** | Unit, quartered status, interdicted, suspended, ongoing investigation |
| **Retirement** | Calculated retirement date, retirement type (AGE/SVC), days until retirement, time in service |

#### Editable Fields

| Field | Approval Required? |
|-------|-------------------|
| Phone number | No — instant update |
| Emergency contact | No — instant update |
| Next of kin | **Yes** — goes to Welfare for approval |
| Profile picture | No — instant upload (mandatory after promotion) |
| Bank details | **Yes** — goes to Accounts for approval via `AccountChangeRequest` |
| Education qualifications | **Yes** — goes to HRD for approval via `EducationChangeRequest` |

#### Profile Picture After Promotion

The web app enforces a mandatory profile picture update after promotion via the `profile_picture.post_promotion` middleware. The mobile app must:

1. Check `officer.profile_picture_required_after_promotion_at`
2. If set and `profile_picture_updated_at` is older → show forced update screen
3. Block all other actions until photo is updated

#### API Endpoints Used

- `GET /api/v1/auth/me` — Full profile
- `PATCH /api/v1/officers/{id}` — Update editable fields
- `POST /officer/profile/update-picture` (web route, needs API version)
- `POST /officer/account-change` — Bank detail change request
- `POST /officer/next-of-kin` — Next of kin change request
- `POST /officer/education-requests` — Education qualification request

### B. Transport (T&L Officer View)

#### What Officers Can See

| Data | Source |
|------|--------|
| Vehicles allocated to them | `FleetVehicleAssignment` where `assigned_to_officer_id` = officer |
| Vehicle details | `FleetVehicle` — type, plate number, make, model, year, status |
| Assignment details | Command, assigned date, status (active/returned) |

#### What Officers Can Do

| Action | Details |
|--------|---------|
| View allocated vehicle | Type, plate, command, status |
| Submit vehicle request | Via `FleetRequest` (New Vehicle, Re-allocation, Repair) |
| View request status | Track pending/approved/rejected fleet requests |

#### What Officers CANNOT Do (Web-Only)

- Allocate vehicles (CC T&L only)
- Issue/return vehicles (CD role only)
- Manage fleet inventory

#### Mobile Screens

1. **My Vehicle** — Shows allocated vehicle details or "No vehicle currently assigned"
2. **Vehicle Request** — Submit request for new vehicle, re-allocation, or repair
3. **Request History** — Track fleet request statuses

### C. Requests & Approvals Dashboard

A unified screen showing **all** of the officer's pending items:

| Request Type | Status Options |
|-------------|---------------|
| Leave applications | Pending → Minuted → Approved / Rejected |
| Pass applications | Pending → Minuted → Approved / Rejected |
| Account change requests | Pending → Approved / Rejected |
| Next of kin changes | Pending → Approved / Rejected |
| Education qualification requests | Pending → Approved / Rejected |
| Quarter requests | Pending → Approved / Rejected |
| Fleet requests | Submitted → In Review → Approved / Rejected |

#### For Approvers (Dual-Role Officers)

Officers who also have approval roles (e.g., Staff Officer, DC Admin, Area Controller) can:

- View pending items requiring their action
- Approve / reject / minute from the app
- Add comments/reasons

### D. My Reports

Officers can generate and view:

| Report | Data Source |
|--------|-----------|
| Personal service summary | Officer profile + postings + courses |
| Training record | `OfficerCourse` entries |
| Leave history | `LeaveApplication` records |
| Pass history | `PassApplication` records |
| Vehicle allocation history | `FleetVehicleAssignment` records |
| Emolument history | `Emolument` records with status |
| Posting history | `OfficerPosting` records |

Features:
- View in-app
- Download as PDF
- Share (read-only link)

### E. Health & Pharmacy (Officer View)

#### What Officers Can See

| Data | Source |
|------|--------|
| Drug catalog | `PharmacyDrug` — name, category, dosage forms |
| Prescriptions (if dispensed to them) | `PharmacyRequisition` items where command pharmacist dispensed |

> **Note:** The existing pharmacy module focuses on procurement/requisition/stock management at the organizational level. Officer-facing prescription/treatment history views need to be built as new API endpoints that query the pharmacy data for the officer's command.

#### New API Endpoints Needed

| Endpoint | Purpose |
|----------|---------|
| `GET /api/v1/pharmacy/my-prescriptions` | Officer's dispensed medications |
| `GET /api/v1/pharmacy/my-appointments` | Officer's medical appointments |

---

## 14. Role-Based Access Matrix

Officers can have **dual roles** (e.g., an officer who is also a Staff Officer). The mobile app adapts the UI based on the user's roles.

| Feature | Officer | Staff Officer | DC Admin | Area Controller | Assessor | Validator | Auditor | Accounts |
|---------|---------|--------------|----------|----------------|----------|-----------|---------|----------|
| Apply for Pass | ✅ Submit | ✅ Minute | ✅ Approve/Reject | ❌ | ❌ | ❌ | ❌ | ❌ |
| Apply for Leave | ✅ Submit | ✅ Minute | ✅ Approve/Reject | View | ❌ | ❌ | ❌ | ❌ |
| Raise Emolument | ✅ Submit | ❌ | ❌ | ✅ Validate | ✅ Assess | ✅ Validate | ✅ Audit | ✅ Process |
| Command Chat | ✅ Member | ✅ Admin | ✅ Member | ✅ Member | ✅ Member | ✅ Member | ✅ Member | ✅ Member |
| Management Chat | AC+ only | ✅ Admin | ✅ Member | ✅ Member | ❌ | ❌ | ❌ | ❌ |
| Create Groups | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Push Notifications | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| My Profile | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Transport View | ✅ View | ✅ View | ✅ View | ✅ View | ✅ View | ✅ View | ✅ View | ❌ |
| My Reports | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Health/Pharmacy | ✅ View | ✅ View | ✅ View | ✅ View | ✅ View | ✅ View | ✅ View | ❌ |

---

## 15. Screen-by-Screen Breakdown

### Bottom Navigation (5 tabs)

```
┌─────────┬────────────┬──────────┬──────────┬──────────┐
│  Home   │  Requests  │   Chat   │  Notif.  │ Profile  │
│   🏠    │    📋      │   💬     │   🔔     │   👤     │
└─────────┴────────────┴──────────┴──────────┴──────────┘
```

### Tab 1: Home Dashboard

```
┌─────────────────────────────────┐
│  Welcome, ASC II M. Nanjwan    │
│  Service No: NCS12345          │
│  Zone D HQ                      │
├─────────────────────────────────┤
│                                 │
│  ┌──────────┐  ┌──────────┐   │
│  │ Apply    │  │ Apply    │   │
│  │ for Pass │  │ for Leave│   │
│  │   🎫     │  │   🏖️     │   │
│  └──────────┘  └──────────┘   │
│                                 │
│  ┌──────────┐  ┌──────────┐   │
│  │  Raise   │  │   My     │   │
│  │Emolument │  │ Vehicle  │   │
│  │   💰     │  │   🚗     │   │
│  └──────────┘  └──────────┘   │
│                                 │
│  ┌──────────┐  ┌──────────┐   │
│  │  Health  │  │   My     │   │
│  │   🏥     │  │ Reports  │   │
│  └──────────┘  │   📊     │   │
│                 └──────────┘   │
│                                 │
│  ── Quick Stats ──             │
│  Pending Requests: 3           │
│  Unread Notifications: 7       │
│  Active Leave: None            │
└─────────────────────────────────┘
```

### Tab 2: My Requests

```
┌─────────────────────────────────┐
│  My Requests                    │
│  ┌───────────────────────────┐ │
│  │ Filter: All | Leave | Pass│ │
│  │ | Emolument | Vehicle     │ │
│  └───────────────────────────┘ │
│                                 │
│  ┌───────────────────────────┐ │
│  │ 🟡 Annual Leave           │ │
│  │ 1 Mar - 5 Mar 2026       │ │
│  │ Status: PENDING           │ │
│  │ Submitted: 24 Feb 2026   │ │
│  └───────────────────────────┘ │
│                                 │
│  ┌───────────────────────────┐ │
│  │ 🟢 Emolument 2026        │ │
│  │ Status: PROCESSED         │ │
│  │ Processed: 20 Feb 2026   │ │
│  └───────────────────────────┘ │
│                                 │
│  ┌───────────────────────────┐ │
│  │ 🔴 Pass Application       │ │
│  │ 15 Jan - 17 Jan 2026     │ │
│  │ Status: REJECTED          │ │
│  │ Reason: Insufficient...   │ │
│  └───────────────────────────┘ │
│                                 │
│  [+ New Request]               │
└─────────────────────────────────┘
```

### Tab 3: Chat Rooms

```
┌─────────────────────────────────┐
│  Chat Rooms                     │
│  ┌───────────────────────────┐ │
│  │ 🏢 Zone D HQ Command     │ │
│  │ "Parade at 0700 tomorrow" │ │
│  │ 2 min ago                 │ │
│  └───────────────────────────┘ │
│                                 │
│  ┌───────────────────────────┐ │
│  │ 🏛️ Zone D Management     │ │
│  │ "Meeting rescheduled..."  │ │
│  │ 15 min ago                │ │
│  └───────────────────────────┘ │
│                                 │
│  ┌───────────────────────────┐ │
│  │ 👥 Logistics Team         │ │
│  │ "Document attached..."    │ │
│  │ 1 hour ago                │ │
│  └───────────────────────────┘ │
│                                 │
│  [+ Create Group]              │
└─────────────────────────────────┘
```

### Tab 4: Notifications

```
┌─────────────────────────────────┐
│  Notifications         Mark All│
│                                 │
│  🟢 Today                      │
│  ┌───────────────────────────┐ │
│  │ 💰 Emolument Processed    │ │
│  │ Your 2026 emolument has   │ │
│  │ been processed for payment│ │
│  │ 10:30 AM                  │ │
│  └───────────────────────────┘ │
│                                 │
│  ┌───────────────────────────┐ │
│  │ ✅ Leave Approved          │ │
│  │ Annual leave approved     │ │
│  │ (1 Mar - 5 Mar)          │ │
│  │ 9:15 AM                   │ │
│  └───────────────────────────┘ │
│                                 │
│  🟡 Yesterday                  │
│  ┌───────────────────────────┐ │
│  │ 📢 Command Update         │ │
│  │ New duty roster published │ │
│  │ 4:00 PM                   │ │
│  └───────────────────────────┘ │
└─────────────────────────────────┘
```

### Tab 5: My Profile

```
┌─────────────────────────────────┐
│  ┌─────────┐                   │
│  │  Photo  │  ASC II M. Nanjwan│
│  │  (tap   │  NCS12345         │
│  │  to     │  Zone D HQ       │
│  │ change) │                    │
│  └─────────┘                   │
├─────────────────────────────────┤
│  Service Details               │
│  ├ Rank: ASC II                │
│  ├ Unit: Operations            │
│  ├ Grade Level: 08             │
│  ├ DOB: 15 Mar 1985           │
│  ├ Date of 1st Appt: 2010     │
│  ├ Time in Service: 16y 0m    │
│  └ Retirement: 2045 (AGE)     │
├─────────────────────────────────┤
│  Contact Details  [Edit]       │
│  ├ Phone: 08012345678          │
│  └ Emergency: 08098765432      │
├─────────────────────────────────┤
│  Banking Details               │
│  ├ Bank: First Bank            │
│  ├ Account: ****5678           │
│  └ [Request Change]            │
├─────────────────────────────────┤
│  Next of Kin                   │
│  ├ Name: Jane Doe              │
│  └ [Request Update]            │
├─────────────────────────────────┤
│  Posting History  ▶            │
│  Training Record  ▶            │
│  Quarter Status   ▶            │
├─────────────────────────────────┤
│  ⚙️ Settings                   │
│  🔐 Change Password            │
│  📱 Biometric Login             │
│  🚪 Logout                     │
└─────────────────────────────────┘
```

---

## 16. API Endpoints Required per Feature

### Summary: What's Ready vs. What's Needed

| Category | Ready (Existing API) | Needs Building |
|----------|---------------------|----------------|
| Auth | ✅ Login, Logout, Me, Refresh | 🔨 Register Device, Biometric Challenge, Deactivate Device |
| Pass | ✅ Submit, List, Approve | — |
| Leave | ✅ Submit, List, Show, Minute, Approve | — |
| Emolument | ✅ Submit, List, Show, Assess, Validate | 🔨 API for resubmit, audit, process-payment |
| Chat | ✅ Rooms, Messages, Send, Create | 🔨 Add/Remove Members, Attachments, Leave Group |
| Notifications | ✅ List, Mark Read, Mark All | 🔨 FCM token registration |
| Profile | ✅ Get Officer, Update Officer | 🔨 API for profile picture upload, account change request, next-of-kin change, education change |
| Transport | Partial — Fleet data exists | 🔨 Officer-facing vehicle view endpoint |
| Reports | ❌ | 🔨 Personal report generation API |
| Pharmacy | ❌ | 🔨 Officer prescription/appointment view |

---

## 17. Development Phases

### Phase 1: Foundation (Weeks 1–2)

| Task | Details |
|------|---------|
| Project setup | Expo + TypeScript + React Navigation + Redux |
| Auth flow | Login → Biometric → Auto-logout → Token management |
| Design system | Color palette, typography, component library |
| API layer | Axios instance, RTK Query base config, interceptors |
| Navigation | Bottom tabs + stack navigators for each tab |

### Phase 2: Core Features (Weeks 3–5)

| Task | Details |
|------|---------|
| **Apply for Pass** | Form, list, detail, status tracking |
| **Apply for Leave** | Form with conditional fields, list, detail |
| **Raise Emolument** | Form with pre-population, full timeline view |
| **My Profile** | Read-only view, editable fields, photo upload |
| **My Requests** | Unified dashboard with filters |

### Phase 3: Chat & Notifications (Weeks 6–8)

| Task | Details |
|------|---------|
| **Command Chat Room** | Auto-join, real-time messages via WebSocket |
| **Management Chat Room** | Rank-based visibility |
| **Group Creation** | WhatsApp-style group creation and management |
| **Staff Officer Admin** | Add/remove members UI |
| **Push Notifications** | FCM integration, deep-linking, notification center |

### Phase 4: Transport & Reports (Weeks 9–10)

| Task | Details |
|------|---------|
| **My Vehicle** | View allocated vehicle, detail screen |
| **Vehicle Requests** | Submit fleet request, track status |
| **My Reports** | Personal summaries, PDF generation, share |

### Phase 5: Health & Approvals (Weeks 11–12)

| Task | Details |
|------|---------|
| **Health & Pharmacy** | Officer prescription view, appointment reminders |
| **Dual-Role Approvals** | Staff Officer minute, DC Admin approve, Assessor assess (for dual-role users) |
| **Command Updates** | Official announcement feed with attachments |

### Phase 6: Polish & Deploy (Weeks 13–14)

| Task | Details |
|------|---------|
| Security hardening | Device binding, remote deactivation, encryption |
| Performance | Caching, lazy loading, image optimization |
| Testing | Unit tests, integration tests, UAT |
| App Store submission | iOS App Store + Google Play |
| OTA update pipeline | EAS Update for rapid fixes |

---

## 18. Folder Structure

```
ncs-employee-mobile/
├── app.json                    # Expo config
├── App.tsx                     # Entry point
├── src/
│   ├── api/
│   │   ├── baseQuery.ts        # Axios + Sanctum token
│   │   ├── authApi.ts          # Login, logout, me
│   │   ├── emolumentApi.ts     # Emolument CRUD
│   │   ├── leaveApi.ts         # Leave CRUD
│   │   ├── passApi.ts          # Pass CRUD
│   │   ├── chatApi.ts          # Chat rooms & messages
│   │   ├── notificationApi.ts  # Notifications
│   │   ├── officerApi.ts       # Profile, postings
│   │   ├── fleetApi.ts         # Vehicle & fleet
│   │   └── pharmacyApi.ts      # Health & pharmacy
│   ├── components/
│   │   ├── ui/                 # Buttons, cards, inputs, badges
│   │   ├── forms/              # FormField, DatePicker, FilePicker
│   │   ├── chat/               # MessageBubble, RoomCard, MemberList
│   │   └── notifications/      # NotificationCard, PushHandler
│   ├── navigation/
│   │   ├── RootNavigator.tsx   # Auth check → App or Login
│   │   ├── BottomTabs.tsx      # 5-tab navigation
│   │   ├── HomeStack.tsx       # Home → Forms → Details
│   │   ├── RequestStack.tsx    # Requests → Detail
│   │   ├── ChatStack.tsx       # Rooms → Room → Info
│   │   ├── NotifStack.tsx      # Notifications → Detail
│   │   └── ProfileStack.tsx    # Profile → Edit → Settings
│   ├── screens/
│   │   ├── auth/
│   │   │   ├── LoginScreen.tsx
│   │   │   └── BiometricScreen.tsx
│   │   ├── home/
│   │   │   └── DashboardScreen.tsx
│   │   ├── pass/
│   │   │   ├── PassListScreen.tsx
│   │   │   ├── PassApplyScreen.tsx
│   │   │   └── PassDetailScreen.tsx
│   │   ├── leave/
│   │   │   ├── LeaveListScreen.tsx
│   │   │   ├── LeaveApplyScreen.tsx
│   │   │   └── LeaveDetailScreen.tsx
│   │   ├── emolument/
│   │   │   ├── EmolumentListScreen.tsx
│   │   │   ├── EmolumentRaiseScreen.tsx
│   │   │   └── EmolumentDetailScreen.tsx
│   │   ├── chat/
│   │   │   ├── ChatRoomsScreen.tsx
│   │   │   ├── ChatRoomScreen.tsx
│   │   │   ├── CreateGroupScreen.tsx
│   │   │   └── RoomInfoScreen.tsx
│   │   ├── notifications/
│   │   │   └── NotificationListScreen.tsx
│   │   ├── profile/
│   │   │   ├── ProfileScreen.tsx
│   │   │   ├── EditContactScreen.tsx
│   │   │   ├── PostingHistoryScreen.tsx
│   │   │   └── SettingsScreen.tsx
│   │   ├── transport/
│   │   │   ├── MyVehicleScreen.tsx
│   │   │   └── VehicleRequestScreen.tsx
│   │   ├── health/
│   │   │   └── HealthScreen.tsx
│   │   └── reports/
│   │       └── MyReportsScreen.tsx
│   ├── store/
│   │   ├── store.ts            # Redux store config
│   │   ├── authSlice.ts        # Auth state
│   │   └── appSlice.ts         # App-wide state
│   ├── hooks/
│   │   ├── useAuth.ts          # Auth hook
│   │   ├── useBiometric.ts     # Biometric hook
│   │   └── useNotifications.ts # Push notification hook
│   ├── utils/
│   │   ├── constants.ts        # API URL, colors, ranks
│   │   ├── helpers.ts          # Date formatting, status colors
│   │   └── storage.ts          # Secure storage wrapper
│   └── theme/
│       ├── colors.ts           # NCS color palette
│       ├── typography.ts       # Font sizes, families
│       └── spacing.ts          # Consistent spacing scale
├── assets/
│   ├── images/                 # App icons, logos
│   └── fonts/                  # Custom fonts
├── package.json
├── tsconfig.json
└── eas.json                    # EAS Build config
```

---

## 19. Testing Strategy

| Level | Tool | Coverage |
|-------|------|----------|
| **Unit Tests** | Jest + React Native Testing Library | Components, hooks, utils |
| **API Integration** | MSW (Mock Service Worker) | API calls, error handling |
| **E2E Tests** | Detox | Full user flows (login → apply → approve) |
| **Manual Testing** | Expo Go on physical devices | UI/UX, biometrics, push notifications |
| **Performance** | React Native Performance Monitor | Render times, memory usage |

### Test Scenarios

1. **Auth Flow:** Login → Biometric setup → Auto-logout → Re-login with biometric
2. **Pass Application:** Submit → Staff Officer minute → DC Admin approve → Officer sees notification
3. **Leave Application:** Submit → Conditional fields (maternity/sick) → Full approval chain
4. **Emolument:** Raise → All 6 workflow steps → Rejection → Resubmit → Final processing
5. **Chat:** Auto-join command room → Send message → Create group → Share document
6. **Notifications:** Receive push → Tap → Deep-link to correct screen
7. **Dual Role:** Officer who is also Staff Officer → See both officer and approver views

---

## 20. Deployment & Distribution

### Build Commands

| Step | Command |
|------|---------|
| Install dependencies | `npm install` |
| Run locally (Expo Go) | `npx expo start` |
| Build iOS | `eas build -p ios --profile production` |
| Build Android | `eas build -p android --profile production` |
| Submit to App Store | `eas submit -p ios` |
| Submit to Play Store | `eas submit -p android` |
| OTA Update (no rebuild) | `eas update --branch production` |

### CI/CD Pipeline

```
Push to main → GitHub Actions → EAS Build → App Store / Play Store
                    │
                    └──── OTA Update (for non-native changes)
```

### Environment Configuration

| Environment | API Base URL | Notes |
|-------------|-------------|-------|
| Development | `http://localhost:8000/api/v1` | Local Laravel server |
| Staging | `https://staging.mis.ncsportal.com/api/v1` | Test server |
| Production | `https://mis.ncsportal.com/api/v1` | Live server |

---

## Summary

This document covers the **complete development plan** for the NCS Employee Mobile App:

- **9 core features** mapped directly to existing web app functionality
- **65+ existing API endpoints** ready for consumption
- **~15 new API endpoints** needed for mobile-specific features
- **14-week development timeline** across 6 phases
- **Full role-based access** supporting dual-role officers
- **Enterprise-grade security** with biometrics, device binding, and encrypted storage
- **Real-time communications** replacing WhatsApp with formal, auditable chat rooms
- **Push notifications** at every workflow step ensuring no officer misses critical updates

The mobile app does **not** introduce new business logic — it simply provides a responsive, secure, officer-friendly interface to the existing MIS system.

---

*End of Document*
