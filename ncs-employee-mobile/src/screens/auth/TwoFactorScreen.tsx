import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { useAppDispatch } from '../../hooks/redux';
import { verifyTwoFactor } from '../../store/authSlice';
import { getExpoPushToken } from '../../utils/pushNotifications';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type Props = {
  temporaryToken: string;
  onCancel: () => void;
};

export function TwoFactorScreen({ temporaryToken, onCancel }: Props) {
  const dispatch = useAppDispatch();
  const [code, setCode] = useState('');
  const [useRecovery, setUseRecovery] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);

  const handleVerify = async () => {
    const value = code.trim();
    if (!value) return;
    setError(null);
    setLoading(true);
    try {
      const pushToken = await getExpoPushToken();
      await dispatch(
        verifyTwoFactor({
          temporary_token: temporaryToken,
          code: useRecovery ? undefined : value,
          recovery_code: useRecovery ? value : undefined,
          push_token: pushToken ?? undefined,
        })
      ).unwrap();
    } catch (e: unknown) {
      const msg = e && typeof e === 'object' && 'message' in e ? String((e as { message: string }).message) : 'Invalid code. Try again.';
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <View style={styles.card}>
        <Text style={styles.title}>Two-factor verification</Text>
        <Text style={styles.subtitle}>
          {useRecovery
            ? 'Enter one of your 10 recovery codes'
            : 'Enter the 6-digit code from your authenticator app'}
        </Text>

        <TextInput
          style={styles.input}
          placeholder={useRecovery ? 'Recovery code' : '000000'}
          placeholderTextColor={colors.textMuted}
          value={code}
          onChangeText={(t) => { setCode(t); setError(null); }}
          keyboardType={useRecovery ? 'default' : 'number-pad'}
          maxLength={useRecovery ? 10 : 6}
          autoCapitalize="none"
          autoCorrect={false}
          editable={!loading}
        />

        {error ? <Text style={styles.error}>{error}</Text> : null}

        <TouchableOpacity
          style={[styles.button, loading && styles.buttonDisabled]}
          onPress={handleVerify}
          disabled={loading}
          activeOpacity={0.85}
        >
          {loading ? (
            <ActivityIndicator color={colors.textOnPrimary} />
          ) : (
            <Text style={styles.buttonText}>Verify</Text>
          )}
        </TouchableOpacity>

        <TouchableOpacity style={styles.link} onPress={() => { setUseRecovery(!useRecovery); setCode(''); setError(null); }}>
          <Text style={styles.linkText}>
            {useRecovery ? 'Use authenticator code instead' : 'Use a recovery code instead'}
          </Text>
        </TouchableOpacity>

        <TouchableOpacity style={styles.cancel} onPress={onCancel} disabled={loading}>
          <Text style={styles.cancelText}>Cancel</Text>
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    padding: spacing.xl,
    backgroundColor: colors.backgroundDark,
  },
  card: {
    backgroundColor: colors.surface,
    borderRadius: 16,
    padding: spacing.xl,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.15,
    shadowRadius: 24,
    elevation: 6,
  },
  title: {
    fontSize: fontSizes['2xl'],
    fontWeight: fontWeights.bold,
    color: colors.text,
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: fontSizes.sm,
    color: colors.textSecondary,
    marginBottom: spacing.xl,
  },
  input: {
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    paddingHorizontal: spacing.base,
    paddingVertical: spacing.md,
    fontSize: fontSizes.base,
    marginBottom: spacing.base,
    color: colors.text,
    backgroundColor: colors.borderLight,
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
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.8,
  },
  buttonText: {
    color: colors.textOnPrimary,
    fontSize: fontSizes.base,
    fontWeight: fontWeights.semibold,
  },
  link: {
    marginTop: spacing.base,
    alignItems: 'center',
  },
  linkText: {
    color: colors.primary,
    fontSize: fontSizes.sm,
  },
  cancel: {
    marginTop: spacing.lg,
    alignItems: 'center',
  },
  cancelText: {
    color: colors.textMuted,
    fontSize: fontSizes.sm,
  },
});
