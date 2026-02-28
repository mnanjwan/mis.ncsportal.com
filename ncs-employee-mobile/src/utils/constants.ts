/**
 * API and app constants (mobile-app-docs: MOBILE_APP_INTEGRATION_README)
 * Environment-aware: development build (__DEV__) → dev URL; production build → production URL.
 * Override with EXPO_PUBLIC_API_URL in .env or EAS env.
 */

const DEV_API_URL = 'http://127.0.0.1:8000/api/v1';
const PROD_API_URL = 'http://mis.ncsportal.com/api/v1';

const envUrl =
  typeof process !== 'undefined' && process.env?.EXPO_PUBLIC_API_URL?.trim?.();
export const API_BASE_URL = envUrl
  ? String(process.env.EXPO_PUBLIC_API_URL).trim()
  : __DEV__
    ? DEV_API_URL
    : PROD_API_URL;

export const APP_NAME = 'NCS Employee';

/** True when app is in development build (Expo Go / dev client). */
export const IS_DEV_API = __DEV__;
