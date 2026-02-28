import React, { useEffect, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ActivityIndicator, KeyboardAvoidingView, Platform, Alert, ScrollView } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { emolumentApi } from '../../api/emolumentApi';
import { useAppSelector, useAppDispatch } from '../../hooks/redux';
import { refreshUser } from '../../store/authSlice';
import { useThemeColor, spacing } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Props = NativeStackScreenProps<RequestStackParamList, 'EmolumentRaise'>;

export function EmolumentRaiseScreen({ navigation, route }: Props) {
  const { user } = useAppSelector((s) => s.auth);
  const dispatch = useAppDispatch();
  const themeColors = useThemeColor();
  const officer = user?.officer;

  const [timeline, setTimeline] = useState<{ id: number, year?: number } | null>(null);

  const [bankName, setBankName] = useState('');
  const [bankAccount, setBankAccount] = useState('');
  const [pfaName, setPfaName] = useState('');
  const [rsaPin, setRsaPin] = useState('');
  const [notes, setNotes] = useState('');

  const [loading, setLoading] = useState(false);
  const [loadTimeline, setLoadTimeline] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Refresh user data so that newly added profile fields (bank, pfa) are populated
    dispatch(refreshUser());

    emolumentApi.getActiveTimeline().then((res) => {
      if (res.success && res.data) setTimeline(res.data);
      setLoadTimeline(false);
    }).catch(() => setLoadTimeline(false));
  }, []);

  useEffect(() => {
    if (user?.officer) {
      setBankName((user.officer as { bank_name?: string }).bank_name ?? '');
      setBankAccount((user.officer as { bank_account_number?: string }).bank_account_number ?? '');
      setPfaName((user.officer as { pfa_name?: string }).pfa_name ?? '');
      setRsaPin((user.officer as { rsa_number?: string }).rsa_number ?? '');
    }
  }, [user?.officer]);

  React.useLayoutEffect(() => {
    if (route.params?.fromDashboard) {
      navigation.setOptions({
        headerLeft: () => (
          <TouchableOpacity onPress={() => navigation.navigate('Home' as any)} style={{ paddingRight: 20, paddingVertical: 8, marginLeft: Platform.OS === 'ios' ? -15 : 0 }}>
            <Ionicons name="arrow-back" size={24} color="#ffffff" />
          </TouchableOpacity>
        ),
      });
    }
  }, [navigation, route.params]);

  const submit = async () => {
    if (!timeline) { setError('No active emolument timeline'); return; }
    if (!bankName || !bankAccount || !pfaName || !rsaPin) { setError('Bank and PFA details required'); return; }

    setError(null);
    setLoading(true);

    try {
      const res = await emolumentApi.raise({
        timeline_id: timeline.id,
        bank_name: bankName,
        bank_account_number: bankAccount,
        pfa_name: pfaName,
        rsa_pin: rsaPin,
        notes: notes || undefined,
      });
      if (res.success && res.data) {
        Alert.alert(
          'Emolument Raised',
          'Your emolument has been submitted successfully for processing.',
          [{ text: 'OK', onPress: () => navigation.goBack() }]
        );
      } else {
        setError(res.message ?? 'Submission failed');
      }
    } catch (e: unknown) {
      setError((e as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Network error occurred');
    } finally {
      setLoading(false);
    }
  };

  if (loadTimeline) {
    return (
      <View style={[styles.centered, { backgroundColor: '#ffffff' }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  if (!timeline) {
    return (
      <View style={[styles.centered, { backgroundColor: '#ffffff', padding: spacing.xl }]}>
        <Ionicons name="alert-circle-outline" size={64} color="#94a3b8" style={{ marginBottom: spacing.md }} />
        <Text style={{ fontSize: 16, color: '#475569', textAlign: 'center', fontWeight: '500' }}>
          No active emolument period. Try again later.
        </Text>
      </View>
    );
  }

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">

        <View style={[styles.infoBanner, { backgroundColor: '#f0fdf4', borderLeftColor: themeColors.primary }]}>
          <Ionicons name="information-circle" size={24} color={themeColors.primary} />
          <View style={styles.infoTextContainer}>
            <Text style={[styles.infoTitle, { color: '#166534' }]}>Raise Emolument</Text>
            <Text style={[styles.infoDesc, { color: '#166534' }]}>
              Submit your annual emolument for processing. Your financial details are pulled automatically.
            </Text>
          </View>
        </View>

        <View style={styles.card}>
          <Text style={styles.label}>Bank Details</Text>
          <View style={styles.readOnlyContainer}>
            <View style={styles.readOnlyRow}>
              <Ionicons name="business-outline" size={16} color="#94a3b8" />
              <Text style={styles.readOnlyText}>{bankName || 'Not Provided'}</Text>
            </View>
            <View style={[styles.readOnlyRow, { marginTop: 8 }]}>
              <Ionicons name="wallet-outline" size={16} color="#94a3b8" />
              <Text style={styles.readOnlyText}>{bankAccount || 'Not Provided'}</Text>
            </View>
          </View>

          <Text style={styles.label}>PFA Details</Text>
          <View style={styles.readOnlyContainer}>
            <View style={styles.readOnlyRow}>
              <Ionicons name="shield-checkmark-outline" size={16} color="#94a3b8" />
              <Text style={styles.readOnlyText}>{pfaName || 'Not Provided'}</Text>
            </View>
            <View style={[styles.readOnlyRow, { marginTop: 8 }]}>
              <Ionicons name="keypad-outline" size={16} color="#94a3b8" />
              <Text style={styles.readOnlyText}>RSA: {rsaPin || 'Not Provided'}</Text>
            </View>
          </View>

          <Text style={styles.label}>Additional Notes (Optional)</Text>
          <View style={[styles.inputContainer, styles.textAreaContainer]}>
            <TextInput
              style={[styles.input, styles.textArea]}
              placeholder="Enter any additional information..."
              placeholderTextColor="#94a3b8"
              value={notes}
              onChangeText={setNotes}
              multiline
              textAlignVertical="top"
              editable={!loading}
            />
          </View>

          {error ? (
            <View style={styles.errorBox}>
              <Ionicons name="warning" size={16} color="#ef4444" />
              <Text style={styles.errorText}>{error}</Text>
            </View>
          ) : null}

          <TouchableOpacity
            style={[styles.button, { backgroundColor: themeColors.primary, shadowColor: themeColors.primary }, loading && styles.buttonDisabled]}
            onPress={submit}
            disabled={loading}
          >
            {loading ? <ActivityIndicator color="#ffffff" /> : <Text style={styles.buttonText}>Submit Emolument</Text>}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#ffffff' },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  scrollContent: { padding: spacing.lg, paddingBottom: spacing['3xl'] },

  infoBanner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    padding: spacing.md,
    borderRadius: 10,
    borderLeftWidth: 4,
    marginBottom: spacing.lg,
  },
  infoTextContainer: { marginLeft: spacing.sm, flex: 1 },
  infoTitle: { fontSize: 14, fontWeight: '700', marginBottom: 2 },
  infoDesc: { fontSize: 12, lineHeight: 18 },

  card: { backgroundColor: '#ffffff', borderRadius: 12, padding: 0 },

  label: { fontSize: 12, fontWeight: '600', color: '#1e293b', marginBottom: 4, marginTop: spacing.md },

  readOnlyContainer: {
    backgroundColor: '#f1f5f9',
    borderRadius: 8,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  readOnlyRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  readOnlyText: {
    marginLeft: 8,
    fontSize: 14,
    color: '#475569',
    fontWeight: '500',
  },

  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f8faf9',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 8,
    paddingHorizontal: spacing.md,
    height: 44,
  },
  input: { flex: 1, fontSize: 14, height: '100%', color: '#1e293b' },

  textAreaContainer: { height: 80, paddingVertical: spacing.sm, alignItems: 'flex-start' },
  textArea: { minHeight: 60, height: '100%' },

  errorBox: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#fef2f2',
    padding: spacing.sm,
    borderRadius: 8,
    marginTop: spacing.md,
    gap: spacing.sm,
  },
  errorText: { color: '#ef4444', fontSize: 13, fontWeight: '500', flex: 1 },

  button: {
    borderRadius: 10,
    height: 44,
    justifyContent: 'center',
    alignItems: 'center',
    marginTop: spacing.lg,
    elevation: 2,
    shadowOffset: { width: 0, height: 3 },
    shadowOpacity: 0.2,
    shadowRadius: 6
  },
  buttonDisabled: { opacity: 0.7, elevation: 0 },
  buttonText: { color: '#ffffff', fontSize: 15, fontWeight: '700' },
});
