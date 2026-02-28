# NCS Employee Mobile — Deploy & Build

## Prerequisites

- Node.js 18+
- [EAS CLI](https://docs.expo.dev/build/setup/): `npm install -g eas-cli`
- Expo account: `eas login`
- For store submission: Apple Developer / Google Play Console accounts

## Environment

- Set `EXPO_PUBLIC_API_URL` for each build profile (e.g. production API base).
- In `eas.json`, `build.production.env` and `build.preview.env` can override; otherwise use `.env` or EAS Secrets.

## EAS Build

1. **Link project** (first time):
   ```bash
   cd ncs-employee-mobile
   eas build:configure
   ```
   Then set `extra.eas.projectId` in `app.json` if EAS created a project.

2. **Development / preview** (internal testing):
   ```bash
   eas build --profile preview --platform android
   eas build --profile preview --platform ios
   ```

3. **Production** (store-ready):
   ```bash
   eas build --profile production --platform all
   ```

4. **Increment version** before production:
   - Bump `version` in `app.json` (or use `autoIncrement: true` in `eas.json` for build number).

## EAS Update (OTA)

For JS-only updates without app store review:

1. Configure: `eas update:configure`
2. Publish: `eas update --branch production --message "Fix for X"`

Requires EAS Update setup and compatible runtime (Expo Go or dev client with updates).

## Store submission

1. **iOS (App Store)**  
   - Fill `submit.production.ios.appleId` and `ascAppId` in `eas.json`, or pass when submitting.  
   - Submit: `eas submit --platform ios --latest`

2. **Android (Google Play)**  
   - Use a service account key; set path in `eas.json` or EAS Secrets.  
   - Submit: `eas submit --platform android --latest`

## Security checklist

- [x] 401 responses clear token and redirect to login (`setUnauthorizedHandler` in API client).
- [x] Token stored in secure storage (expo-secure-store).
- [ ] Use HTTPS for `EXPO_PUBLIC_API_URL` in production.
- [ ] Do not commit `.env` with production URLs or secrets; use EAS Secrets for build-time env.

## Plan reference

See **../mobile-app-docs/MAIN_PLAN.md** Phase 6 for the full polish & deploy checklist.
