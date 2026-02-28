import React, { useEffect, useRef } from 'react';
import { StatusBar } from 'expo-status-bar';
import { NavigationContainer, useNavigationContainerRef } from '@react-navigation/native';
import { SafeAreaProvider } from 'react-native-safe-area-context';
import { Provider, useDispatch, useSelector } from 'react-redux';
import { store } from './src/store/store';
import { RootNavigator } from './src/navigation/RootNavigator';
import { useNotificationResponseHandler } from './src/components/NotificationResponseHandler';
import { useInactivityLogout } from './src/hooks/useInactivityLogout';
import { logout } from './src/store/authSlice';

function AppContent() {
  const navigationRef = useNavigationContainerRef();
  const dispatch = useDispatch();
  const token = useSelector((s: { auth: { token: string | null } }) => s.auth.token);
  const resetInactivity = useInactivityLogout(() => dispatch(logout()));

  useNotificationResponseHandler(navigationRef);

  // Reset 10-min inactivity timer on every navigation when logged in
  useEffect(() => {
    if (!token) return;
    let unsubscribe: (() => void) | undefined;
    const id = setTimeout(() => {
      const nav = navigationRef.current;
      if (nav) unsubscribe = nav.addListener('state', resetInactivity);
    }, 100);
    return () => {
      clearTimeout(id);
      unsubscribe?.();
    };
  }, [token, resetInactivity]);

  return (
    <>
      <NavigationContainer ref={navigationRef}>
        <RootNavigator />
      </NavigationContainer>
      <StatusBar style="auto" />
    </>
  );
}

export default function App() {
  return (
    <Provider store={store}>
      <SafeAreaProvider>
        <AppContent />
      </SafeAreaProvider>
    </Provider>
  );
}
