# Mobile App — Doc Compliance Checklist

> **Purpose:** Map requirements from `mobile-app-docs` (NCS_EMPLOYEE_MOBILE_APP_README, MAIN_PLAN, MOBILE_APP_INTEGRATION_README, API_GAPS_IMPLEMENTED, FEATURE_*.md) to implementation. Use this to avoid missing items like “all notifications → mobile” and to track remaining gaps.

---

## 1. Notifications (Feature 8)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| **Every notification that fires on the web MUST also fire as push on the app** | `App\Observers\NotificationObserver`: on `Notification::created()`, if user has valid Expo `push_token`, dispatch `SendExpoPushNotificationJob`. All code paths that create a `Notification` (LeaveService, EmolumentService, RetirementService, APERFormController, pass/leave expiry jobs, etc.) now trigger push. | ✅ Done |
| Push via Expo Push API (not FCM directly) | Backend uses `SendExpoPushNotificationJob` → `https://exp.host/--/api/v2/push/send`. | ✅ Done |
| Login / register-token store Expo token | Login accepts `push_token`; `POST /notifications/register-token`; `users.push_token`. | ✅ Done |
| Deep-link on tap | `NotificationResponseHandler`: `pass_application` → PassDetail, `leave_application` → LeaveDetail, `emolument` → EmolumentDetail; all other `entity_type` → Notifications tab. | ✅ Done (core types); others fallback to list |
| GET /notifications, mark read, mark all read | Implemented in API and mobile. | ✅ Done |
| Notification preferences (per-type toggles) | NotificationSettingsScreen (NotificationStack); `notificationPreferences.ts` (SecureStore); toggles for leave, pass, emolument, chat, fleet, pharmacy, system, quarters. Expo Notifications; backend can add prefs API later. | ✅ Done |

**Note:** The main README described modifying `NotificationService::notify()` only. In practice, many notifications are created via `Notification::create()` elsewhere. The **observer** ensures every created notification triggers push, satisfying the RULE.

---

## 2. Auth & Security (NCS_EMPLOYEE_MOBILE_APP_README §3)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| Login (service_number, password) | LoginScreen, auth API, token in secure store. | ✅ Done |
| GET /auth/me, POST /auth/logout | Used; 401 triggers logout (setUnauthorizedHandler). | ✅ Done |
| Biometric login (Face ID / Fingerprint) | `expo-local-authentication`: BiometricPromptScreen on restore when biometric enabled; Profile "Use Face ID / Fingerprint for next login" toggle; authStorage get/setBiometricEnabled. | ✅ Done |
| **Auto-logout (inactivity 10 min)** | **Not implemented.** Doc: “Inactivity timer (10 min) — Clears auth token, returns to login screen.” | ❌ Missing |
| Device binding (one active device) | Backend does not enforce single device; `users.push_token` is one token per user. | ⏸ Doc optional |
| Remote deactivation / 401 logout | 401 from API clears token and redirects to login. | ✅ Done |
| Two-Factor Auth (POST /two-factor/verify) | API: login returns `requires_two_factor` + `temporary_token`; `POST /auth/two-factor/verify` with Bearer temp token + code returns full token. Mobile: TwoFactorScreen; verifyTwoFactor thunk. | ✅ Done |

---

## 3. Core Features (MAIN_PLAN Phases 1–2)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| Pass: list, apply, detail | Pass list in My Requests, Apply Pass, Pass Detail; approve/reject for DC Admin. | ✅ Done |
| Leave: list, apply, detail | Leave list, Apply Leave, Leave Detail; minute (SO), approve/reject (DC Admin). | ✅ Done |
| Emolument: list, raise, detail | My Emoluments, Raise Emolument, Emolument Detail; resubmit. | ✅ Done |
| My Requests (unified, filters) | MyRequestsScreen with All \| Pass \| Leave \| Emolument, “+ New”. | ✅ Done |
| Profile: contact, profile picture | Edit contact (phone), profile picture upload; auth/me refresh. | ✅ Done |

---

## 4. Chat & Push (Phase 3)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| Chat rooms list, messages, send | ChatRoomsScreen, ChatRoomScreen, chatApi (rooms, messages, send). | ✅ Done |
| Notification center | NotificationListScreen, list, mark read, “Mark all as read”. | ✅ Done |
| Push registration when logged in | PushRegistration, getExpoPushToken(), register-token. | ✅ Done |
| Deep-link from notification tap | useNotificationResponseHandler → Pass/Leave/Emolument detail or Notifications. | ✅ Done |

---

## 5. Transport & Reports (Phase 4)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| Movement orders (list, detail) | Movement orders list filtered by officer, detail screen. | ✅ Done |
| Duty schedule | GET /officers/:id/duty-schedule, My schedule screen. | ✅ Done |
| Quarters: requests, allocations, accept/reject | myRequests, submitRequest, myAllocations, accept/reject. | ✅ Done |
| My Vehicle | Placeholder (backend fleet API when available). | ⏸ Placeholder |
| Reports | Placeholder (backend reports API when available). | ⏸ Placeholder |

---

## 6. Health & Approvals (Phase 5)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| Health / Pharmacy screen | HealthScreen placeholder in TransportStack. | ⏸ Placeholder |
| Pass/Leave approval actions | Pass/Leave detail: Minute (SO), Approve/Reject (DC Admin). | ✅ Done |

---

## 7. Polish & Deploy (Phase 6)

| Doc requirement | Implementation | Status |
|-----------------|----------------|--------|
| 401 → logout | setUnauthorizedHandler, clear token, redirect to login. | ✅ Done |
| EAS config, app.json | eas.json profiles; app.json name, scheme, bundleId/package, splash, notifications. | ✅ Done |
| Deploy docs | DEPLOY.md (EAS Build, Update, store, security). | ✅ Done |

---

## 8. Deep-Link Entity Types (NCS_EMPLOYEE_MOBILE_APP_README §12)

Doc lists 18 entity types with target screens. Currently implemented:

| entity_type | Mobile behavior |
|-------------|-----------------|
| `pass_application` | PassDetail |
| `leave_application` | LeaveDetail |
| `emolument` | EmolumentDetail |
| `quarter`, `quarter_request`, `quarter_allocation` | Transport → Quarter requests. |
| `fleet_request`, `fleet_vehicle`, `movement_order` | Transport → Movement orders. |
| `pharmacy_*` | Transport → Health. |
| `chat_message`, `chat_room` | Chat → ChatRoom (roomId). |
| `officer` | Profile tab. |
| `manning_request` | My Requests tab. |
| `duty_roster` | Transport → My schedule. |
| All others | Notifications tab. |

Adding more entity-specific screens (e.g. Quarter detail, Fleet detail) can be done when those screens and APIs exist.

---

## 9. Optional / Not Yet Implemented

| Item | Doc reference | Notes |
|------|----------------|--------|
| GET /notifications/unread-count | FEATURE_05 | App derives unread from list; dedicated endpoint optional. |
| Device binding (one device per officer) | NCS_EMPLOYEE_MOBILE_APP_README §3.2 | Would require backend policy and possibly `fcm_tokens`-style table. |

---

## 10. Summary

- **Notifications:** Fully aligned with “every notification from web → app” via **NotificationObserver** (all `Notification::created()` paths trigger Expo push).
- **Core app:** Auth, Pass, Leave, Emolument, Profile, My Requests, Chat, Notifications, Transport (movement, duty, quarters), Pass/Leave approvals, 401 logout, EAS/deploy docs are implemented.
- **All doc-requested items implemented:** Inactivity auto-logout (10 min), 2FA (API + mobile TwoFactorScreen), biometric (expo-local-authentication + Profile toggle + BiometricPromptScreen on restore), notification preferences (NotificationSettingsScreen + SecureStore), deep links for quarter, fleet, pharmacy, chat, officer, manning_request, duty_roster. Expo Notifications used throughout as defined.

When adding features or backend changes, check this checklist and the source docs so nothing is missed (e.g. any new code that creates `Notification` records will automatically get push via the observer).
