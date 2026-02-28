# NCS Employee Mobile App

React Native (Expo) app for NCS officers. Built according to **mobile-app-docs** in the parent repo.

## Stack

- Expo (SDK 54) + TypeScript
- React Navigation (Stack + Bottom Tabs)
- Redux Toolkit (auth) + Axios (API)
- expo-secure-store (token storage)

## UI & theme

- **Site colors** from the NCS Employee Portal (pisportal) are used across the app:
  - **Primary:** `#088a56` (green) — buttons, headers, active states
  - **Primary dark:** `#066c43` — hover/pressed
  - **Danger:** `#dc3545` — errors, logout
  - **Neutrals:** background `#f8faf9`, surface white, borders and text grays
- Theme lives in `src/theme/` (colors, typography, spacing). All screens use it for a consistent, professional look.

## Setup

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **API URL (environment-aware)**  
   The app picks the API URL automatically:
   - **Development** (Expo Go / dev build): `http://127.0.0.1:8000/api/v1`  
   - **Production** (release build): `http://mis.ncsportal.com/api/v1`  
   Override with `EXPO_PUBLIC_API_URL` in `.env` or EAS env when needed.

3. **Start backend**
   - From the parent repo (`pisportal`): `php artisan serve`

4. **Start app**
   ```bash
   npx expo start
   ```
   - **Android:** `npx expo start --android` (opens on emulator/device directly)  
   - **iOS:** `npx expo start --ios`  
   Or press `i` / `a` in the Expo terminal.

## Testing: local vs production

The app supports **both local and production** and chooses the API URL by environment:
- **Development build** (Expo Go, `npx expo start`): uses dev URL `http://127.0.0.1:8000/api/v1` unless you set `EXPO_PUBLIC_API_URL` in `.env`.
- **Production build** (EAS Build release): uses `http://mis.ncsportal.com/api/v1` unless overridden in EAS env.

**Test locally:** Run `php artisan serve` in `pisportal`, then in `ncs-employee-mobile`: iOS Simulator uses the default; Android Emulator needs `.env` with `EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api/v1`; physical device needs your machine IP (e.g. `http://192.168.1.100:8000/api/v1`). Start with `npx expo start` and log in with a valid service number/password.

**Test production:** Set in `.env`: `EXPO_PUBLIC_API_URL=https://your-domain.com/api/v1`, restart Expo. For EAS Build, set the URL in `eas.json` (build profile `env`) or EAS Secrets.

| Environment | Auto URL | Override (`.env` or EAS) |
|-------------|----------|---------------------------|
| Development (Expo Go) | `http://127.0.0.1:8000/api/v1` | `EXPO_PUBLIC_API_URL=http://10.0.2.2:8000/api/v1` (Android emu) or your IP (device) |
| Production (release build) | `http://mis.ncsportal.com/api/v1` | Set in EAS Build env if different |

## Login

Use **service number** and **password** (same as web). Token is stored in secure storage and restored on next launch.

## Structure

- `src/api` — API client and auth API
- `src/navigation` — Root (auth check) and Bottom Tabs (Home, My Requests, Chat, Notifications, Profile)
- `src/screens` — Auth, Home, Requests, Chat, Notifications, Profile (Phase 1 placeholders for Requests/Chat/Notifications)
- `src/store` — Redux store, auth slice, secure storage
- `src/hooks` — `useAppDispatch`, `useAppSelector`
- `src/utils` — Constants (API URL)

## Plan

See **../mobile-app-docs/MAIN_PLAN.md** for phases and build order. All phases 1–6 are in place (Foundation through Polish & deploy). For building and store submission, see **DEPLOY.md**.
