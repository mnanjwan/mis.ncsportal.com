import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  Alert,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { officerApi } from '../../api/officerApi';
import { refreshUser } from '../../store/authSlice';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type RouteParams = { EditContact: { officerId: number } };

export function EditContactScreen() {
  const navigation = useNavigation();
  const route = useRoute<RouteProp<RouteParams, 'EditContact'>>();
  const officerId = route.params?.officerId;
  const dispatch = useAppDispatch();
  const { user } = useAppSelector((s) => s.auth);
  const [phone, setPhone] = useState('');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const id = officerId ?? user?.officer?.id;
  const currentPhone = user?.officer?.phone_number ?? '';

  useEffect(() => {
    setPhone(currentPhone || '');
  }, [currentPhone]);

  const handleSave = async () => {
    if (id == null) return;
    setError(null);
    setSaving(true);
    try {
      await officerApi.update(id, { phone_number: phone.trim() || undefined });
      await dispatch(refreshUser()).unwrap();
      navigation.goBack();
    } catch (e: unknown) {
      const msg = e && typeof e === 'object' && 'message' in e ? String((e as { message: string }).message) : 'Failed to update';
      setError(msg);
    } finally {
      setSaving(false);
    }
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={100}
    >
      <View style={styles.form}>
        <Text style={styles.label}>Phone number</Text>
        <TextInput
          style={styles.input}
          value={phone}
          onChangeText={setPhone}
          placeholder="e.g. 08012345678"
          placeholderTextColor={colors.textMuted}
          keyboardType="phone-pad"
          editable={!saving}
        />
        {error ? <Text style={styles.error}>{error}</Text> : null}
        <TouchableOpacity
          style={[styles.button, saving && styles.buttonDisabled]}
          onPress={handleSave}
          disabled={saving}
        >
          {saving ? (
            <ActivityIndicator color={colors.textOnPrimary} size="small" />
          ) : (
            <Text style={styles.buttonText}>Save</Text>
          )}
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background, padding: spacing.xl },
  form: { marginTop: spacing.lg },
  label: { fontSize: fontSizes.sm, fontWeight: fontWeights.medium, color: colors.text, marginBottom: spacing.xs },
  input: {
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    padding: spacing.base,
    fontSize: fontSizes.base,
    color: colors.text,
    marginBottom: spacing.base,
  },
  error: { color: colors.danger, fontSize: fontSizes.sm, marginBottom: spacing.base },
  button: {
    backgroundColor: colors.primary,
    paddingVertical: spacing.base,
    borderRadius: 12,
    alignItems: 'center',
  },
  buttonDisabled: { opacity: 0.7 },
  buttonText: { color: colors.textOnPrimary, fontSize: fontSizes.base, fontWeight: fontWeights.semibold },
});
