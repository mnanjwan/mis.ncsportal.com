import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ActivityIndicator, ScrollView, TouchableOpacity, TextInput, Alert } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { useAppSelector } from '../../hooks/redux';
import { leaveApi } from '../../api/leaveApi';
import type { LeaveApplicationItem } from '../../api/types';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Props = NativeStackScreenProps<RequestStackParamList, 'LeaveDetail'>;

export function LeaveDetailScreen({ route, navigation }: Props) {
  const { id } = route.params;
  const themeColors = useThemeColor();
  const roles = useAppSelector((s) => s.auth.user?.roles ?? []);
  const isStaffOfficer = roles.includes('Staff Officer');
  const isDcAdmin = roles.includes('DC Admin');
  const [item, setItem] = useState<LeaveApplicationItem | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [actioning, setActioning] = useState(false);
  const [rejectMode, setRejectMode] = useState(false);
  const [rejectComments, setRejectComments] = useState('');

  const load = () => {
    leaveApi.get(id).then((res) => {
      if (res.success && res.data) setItem(res.data);
      else setError(res.message ?? 'Failed to load details');
      setLoading(false);
    }).catch(() => { setError('Failed to connect to server'); setLoading(false); });
  };

  useEffect(() => {
    let cancelled = false;
    leaveApi.get(id).then((res) => {
      if (cancelled) return;
      if (res.success && res.data) setItem(res.data);
      else setError(res.message ?? 'Failed to load details');
      setLoading(false);
    }).catch(() => {
      if (!cancelled) { setError('Failed to connect to server'); setLoading(false); }
    });
    return () => { cancelled = true; };
  }, [id]);

  const handleMinute = () => {
    Alert.alert('Minute leave', 'Send this leave application for DC Admin approval?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Minute', onPress: async () => {
          setActioning(true);
          try {
            const res = await leaveApi.minute(id);
            if (res.success) { load(); navigation.goBack(); }
            else Alert.alert('Error', res.message ?? 'Failed');
          } catch (e: unknown) {
            Alert.alert('Error', (e as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Failed to minute');
          } finally { setActioning(false); }
        }
      },
    ]);
  };

  const handleApprove = () => {
    Alert.alert('Approve Leave', 'Are you sure you want to approve this leave application?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Approve', onPress: async () => {
          setActioning(true);
          try {
            const res = await leaveApi.approve(id, { action: 'approve' });
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
      leaveApi.approve(id, { action: 'reject', comments: rejectComments.trim() }).then((res) => {
        setActioning(false);
        if (res.success) { load(); setRejectMode(false); setRejectComments(''); navigation.goBack(); }
        else Alert.alert('Error', res.message ?? 'Rejection failed');
      }).catch(() => { setActioning(false); Alert.alert('Error', 'Network error'); });
      return;
    }
    setRejectMode(true);
  };

  if (loading) return <View style={[styles.centered, { backgroundColor: '#ffffff' }]}><ActivityIndicator size="large" color={themeColors.primary} /></View>;
  if (error || !item) return <View style={[styles.centered, { backgroundColor: '#ffffff' }]}><Text style={styles.error}>{error ?? 'Leave details not found'}</Text></View>;

  const getStatusColor = () => {
    const s = item.status.toUpperCase();
    if (s === 'APPROVED' || s === 'PROCESSED' || s === 'SUCCESSFUL') return '#16a34a'; // green
    if (s === 'REJECTED' || s === 'FAILED') return '#ef4444'; // red
    if (s === 'PENDING') return '#f59e0b'; // amber
    return '#64748b';
  };

  const statusColor = getStatusColor();
  const leaveTypeName = item.leave_type?.name ?? `Leave type #${item.leave_type_id}`;
  const canMinute = isStaffOfficer && item.status === 'PENDING';
  const canApproveReject = isDcAdmin && item.status === 'MINUTED';

  const toCamelCase = (str: string) => {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  };

  return (
    <ScrollView style={styles.scroll} contentContainerStyle={styles.container}>

      {/* Header Info */}
      <View style={styles.headerBox}>
        <View style={styles.iconCircle}>
          <Ionicons name="calendar" size={28} color="#f97316" />
        </View>
        <Text style={styles.headerTitle}>{leaveTypeName}</Text>
        <Text style={[styles.statusText, { color: statusColor }]}>{toCamelCase(item.status)}</Text>
      </View>

      <View style={styles.card}>
        <View style={styles.row}>
          <Text style={styles.label}>Start Date</Text>
          <Text style={styles.value}>{item.start_date}</Text>
        </View>
        <View style={styles.row}>
          <Text style={styles.label}>End Date</Text>
          <Text style={styles.value}>{item.end_date}</Text>
        </View>
        <View style={[styles.row, { borderBottomWidth: item.reason ? StyleSheet.hairlineWidth : 0 }]}>
          <Text style={styles.label}>Duration</Text>
          <Text style={styles.value}>{item.number_of_days} days</Text>
        </View>

        {item.reason ? (
          <View style={[styles.rowColumn, { borderBottomWidth: item.rejection_reason ? StyleSheet.hairlineWidth : 0 }]}>
            <Text style={styles.label}>Reason</Text>
            <Text style={styles.valueMultiline}>{item.reason}</Text>
          </View>
        ) : null}

        {item.rejection_reason ? (
          <View style={[styles.rowColumn, { borderBottomWidth: 0 }]}>
            <Text style={styles.label}>Rejection Reason</Text>
            <Text style={[styles.valueMultiline, { color: '#ef4444' }]}>{item.rejection_reason}</Text>
          </View>
        ) : null}
      </View>

      {canMinute && (
        <TouchableOpacity style={[styles.minuteBtn, actioning && styles.btnDisabled]} onPress={handleMinute} disabled={actioning}>
          <Ionicons name="send" size={20} color="#fff" style={{ marginRight: 8 }} />
          <Text style={styles.minuteBtnText}>Minute (Send for Approval)</Text>
        </TouchableOpacity>
      )}

      {canApproveReject && !rejectMode && (
        <View style={styles.actions}>
          <TouchableOpacity style={[styles.rejectBtn, actioning && styles.btnDisabled]} onPress={handleReject} disabled={actioning}>
            <Ionicons name="close-circle" size={20} color="#ef4444" style={{ marginRight: 8 }} />
            <Text style={styles.rejectBtnText}>Refuse</Text>
          </TouchableOpacity>
          <TouchableOpacity style={[styles.approveBtn, actioning && styles.btnDisabled]} onPress={handleApprove} disabled={actioning}>
            <Ionicons name="checkmark-circle" size={20} color="#fff" style={{ marginRight: 8 }} />
            <Text style={styles.approveBtnText}>Approve</Text>
          </TouchableOpacity>
        </View>
      )}

      {canApproveReject && rejectMode && (
        <View style={styles.rejectForm}>
          <View style={styles.rejectHeader}>
            <Ionicons name="alert-circle" size={20} color="#ef4444" style={{ marginRight: 8 }} />
            <Text style={styles.rejectLabel}>Reason for Refusal</Text>
          </View>
          <TextInput
            style={styles.rejectInput}
            value={rejectComments}
            onChangeText={setRejectComments}
            placeholder="Please enter the exact reason..."
            placeholderTextColor="#94a3b8"
            multiline
            editable={!actioning}
          />
          <View style={styles.rejectActions}>
            <TouchableOpacity style={styles.cancelBtn} onPress={() => { setRejectMode(false); setRejectComments(''); }}>
              <Text style={styles.cancelBtnText}>Cancel</Text>
            </TouchableOpacity>
            <TouchableOpacity style={[styles.rejectSubmitBtn, actioning && styles.btnDisabled]} onPress={handleReject} disabled={actioning}>
              {actioning ? <ActivityIndicator color="#fff" /> : <Text style={styles.rejectSubmitText}>Confirm Refusal</Text>}
            </TouchableOpacity>
          </View>
        </View>
      )}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1, backgroundColor: '#ffffff' },
  container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#ffffff' },
  error: { fontSize: fontSizes.base, fontWeight: '600', color: '#ef4444' },

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
    backgroundColor: '#fff7ed', // pastel orange
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: '700',
    marginBottom: 4,
    color: '#1e293b',
  },
  statusText: {
    fontSize: 14,
    fontWeight: '600',
  },

  card: {
    backgroundColor: '#fafafa',
    borderRadius: 16,
    padding: spacing.xl,
    marginBottom: spacing.xl
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.md,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: '#e2e8f0'
  },
  rowColumn: {
    flexDirection: 'column',
    alignItems: 'flex-start',
    paddingVertical: spacing.md,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: '#e2e8f0'
  },
  label: { fontSize: 13, color: '#64748b', marginBottom: 2 },
  value: { fontSize: 15, fontWeight: '600', color: '#1e293b' },
  valueMultiline: { fontSize: 15, fontWeight: '500', color: '#1e293b', lineHeight: 22, marginTop: 4 },

  minuteBtn: { flexDirection: 'row', backgroundColor: '#3b82f6', paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center', marginBottom: spacing.lg, elevation: 2, shadowColor: '#3b82f6', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8 },
  minuteBtnText: { color: '#ffffff', fontWeight: '700', fontSize: fontSizes.base },

  actions: { flexDirection: 'row', gap: spacing.base },
  approveBtn: { flex: 1, flexDirection: 'row', backgroundColor: '#10b981', paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center', elevation: 2, shadowColor: '#10b981', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8 },
  approveBtnText: { color: '#ffffff', fontWeight: '700', fontSize: fontSizes.base },

  rejectBtn: { flex: 1, flexDirection: 'row', backgroundColor: '#fee2e2', paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center' },
  rejectBtnText: { color: '#ef4444', fontWeight: '700', fontSize: fontSizes.base },
  btnDisabled: { opacity: 0.6, elevation: 0 },

  rejectForm: { backgroundColor: '#ffffff', borderRadius: 16, padding: spacing.xl, borderWidth: 1, borderColor: '#e2e8f0', marginTop: spacing.md },
  rejectHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.md },
  rejectLabel: { fontSize: fontSizes.base, fontWeight: '600', color: '#1e293b' },
  rejectInput: { backgroundColor: '#f8f9fa', borderWidth: 1, borderColor: '#e2e8f0', borderRadius: 12, padding: spacing.base, fontSize: fontSizes.base, color: '#1e293b', minHeight: 100, textAlignVertical: 'top', marginBottom: spacing.xl },
  rejectActions: { flexDirection: 'row', gap: spacing.base },
  cancelBtn: { flex: 1, paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', backgroundColor: '#f1f5f9' },
  cancelBtnText: { color: '#64748b', fontWeight: '700', fontSize: fontSizes.base },
  rejectSubmitBtn: { flex: 1, backgroundColor: '#ef4444', paddingVertical: spacing.md, borderRadius: 12, alignItems: 'center', justifyContent: 'center', elevation: 2, shadowColor: '#ef4444', shadowOffset: { width: 0, height: 4 }, shadowOpacity: 0.2, shadowRadius: 8 },
  rejectSubmitText: { color: '#ffffff', fontWeight: '700', fontSize: fontSizes.base },
});
