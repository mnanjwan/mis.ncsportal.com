import { useEffect, useRef } from 'react';
import { useAppSelector } from '../hooks/redux';
import { authApi } from '../api/authApi';
import { getExpoPushToken } from '../utils/pushNotifications';

/**
 * When user is logged in, obtain Expo push token and register with backend.
 */
export function PushRegistration() {
  const token = useAppSelector((s) => s.auth.token);
  const registered = useRef(false);

  useEffect(() => {
    if (!token) {
      registered.current = false;
      return;
    }
    let cancelled = false;
    (async () => {
      try {
        const pushToken = await getExpoPushToken();
        if (cancelled || !pushToken) return;
        await authApi.registerPushToken(pushToken);
        registered.current = true;
      } catch {
        // ignore
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [token]);

  return null;
}
