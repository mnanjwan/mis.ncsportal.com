import React, { useEffect, useState } from 'react';
import { View, Text, TouchableOpacity, StyleSheet, ActivityIndicator } from 'react-native';
import * as LocalAuthentication from 'expo-local-authentication';
import { useAppDispatch } from '../../hooks/redux';
import { setBiometricUnlocked, logout } from '../../store/authSlice';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

export function BiometricPromptScreen() {
  const dispatch = useAppDispatch();
  const [checking, setChecking] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const runBiometric = async () => {
    setError(null);
    setChecking(true);
    try {
      const hasHardware = await LocalAuthentication.hasHardwareAsync();
      const isEnrolled = await LocalAuthentication.isEnrolledAsync();
      if (!hasHardware || !isEnrolled) {
        dispatch(setBiometricUnlocked());
        return;
      }
      const result = await LocalAuthentication.authenticateAsync({
        promptMessage: 'Unlock NCS Employee',
        fallbackLabel: 'Use passcode',
      });
      if (result.success) {
        dispatch(setBiometricUnlocked());
      } else {
        if (result.error === 'user_cancel' || result.error === 'user_fallback') {
          dispatch(logout());
        } else {
          setError('Authentication failed. Try again.');
        }
      }
    } catch {
      setError('Biometric unavailable');
      dispatch(setBiometricUnlocked());
    } finally {
      setChecking(false);
    }
  };

  useEffect(() => {
    runBiometric();
  }, []);

  if (checking && !error) {
    return (
      <View style={styles.container}>
        <ActivityIndicator size="large" color={colors.primary} />
        <Text style={styles.subtitle}>Verifying identity...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Unlock NCS Employee</Text>
      <Text style={styles.subtitle}>Use Face ID or Fingerprint to continue</Text>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <TouchableOpacity style={styles.button} onPress={runBiometric} activeOpacity={0.85}>
        <Text style={styles.buttonText}>Try again</Text>
      </TouchableOpacity>
      <TouchableOpacity style={styles.cancel} onPress={() => dispatch(logout())}>
        <Text style={styles.cancelText}>Sign out</Text>
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: spacing.xl,
    backgroundColor: colors.backgroundDark,
  },
  title: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.textOnPrimary,
    marginBottom: spacing.sm,
  },
  subtitle: {
    fontSize: fontSizes.sm,
    color: colors.textMuted,
    marginBottom: spacing.xl,
  },
  error: {
    color: colors.danger,
    fontSize: fontSizes.sm,
    marginBottom: spacing.md,
  },
  button: {
    backgroundColor: colors.primary,
    borderRadius: 12,
    paddingVertical: spacing.base,
    paddingHorizontal: spacing.xl,
    marginTop: spacing.base,
  },
  buttonText: {
    color: colors.textOnPrimary,
    fontSize: fontSizes.base,
    fontWeight: fontWeights.semibold,
  },
  cancel: {
    marginTop: spacing.lg,
  },
  cancelText: {
    color: colors.textMuted,
    fontSize: fontSizes.sm,
  },
});
