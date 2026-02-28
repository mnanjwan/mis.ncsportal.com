# NCS Employee Mobile App — Main Plan

> **Single plan we build by.** Sourced from `NCS_EMPLOYEE_MOBILE_APP_README.md`, `MOBILE_APP_INTEGRATION_README.md`, and `API_GAPS_IMPLEMENTED.md`.

---

## 1. Tech Stack

| Layer | Choice |
|-------|--------|
| Framework | React Native (Expo) |
| Language | TypeScript |
| State & API | Redux Toolkit + RTK Query |
| Navigation | React Navigation v6 (Stack + Bottom Tabs) |
| Push | Expo Notifications (backend uses Expo Push API) |
| Secure storage | expo-secure-store |
| Biometrics | expo-local-authentication (Phase 1 optional) |
| Backend | Laravel Sanctum — base URL `/api/v1` |

---

## 2. Build Order (Phases)

| Phase | Scope | Deliverable |
|-------|--------|-------------|
| **1. Foundation** | Project, auth, API layer, nav | Expo app with login, token storage, 5-tab shell |
| **2. Core features** | Pass, Leave, Emolument, Profile, My Requests | List/apply/detail screens for each |
| **3. Chat & push** | Rooms, messages, push, deep links | Chat UI, push registration, notification center |
| **4. Transport & reports** | Vehicle, fleet requests, reports | My Vehicle, requests, reports screens |
| **5. Health & approvals** | Pharmacy view, dual-role actions | Health screen, Staff Officer/DC Admin actions |
| **6. Polish & deploy** | Security, tests, store submission | Hardening, EAS Build, OTA |

---

## 3. Phase 1 Checklist (What We Build First)

- [x] Expo app init (TypeScript)
- [x] Folder structure: `src/api`, `src/navigation`, `src/screens`, `src/store`, `src/hooks`, `src/utils`
- [x] API base URL from env; Axios client with Bearer token interceptor
- [x] Auth API: login (service_number, password, push_token?), logout, me, register-token
- [x] Auth slice + secure storage (token, user)
- [x] RootNavigator: if no token → LoginScreen; else → Main (tabs)
- [x] LoginScreen: service_number, password, submit → store token + user
- [x] Main: Bottom Tabs (Home, My Requests, Chat, Notifications, Profile) + placeholder screens
- [x] Logout clears token and redirects to login

---

## 3b. Phase 2 Checklist (Core features)

- [x] Pass: list (in My Requests), apply, detail screens + RequestStack
- [x] Leave: list (in My Requests), apply, detail screens + RequestStack
- [x] Emolument: list (in My Requests), raise, detail screens + RequestStack
- [x] My Requests: unified list, filters (All | Pass | Leave | Emolument), "+ New" → Pass/Leave/Emolument apply
- [x] Profile: edit contact (phone), profile picture upload; ProfileStack (Profile + EditContact)
- [x] Auth: refreshUser (auth/me) after profile/contact updates; officerApi (PATCH officers/:id, POST profile-picture)

---

## 3c. Phase 3 Checklist (Chat & push)

- [x] Chat API: chatApi (rooms, messages, sendMessage)
- [x] Chat UI: ChatRoomsScreen (list), ChatRoomScreen (messages + send); ChatStack
- [x] Notifications API: notificationApi (list, markAsRead, markAllAsRead)
- [x] Notification center: NotificationListScreen (list, mark read on tap, “Mark all as read”)
- [x] Push: expo-notifications; getExpoPushToken(); PushRegistration (register token when logged in)
- [x] Deep links: NotificationResponseHandler (tap notification → PassDetail / LeaveDetail / EmolumentDetail / Notifications)

---

## 3d. Phase 4 Checklist (Transport & reports)

- [x] movementApi (list with officer_id, show); dutyRosterApi (officerSchedule); quarterApi (myRequests, myAllocations, submitRequest, acceptAllocation, rejectAllocation)
- [x] Transport tab “More” + TransportStack: TransportHome (menu), Movement orders (list + detail), My duty schedule, Quarter requests (+ submit), My allocations (accept/reject), My vehicle (placeholder), Reports (placeholder)
- [x] Movement orders: list filtered by current officer, detail screen
- [x] My schedule: officer duty assignments from GET /officers/:id/duty-schedule
- [x] Quarters: my requests list, submit request, my allocations list, accept/reject allocation
- [x] My vehicle & Reports: placeholder screens (backend fleet/report APIs when available)

---

## 3e. Phase 5 Checklist (Health & approvals)

- [x] Health & pharmacy: HealthScreen (placeholder) in TransportStack; link from Transport home menu
- [x] passApi.approve(id, { action, comments? }); leaveApi.minute(id), leaveApi.approve(id, { action, comments? })
- [x] Pass detail: DC Admin sees Approve / Reject when status is MINUTED; reject requires comments
- [x] Leave detail: Staff Officer sees “Minute” when status is PENDING; DC Admin sees Approve / Reject when status is MINUTED; reject requires comments

---

## 3f. Phase 6 Checklist (Polish & deploy)

- [x] Security: 401 from API triggers logout (setUnauthorizedHandler + clear token, redirect to login)
- [x] EAS: eas.json with development, preview, production profiles; submit placeholders
- [x] app.json: name “NCS Employee”, scheme “ncs-employee”, bundleIdentifier/package, splash/primary color, plugins (notifications)
- [x] Deploy docs: DEPLOY.md with EAS Build, EAS Update, store submission, security checklist

---

## 4. API Contract (Already Live)

- **Base URL:** `https://your-domain.com/api/v1` (or dev `http://IP:8000/api/v1`)
- **Auth:** `POST /auth/login`, `POST /auth/logout`, `GET /auth/me`, optional `POST /notifications/register-token`
- **Pass:** `GET /pass-applications`, `GET /pass-applications/:id`, `POST /officers/:id/pass-applications`
- **Leave:** `GET /leave-types`, `GET /leave-applications`, `GET /leave-applications/:id`, `POST /officers/:id/leave-applications`
- **Emolument:** `GET /emoluments/my-emoluments`, `GET /emoluments/:id`, `POST /emoluments`, `POST /emoluments/:id/resubmit`
- **Profile:** `GET /auth/me`, `PATCH /officers/:id`, `POST /officers/:id/profile-picture`
- **Notifications:** `GET /notifications`, `PATCH /notifications/:id/read`, `PATCH /notifications/read-all`
- **Chat:** `GET /chat/rooms`, `GET /chat/rooms/:id/messages`, `POST /chat/rooms/:id/messages`, members, leave
- **Transport & more:** `GET /movement-orders`, `GET /movement-orders/:id`, `GET /officers/:id/duty-schedule`, `GET /quarters/my-requests`, `POST /quarters/request`, `GET /quarters/my-allocations`, `POST /quarters/allocations/:id/accept`, `POST /quarters/allocations/:id/reject`
- **Approvals:** `POST /pass-applications/:id/approve`, `POST /leave-applications/:id/minute`, `POST /leave-applications/:id/approve`

---

## 5. Key Docs

- **Full spec:** `NCS_EMPLOYEE_MOBILE_APP_README.md`
- **Integration (URLs, headers, push):** `MOBILE_APP_INTEGRATION_README.md`
- **Backend gaps done:** `API_GAPS_IMPLEMENTED.md`

We build according to this plan and these docs.
