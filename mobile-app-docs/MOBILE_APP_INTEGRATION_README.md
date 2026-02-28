# Backend API Integration Guide – Mobile & Web

This document describes how to connect **any mobile app** or **any website** to the backend API: base URL, authentication, push notifications (for mobile), CORS, and required headers. Use it for new mobile apps, new web apps, or any client that talks to this backend.

---

## Table of Contents

1. [API base URL & environment](#api-base-url--environment)
2. [Required headers](#required-headers)
3. [Authentication](#authentication)
4. [Web app connection](#web-app-connection)
5. [Mobile app connection](#mobile-app-connection)
6. [Push notifications (mobile, Expo)](#push-notifications-mobile-expo)
7. [Backend requirements](#backend-requirements)
8. [CORS configuration](#cors-configuration)
9. [Checklist for a new client](#checklist-for-a-new-client)

---

## API base URL & environment

Replace `https://your-domain.com` with your actual backend domain.

| Environment   | Base URL (example) |
|---------------|--------------------|
| Production    | `https://your-domain.com/api/mobile` |
| Staging       | Same as production, or your staging URL |
| Development   | `http://localhost:8000/api/mobile` or your dev server |

**Mobile development (same backend):**

- iOS Simulator: `http://127.0.0.1:8000/api/mobile`
- Android Emulator: `http://10.0.2.2:8000/api/mobile`
- Physical device: `http://<your-machine-ip>:8000/api/mobile`

All API paths in this document are relative to that base URL (e.g. `/login` means `{BASE_URL}/login`).

---

## Required headers

Every request must include:

```http
Content-Type: application/json
Accept: application/json
```

For **authenticated** requests, add:

```http
Authorization: Bearer <token>
```

Optional but recommended (helps backend identify client):

```http
X-Platform: web
X-App-Version: 1.0.0
```

For mobile apps, use `X-Platform: react-native` (or `ios` / `android`) and your app version.

---

## Authentication

The backend uses **Laravel Sanctum**. Clients receive a Bearer token on login and send it on subsequent requests.

### Login

- **Method:** `POST`
- **URL:** `{BASE_URL}/login`
- **Headers:** `Content-Type: application/json`, `Accept: application/json`
- **Body:**
  ```json
  {
    "service_number": "string",
    "password": "string",
    "push_token": "optional, for mobile only"
  }
  ```
- **Success (200):**
  ```json
  {
    "success": true,
    "message": "Login successful",
    "token": "<sanctum_plain_text_token>",
    "user": { "id", "username", "firstname", "lastname", "service_number", "role", ... }
  }
  ```
- **Failure (401):**
  ```json
  { "success": false, "message": "Invalid service number or password" }
  ```

**Web:** Store the token (e.g. in memory, sessionStorage, or httpOnly cookie if the backend sets it). Send it as `Authorization: Bearer <token>` on every API call.

**Mobile:** Store the token (e.g. AsyncStorage) and send it the same way. You may also send `push_token` in the login body so the backend can store it for push notifications.

### Logout

- **Method:** `POST`
- **URL:** `{BASE_URL}/logout`
- **Headers:** `Authorization: Bearer <token>`

### Token refresh

If the backend exposes a refresh endpoint (e.g. `POST {BASE_URL}/refresh` with `Authorization: Bearer <token>`), use it when the API returns 401 and then retry the request with the new token.

---

## Web app connection

Any website (React, Vue, Angular, plain JS, etc.) can use the same API.

1. **Base URL**  
   Set your API base URL from environment (e.g. `https://your-domain.com/api/mobile` in production).

2. **CORS**  
   The backend must allow your web origin. See [CORS configuration](#cors-configuration). If you host the site on a new domain, add that origin to the backend CORS config.

3. **Login**  
   `POST {BASE_URL}/login` with `service_number` and `password`. Do not send `push_token` from web unless the backend supports it for web push.

4. **Authenticated requests**  
   Send `Authorization: Bearer <token>` and the [required headers](#required-headers) on every request. You can set `X-Platform: web` and `X-App-Version: 1.0.0` (or your app version).

5. **Token storage**  
   Prefer secure options: httpOnly cookie (if backend sets it), or in-memory + refresh. Avoid storing long-lived tokens in `localStorage` on shared devices.

6. **Logout**  
   Call `POST {BASE_URL}/logout` with the current token, then clear the token on the client.

No Expo or mobile-specific setup is required for a web-only client.

---

## Mobile app connection

Any mobile app (Expo/React Native, or other) can use the same API.

1. **Base URL**  
   Use the same base URL as in [API base URL & environment](#api-base-url--environment), switching by environment (development vs production).

2. **Headers**  
   Same as [Required headers](#required-headers). Use `X-Platform: react-native` (or `ios` / `android`) and your app version.

3. **Login**  
   `POST {BASE_URL}/login` with `service_number`, `password`, and optionally `push_token` (Expo push token) so the backend can store it for push.

4. **Token storage**  
   Store `token` and `user` (e.g. AsyncStorage). Attach `Authorization: Bearer <token>` to all API requests (e.g. via axios/fetch interceptors).

5. **Push token registration**  
   After login, if you have an Expo push token, register it: `POST {BASE_URL}/notifications/register-token` with body `{ "token": "ExponentPushToken[...]" }`. See [Push notifications](#push-notifications-mobile-expo).

Recommended versions (for compatibility with this backend’s mobile contract):

- Expo SDK: 53.x  
- React: 19.x  
- React Native: 0.79.x  
- expo-notifications: ~0.31.x  
- axios: ^1.6.x  

---

## Push notifications (mobile, Expo)

Only relevant for **mobile** clients that need push. The backend sends push via **Expo’s push service**, not FCM/APNs directly.

### Flow

1. **Mobile app:** Get an Expo push token (physical device only) using your Expo project ID.
2. **Mobile app:** After login, send that token to the backend: `POST {BASE_URL}/notifications/register-token` with `{ "token": "ExponentPushToken[xxxxxx]" }`.
3. **Backend:** Saves the token on the user (e.g. `users.push_token`) and later sends notifications by calling Expo’s API: `https://exp.host/--/api/v2/push/send`.

### Token format

- Must start with `ExponentPushToken[`. The backend rejects other formats.
- Obtain the token with `expo-notifications`: `getExpoPushTokenAsync({ projectId })` where `projectId` is your Expo project ID (from your Expo app config / EAS).

### When to register the token

- After successful login.
- On app start if the user is already logged in (e.g. auto-login).
- Optionally include `push_token` in the login request body so the backend can store it at login time.

### Backend notification endpoints (reference)

| Endpoint | Method | Auth | Purpose |
|----------|--------|------|---------|
| `{BASE_URL}/notifications/register-token` | POST | Bearer | Register/update Expo push token |
| `{BASE_URL}/notifications/send` | POST | Bearer | Send push to given user IDs |
| `{BASE_URL}/notifications/token-status` | GET | Bearer | Check if current user has a token |
| `{BASE_URL}/notifications/test` | POST | Bearer | Send a test notification to current user |

### Expo app config (relevant parts)

In your Expo app config (e.g. `app.json`):

- Set `expo.projectId` and `expo.extra.eas.projectId` to your Expo project ID.
- Add the `expo-notifications` plugin.
- iOS: `UIBackgroundModes`: `["remote-notification"]`.
- Android: include `POST_NOTIFICATIONS` (and any other needed permissions).

Example plugin:

```json
[
  "expo-notifications",
  {
    "icon": "./assets/icon.png",
    "color": "#ffffff",
    "mode": "production",
    "androidMode": "default",
    "androidCollapsedTitle": "New Notification"
  }
]
```

---

## Backend requirements

For the backend to support **any** website or mobile app:

- **Laravel** with **Sanctum** for API authentication.
- **Users table:** a `push_token` column (nullable string) if you want mobile push; store only Expo tokens (`ExponentPushToken[...]`).
- **Routes:** API under a consistent prefix (e.g. `/api/mobile`). All paths in this doc are relative to that base.
- **Login:** Accept `service_number`, `password`, optional `push_token`; return `token` and `user`; create Sanctum token (e.g. `$user->createToken('MobileApp')->plainTextToken`).
- **Push:** To send notifications, backend calls `https://exp.host/--/api/v2/push/send` with `to` (token or array of tokens), `title`, `body`, `data`, `sound`, `priority`.

CORS must allow the origins of every web app that will call the API (see below).

---

## CORS configuration

The backend must allow requests from your **web** and **mobile** clients.

- **Paths:** e.g. `api/*`, `sanctum/csrf-cookie` (match your API and Sanctum routes).
- **Allowed origins:** Include every web origin that will call the API (e.g. `https://your-website.com`, `https://app.your-domain.com`). For mobile, common patterns are `exp://*`, `capacitor://*`, `ionic://*`, or a wildcard if the backend is designed for public API access.
- **Allowed headers:** At least `Content-Type`, `Authorization`, `Accept`, `Origin`, `X-Requested-With`, `X-Platform`, `X-App-Version`. Add `X-API-Key`, `X-Device-ID`, `X-App-Build` if your clients use them.
- **Credentials:** If the web app sends cookies or auth headers, enable `supports_credentials` and list specific origins (avoid `*` with credentials in most browsers).

When you add a **new website**, add its origin to the backend CORS allowed origins so the browser allows API requests.

---

## Checklist for a new client

Use this whether you are connecting a **new website** or a **new mobile app**.

**Every client (web or mobile):**

- [ ] Use the correct API base URL for the environment (production/staging/development).
- [ ] Send `Content-Type: application/json` and `Accept: application/json` on every request.
- [ ] Login via `POST {BASE_URL}/login` with `service_number` and `password`.
- [ ] Store the returned `token` and send `Authorization: Bearer <token>` on all authenticated requests.
- [ ] Call `POST {BASE_URL}/logout` when the user logs out.
- [ ] If the backend uses CORS, ensure your web origin is allowed (for web apps).

**Web only:**

- [ ] Set `X-Platform: web` (or similar) and your app version if the backend uses it.
- [ ] Prefer secure token storage (e.g. avoid long-lived tokens in `localStorage` on shared devices).

**Mobile only:**

- [ ] Set `X-Platform: react-native` (or `ios` / `android`) and your app version.
- [ ] If using push: get Expo push token, register it with `POST {BASE_URL}/notifications/register-token` after login, and use the same Expo project ID in your app config.

After that, your website or app will be using the same configuration and behavior as any other client of this backend.
