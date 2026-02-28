import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ActivityIndicator, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { quarterApi } from '../../api/quarterApi';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

export function QuarterRequestSubmitScreen() {
  const navigation = useNavigation();
  const [preferredType, setPreferredType] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const submit = async () => {
    setError(null);
    setSubmitting(true);
    try {
      const res = await quarterApi.submitRequest({
        preferred_quarter_type: preferredType.trim() || undefined,
      });
      if (res.success) {
        Alert.alert('Success', 'Quarter request submitted.', [
          { text: 'OK', onPress: () => navigation.goBack() },
        ]);
      } else setError('Failed to submit');
    } catch {
      setError('Failed to submit request');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <View style={styles.container}>
      <Text style={styles.label}>Preferred quarter type (optional)</Text>
      <TextInput
        style={styles.input}
        value={preferredType}
        onChangeText={setPreferredType}
        placeholder="e.g. 2-bedroom"
        placeholderTextColor={colors.textMuted}
        editable={!submitting}
      />
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <TouchableOpacity style={[styles.btn, submitting && styles.btnDisabled]} onPress={submit} disabled={submitting}>
        {submitting ? <ActivityIndicator color={colors.textOnPrimary} size="small" /> : <Text style={styles.btnText}>Submit request</Text>}
      </TouchableOpacity>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background, padding: spacing.xl },
  label: { fontSize: fontSizes.sm, fontWeight: fontWeights.medium, color: colors.text, marginBottom: spacing.xs },
  input: { backgroundColor: colors.surface, borderWidth: 1, borderColor: colors.border, borderRadius: 12, padding: spacing.base, fontSize: fontSizes.base, color: colors.text, marginBottom: spacing.base },
  error: { color: colors.danger, fontSize: fontSizes.sm, marginBottom: spacing.base },
  btn: { backgroundColor: colors.primary, paddingVertical: spacing.base, borderRadius: 12, alignItems: 'center' },
  btnDisabled: { opacity: 0.7 },
  btnText: { color: colors.textOnPrimary, fontSize: fontSizes.base, fontWeight: fontWeights.semibold },
});
