# Mobile API Gaps — Implemented (Option B)

> **Date:** 24 February 2026  
> **Reference:** [NCS_EMPLOYEE_MOBILE_APP_README.md](./NCS_EMPLOYEE_MOBILE_APP_README.md) Section 16 — "What's Ready vs. What's Needed"

This document lists the **backend API endpoints and changes** that were added so the mobile app can consume them from day one. The web controllers already contained the business logic; the API layer wraps that logic with JSON responses.

---

## 1. Pass Applications

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/pass-applications/{id}` | **NEW** — Get pass application detail (for mobile detail screen). Returns full record with officer and approval info. |

---

## 2. Emoluments

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/emoluments/{id}/resubmit` | **NEW** — Resubmit a rejected emolument (after validation rejection). Resets to RAISED and notifies assessors. |

---

## 3. Auth & Push Notifications

| Change | Description |
|--------|-------------|
| **Login** | `POST /api/v1/auth/login` now accepts optional `push_token` in body. If provided, it is stored on the user for push notifications. |
| **Migration** | `users.push_token` column added (nullable string, 512 chars) for Expo push token. |
| **Register token** | `POST /api/v1/notifications/register-token` — Body: `{ "token": "ExponentPushToken[...]" }`. Validates format and saves on current user. |
| **Token status** | `GET /api/v1/notifications/token-status` — Returns `{ "has_token": true/false }` for current user. |
| **All notifications → mobile** | An **observer** on `Notification` (`App\Observers\NotificationObserver`) runs on every `Notification::created()`. When the user has a valid Expo `push_token`, it dispatches `SendExpoPushNotificationJob`. This ensures **every** in-app notification (from `NotificationService::notify()` or from any other code path that creates a `Notification` — leave, pass, emolument, quarters, postings, retirement, APER, etc.) also goes to the mobile app via Expo Push API (`https://exp.host/--/api/v2/push/send`). Deep-link data: `notification_id`, `notification_type`, `entity_type`, `entity_id`. |

---

## 4. Profile Picture

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/v1/officers/{id}/profile-picture` | **NEW** — Upload profile picture (multipart: `profile_picture` image, max 2MB). Officer can only update own. Respects onboarding and post-promotion picture requirement. |

---

## 5. Chat (Rooms & Members)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/chat/rooms/{id}/members` | **NEW** — List room members (officer_id, user_id, name, service_number). |
| `POST` | `/api/v1/chat/rooms/{id}/members` | **NEW** — Add members. Body: `{ "officer_ids": [1,2,3] }`. Staff Officer only (same command). |
| `DELETE` | `/api/v1/chat/rooms/{id}/members/{userId}` | **NEW** — Remove member (Staff Officer) or leave room (current user). |
| `PUT` | `/api/v1/chat/rooms/{id}` | **NEW** — Update room name/description. UNIT (custom) rooms only. |
| `POST` | `/api/v1/chat/rooms/{id}/leave` | **NEW** — Leave a custom/unit room (soft leave: is_active = false). |

**Note:** Chat API was aligned with existing schema: `chat_room_members` use `officer_id` and `chat_room_id`; `chat_messages` use `sender_id` (officer) and `message_text`. Room listing and message send now use officer-based membership.

---

## 6. Profile Change Requests (Account, Next of Kin, Education)

### Account Change Requests

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/account-change-requests` | **NEW** — List current officer's account change requests (paginated). |
| `GET` | `/api/v1/account-change-requests/options` | **NEW** — Banks and PFAs for form dropdowns. |
| `POST` | `/api/v1/account-change-requests` | **NEW** — Submit account/RSA change request (same validation as web). |
| `GET` | `/api/v1/account-change-requests/{id}` | **NEW** — Get one request. |

### Next of Kin Change Requests

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/next-of-kin-requests` | **NEW** — List current officer's next of kin requests. |
| `POST` | `/api/v1/next-of-kin-requests` | **NEW** — Submit add next of kin request. |
| `GET` | `/api/v1/next-of-kin-requests/{id}` | **NEW** — Get one request. |

### Education Change Requests

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/education-change-requests` | **NEW** — List current officer's education change requests. |
| `GET` | `/api/v1/education-change-requests/options` | **NEW** — Institutions, disciplines, qualifications for form. |
| `POST` | `/api/v1/education-change-requests` | **NEW** — Submit education change request (supports optional `documents[]` upload). |
| `GET` | `/api/v1/education-change-requests/{id}` | **NEW** — Get one request. |

---

## Summary

- **Pass:** 1 new endpoint (show).
- **Emolument:** 1 new endpoint (resubmit).
- **Auth / Notifications:** push_token on login, new column, register-token and token-status endpoints.
- **Profile:** 1 new endpoint (profile picture upload).
- **Chat:** 5 new endpoints (members list, add members, remove member, update room, leave).
- **Profile requests:** 3 new API controllers with index, options (where needed), store, show for account-change, next-of-kin, and education-change.

All routes live under `routes/api.php` with the `v1` prefix and `auth:sanctum` middleware. Base URL remains `/api/v1/` (or `/api/mobile` if you configure a separate prefix; see [MOBILE_APP_INTEGRATION_README.md](./MOBILE_APP_INTEGRATION_README.md)).

Next step: **Option A** (start building the Expo/React Native app) or **Option C** (build API + mobile in parallel) using these endpoints.
