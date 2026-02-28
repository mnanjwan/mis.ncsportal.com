import { useEffect, useRef, useCallback } from 'react';
import { AppState, AppStateStatus } from 'react-native';
import { useAppDispatch } from './redux';

const INACTIVITY_MS = 10 * 60 * 1000; // 10 minutes per NCS_EMPLOYEE_MOBILE_APP_README §3.2

/**
 * Inactivity auto-logout: after 10 minutes of no app interaction, clear token and redirect to login.
 * Resets on any navigation/focus; uses AppState to pause when app is in background.
 */
export function useInactivityLogout(logout: () => void) {
  const lastActivityRef = useRef<number>(Date.now());
  const appStateRef = useRef<AppStateStatus>(AppState.currentState);
  const logoutRef = useRef(logout);
  logoutRef.current = logout;

  const resetTimer = useCallback(() => {
    lastActivityRef.current = Date.now();
  }, []);

  useEffect(() => {
    const interval = setInterval(() => {
      const now = Date.now();
      if (appStateRef.current !== 'active') return;
      if (now - lastActivityRef.current >= INACTIVITY_MS) {
        logoutRef.current();
      }
    }, 60 * 1000); // check every minute
    return () => clearInterval(interval);
  }, []);

  useEffect(() => {
    const sub = AppState.addEventListener('change', (next) => {
      appStateRef.current = next;
      if (next === 'active') resetTimer();
    });
    return () => sub.remove();
  }, [resetTimer]);

  return resetTimer;
}
