import React, { useEffect } from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { View, ActivityIndicator, StyleSheet } from 'react-native';
import { useAppDispatch, useAppSelector } from '../hooks/redux';
import { restoreSession, logout, clearTwoFactor } from '../store/authSlice';
import { setAuthTokenGetter, setUnauthorizedHandler } from '../api/client';
import { store } from '../store/store';
import { LoginScreen } from '../screens/auth/LoginScreen';
import { TwoFactorScreen } from '../screens/auth/TwoFactorScreen';
import { BiometricPromptScreen } from '../screens/auth/BiometricPromptScreen';
import { BottomTabs } from './BottomTabs';
import { NotificationStack } from './NotificationStack';
import { TransportStack } from './TransportStack';
import { ApprovalsStack } from './ApprovalsStack';
import { PushRegistration } from '../components/PushRegistration';
import { colors } from '../theme';

const Stack = createNativeStackNavigator();

export function RootNavigator() {
  const dispatch = useAppDispatch();
  const { token, temporaryToken, biometricRequired, biometricUnlocked, isRestoring } = useAppSelector((s) => s.auth);

  useEffect(() => {
    setAuthTokenGetter(() => store.getState().auth.token);
    setUnauthorizedHandler(() => dispatch(logout()));
    dispatch(restoreSession());
    return () => setUnauthorizedHandler(null);
  }, [dispatch]);

  if (isRestoring) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" color={colors.light.primary} />
      </View>
    );
  }

  const showTwoFactor = !token && temporaryToken;
  const showMain = token && (!biometricRequired || biometricUnlocked);
  const showBiometric = token && biometricRequired && !biometricUnlocked;

  return (
    <>
      {showMain ? <PushRegistration /> : null}
      <Stack.Navigator screenOptions={{ headerShown: false }}>
        {showMain ? (
          <>
            <Stack.Screen name="Main" component={BottomTabs} />
            <Stack.Screen name="Notifications" component={NotificationStack} />
            <Stack.Screen name="Transport" component={TransportStack} />
            <Stack.Screen name="Approvals" component={ApprovalsStack} />
          </>
        ) : showBiometric ? (
          <Stack.Screen name="Biometric" component={BiometricPromptScreen} />
        ) : showTwoFactor ? (
          <Stack.Screen name="TwoFactor">
            {() => (
              <TwoFactorScreen
                temporaryToken={temporaryToken}
                onCancel={() => dispatch(clearTwoFactor())}
              />
            )}
          </Stack.Screen>
        ) : (
          <Stack.Screen name="Login" component={LoginScreen} />
        )}
      </Stack.Navigator>
    </>
  );
}

const styles = StyleSheet.create({
  loading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: colors.light.background,
  },
});
