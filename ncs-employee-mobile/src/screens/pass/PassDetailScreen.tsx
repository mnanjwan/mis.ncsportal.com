import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ActivityIndicator, ScrollView, TouchableOpacity, TextInput, Alert } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { useAppSelector } from '../../hooks/redux';
import { passApi } from '../../api/passApi';
import type { PassApplicationItem } from '../../api/types';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Props = NativeStackScreenProps<RequestStackParamList, 'PassDetail'>;

export function PassDetailScreen({ route, navigation }: Props) {
  const { id } = route.params;
  const themeColors = useThemeColor();
  const roles = useAppSelector((s) => s.auth.user?.roles ?? []);
  const isDcAdmin = roles.includes('2iC Unit Head');
  const [item, setItem] = useState<PassApplicationItem | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [actioning, setActioning] = useState(false);
  const [rejectMode, setRejectMode] = useState(false);
  const [rejectComments, setRejectComments] = useState('');

  const load = () => {
    passApi.get(id).then((res) => {
      if (res.success && res.data) setItem(res.data);
      else setError(res.message ?? 'Failed to load details');
      setLoading(false);
    }).catch(() => { setError('Failed to connect to server'); setLoading(false); });
  };

  useEffect(() => {
    let cancelled = false;
    passApi.get(id).then((res) => {
      if (cancelled) return;
      if (res.success && res.data) setItem(res.data);
      else setError(res.message ?? 'Failed to load details');
      setLoading(false);
    }).catch(() => {
      if (!cancelled) { setError('Failed to connect to server'); setLoading(false); }
    });
    return () => { cancelled = true; };
  }, [id]);

  const handleApprove = () => {
    Alert.alert('Approve Pass', 'Are you sure you want to approve this pass application?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Approve', onPress: async () => {
          setActioning(true);
          try {
            const res = await passApi.approve(id, { action: 'approve' });
            if (res.success) { load(); navigation.goBack(); }
            else Alert.alert('Error', res.message ?? 'Approval failed');
          } catch (e: unknown) {
            Alert.alert('Error', (e as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Network error');
          } finally { setActioning(false); }
        }
      },
    ]);
  };

  const handleReject = () => {
    if (rejectMode) {
      if (!rejectComments.trim()) { Alert.alert('Required', 'Please enter a reason for rejection.'); return; }
      setActioning(true);
      passApi.approve(id, { action: 'reject', comments: rejectComments.trim() }).then((res) => {
        setActioning(false);
        if (res.success) { load(); setRejectMode(false); setRejectComments(''); navigation.goBack(); }
        else Alert.alert('Error', res.message ?? 'Rejection failed');
      }).catch(() => { setActioning(false); Alert.alert('Error', 'Network error'); });
      return;
    }
    setRejectMode(true);
  };

  if (loading) return <View style={[styles.centered, { backgroundColor: themeColors.background }]}><ActivityIndicator size="large" color={themeColors.primary} /></View>;
  if (error || !item) return <View style={[styles.centered, { backgroundColor: themeColors.background }]}><Text style={[styles.error, { color: themeColors.danger }]}>{error ?? 'Pass details not found'}</Text></View>;

  const getStatusColor = () => {
    const s = item.status.toUpperCase();
    if (s === 'APPROVED' || s === 'PROCESSED') return themeColors.success;
    if (s === 'REJECTED') return themeColors.danger;
    if (s === 'PENDING') return '#f59e0b';
    return themeColors.textSecondary;
  };

  const statusColor = getStatusColor();
  const canApproveReject = isDcAdmin && item.status === 'MINUTED';

  return (
    <ScrollView style={[styles.scroll, { backgroundColor: themeColors.background }]} contentContainerStyle={styles.container}>

      {/* Header Info */}
      <View style={styles.headerBox}>
        <View style={[styles.iconCircle, { backgroundColor: themeColors.primaryLight }]}>
          <Ionicons name="card" size={28} color={themeColors.primary} />
        </View>
        <Text style={[styles.headerTitle, { color: themeColors.text }]}>Pass Request</Text>
        <View style={styles.statusBadge}>
          <Ionicons name="ellipse" size={10} color={statusColor} style={{ marginRight: 6 }} />
          <Text style={[styles.statusText, { color: statusColor }]}>{item.status}</Text>
        </View>
      </View>

      <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
        <View style={[styles.row, { borderBottomColor: themeColors.borderLight }]}>
          <Text style={[styles.label, { color: themeColors.textMuted }]}>Start Date</Text>
          <Text style={[styles.value, { color: themeColors.text }]}>{item.start_date}</Text>
        </View>
        <View style={[styles.row, { borderBottomColor: themeColors.borderLight }]}>
          <Text style={[styles.label, { color: themeColors.textMuted }]}>End Date</Text>
          <Text style={[styles.value, { color: themeColors.text }]}>{item.end_date}</Text>
        </View>
        <View style={[styles.row, { borderBottomColor: themeColors.borderLight, borderBottomWidth: item.reason ? StyleSheet.hairlineWidth : 0 }]}>
          <Text style={[styles.label, { color: themeColors.textMuted }]}>Duration</Text>
          <Text style={[styles.value, { color: themeColors.text }]}>{item.number_of_days} days</Text>
        </View>
        {item.reason ? (
          <View style={[styles.row, { borderBottomWidth: item.rejection_reason ? StyleSheet.hairlineWidth : 0, borderBottomColor: themeColors.borderLight, flexDirection: 'column', alignItems: 'flex-start' }]}>
            <Text style={[styles.label, { color: themeColors.textMuted, marginBottom: 4 }]}>Reason</Text>
            <Text style={[styles.value, { color: themeColors.text, lineHeight: 22 }]}>{item.reason}</Text>
          </View>
        ) : null}
        {item.rejection_reason ? (
          <View style={[styles.row, { borderBottomWidth: 0, flexDirection: 'column', alignItems: 'flex-start' }]}>
            <Text style={[styles.label, { color: themeColors.textMuted, marginBottom: 4 }]}>Rejection Reason</Text>
            <Text style={[styles.value, { color: themeColors.danger, lineHeight: 22 }]}>{item.rejection_reason}</Text>
          </View>
        ) : null}
      </View>

      {canApproveReject && !rejectMode && (
        <View style={styles.actions}>
          <TouchableOpacity style={[styles.rejectBtn, { backgroundColor: themeColors.dangerLight }, actioning && styles.btnDisabled]} onPress={handleReject} disabled={actioning}>
            <Ionicons name="close-circle" size={20} color={themeColors.danger} style={{ marginRight: 8 }} />
            <Text style={[styles.rejectBtnText, { color: themeColors.danger }]}>Refuse</Text>
          </TouchableOpacity>
          <TouchableOpacity style={[styles.approveBtn, { backgroundColor: themeColors.success }, actioning && styles.btnDisabled]} onPress={handleApprove} disabled={actioning}>
            <Ionicons name="checkmark-circle" size={20} color="#fff" style={{ marginRight: 8 }} />
            <Text style={styles.approveBtnText}>Approve</Text>
          </TouchableOpacity>
        </View>
      )}

      {canApproveReject && rejectMode && (
        <View style={[styles.rejectForm, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          <View style={styles.rejectHeader}>
            <Ionicons name="alert-circle" size={20} color={themeColors.danger} style={{ marginRight: 8 }} />
            <Text style={[styles.rejectLabel, { color: themeColors.text }]}>Reason for Refusal</Text>
          </View>
          <TextInput
            style={[styles.rejectInput, { backgroundColor: themeColors.background, borderColor: themeColors.border, color: themeColors.text }]}
            value={rejectComments}
            onChangeText={setRejectComments}
            placeholder="Please enter the exact reason..."
            placeholderTextColor={themeColors.textMuted}
            multiline
            editable={!actioning}
          />
          <View style={styles.rejectActions}>
            <TouchableOpacity style={[styles.cancelBtn, { backgroundColor: themeColors.surfaceSecondary }]} onPress={() => { setRejectMode(false); setRejectComments(''); }}>
              <Text style={[styles.cancelBtnText, { color: themeColors.textSecondary }]}>Cancel</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.rejectSubmitBtn, { backgroundColor: themeColors.danger }, actioning && styles.btnDisabled]} onPress={handleReject} disabled={actioning}>
              {actioning ? <ActivityIndicator color="#fff" /> : <Text style={styles.rejectSubmitText}>Confirm Refusal</Text>}
            </TouchableOpacity>
          </View>
        </View>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1 },
  container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  error: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold },

  headerBox: {
    alignItems: 'center',
    marginBottom: spacing.xl,
    marginTop: spacing.md,
  },
  iconCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.md,
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: fontWeights.bold,
    marginBottom: spacing.sm,
  },
  statusBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: 'rgba(0,0,0,0.03)',
  },
  statusText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },

  card: { borderRadius: 16, padding: spacing.xl, borderWidth: 1, elevation: 1, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.05, shadowRadius: 8, marginBottom: spacing.xl },
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: spacing.md, borderBottomWidth: StyleSheet.hairlineWidth },
  label: { fontSize: fontSizes.sm, fontWeight: fontWeights.medium },
  value: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold },

  actions: { flexDirection: 'row', gap: spacing.base },
  approveBtn: { flex: 1, flexDirection: 'row', paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center', elevation: 2, shadowColor: '#10b981', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8 },
  approveBtnText: { color: '#ffffff', fontWeight: fontWeights.bold, fontSize: fontSizes.base },
  rejectBtn: { flex: 1, flexDirection: 'row', paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
  rejectBtnText: { fontWeight: fontWeights.bold, fontSize: fontSizes.base },
  btnDisabled: { opacity: 0.6, elevation: 0 },

  rejectForm: { borderRadius: 16, padding: spacing.xl, borderWidth: 1 },
  rejectHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.md },
  rejectLabel: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold },
  rejectInput: { borderWidth: 1, borderRadius: 12, padding: spacing.base, fontSize: fontSizes.base, minHeight: 100, textAlignVertical: 'top', marginBottom: spacing.xl },
  rejectActions: { flexDirection: 'row', gap: spacing.base },
  cancelBtn: { flex: 1, paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center' },
  cancelBtnText: { fontWeight: fontWeights.bold, fontSize: fontSizes.base },
  rejectSubmitBtn: { flex: 1, paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center', elevation: 2, shadowColor: '#ef4444', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8 },
  rejectSubmitText: { color: '#ffffff', fontWeight: fontWeights.bold, fontSize: fontSizes.base },
});
