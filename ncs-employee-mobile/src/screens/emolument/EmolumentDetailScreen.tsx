import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ActivityIndicator, ScrollView, TouchableOpacity, Alert, Platform } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { emolumentApi } from '../../api/emolumentApi';
import type { EmolumentItem } from '../../api/types';
import { useThemeColor, spacing } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Props = NativeStackScreenProps<RequestStackParamList, 'EmolumentDetail'>;

export function EmolumentDetailScreen({ route, navigation }: Props) {
  const { id } = route.params;
  const themeColors = useThemeColor();

  const [item, setItem] = useState<EmolumentItem | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [resubmitting, setResubmitting] = useState(false);

  const load = () => {
    setLoading(true);
    emolumentApi.get(id).then((res) => {
      if (res.success && res.data) setItem(res.data);
      else setError(res.message ?? 'Failed to load emolument details');
      setLoading(false);
    }).catch(() => { setError('Failed to load emolument details'); setLoading(false); });
  };

  useEffect(() => { load(); }, [id]);

  const onResubmit = () => {
    Alert.alert('Resubmit Emolument', 'Are you sure you want to resubmit this emolument for review?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Resubmit',
        onPress: async () => {
          setResubmitting(true);
          try {
            const res = await emolumentApi.resubmit(id);
            if (res.success) {
              Alert.alert('Success', 'Emolument resubmitted successfully.', [{ text: 'OK', onPress: load }]);
            } else {
              Alert.alert('Error', res.message ?? 'Resubmission failed');
            }
          } catch {
            Alert.alert('Error', 'Network error. Resubmission failed');
          } finally {
            setResubmitting(false);
          }
        },
      },
    ]);
  };

  if (loading) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  if (error || !item) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <Ionicons name="alert-circle-outline" size={48} color={themeColors.danger} style={{ marginBottom: spacing.sm }} />
        <Text style={[styles.error, { color: themeColors.danger }]}>{error ?? 'Emolument not found'}</Text>
        <TouchableOpacity style={styles.retryBtn} onPress={load}>
          <Text style={{ color: themeColors.primary, fontWeight: '600' }}>Retry</Text>
        </TouchableOpacity>
      </View>
    );
  }

  const getStatusColor = (status: string) => {
    const s = status.toUpperCase();
    if (s === 'PROCESSED' || s === 'AUDITED' || s === 'SUCCESSFUL') return { bg: '#dcfce7', text: '#166534' }; // green
    if (s === 'REJECTED' || s === 'FAILED') return { bg: '#fee2e2', text: '#991b1b' }; // red
    if (s === 'PENDING' || s === 'ASSESSED' || s === 'VALIDATED') return { bg: '#fef3c7', text: '#92400e' }; // amber
    return { bg: '#e0e7ff', text: '#3730a3' }; // RAISED / default blue
  };

  const statusStyle = getStatusColor(item.status);
  const canResubmit = item.status === 'REJECTED';

  const isAssessed = ['ASSESSED', 'VALIDATED', 'AUDITED', 'PROCESSED', 'SUCCESSFUL'].includes(item.status.toUpperCase()) || !!item.assessed_at;
  const isValidated = ['VALIDATED', 'AUDITED', 'PROCESSED', 'SUCCESSFUL'].includes(item.status.toUpperCase()) || !!item.validated_at;
  const isAudited = ['AUDITED', 'PROCESSED', 'SUCCESSFUL'].includes(item.status.toUpperCase()) || !!item.audited_at;
  const isProcessed = ['PROCESSED', 'SUCCESSFUL'].includes(item.status.toUpperCase()) || !!item.processed_at;

  const toCamelCase = (str: string) => {
    if (!str) return str;
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
  };

  const formatDate = (dateString?: string) => {
    if (!dateString) return 'N/A';
    const d = new Date(dateString);
    if (isNaN(d.getTime())) return dateString;
    return d.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
  };

  return (
    <ScrollView style={[styles.scroll, { backgroundColor: '#ffffff' }]} contentContainerStyle={styles.container}>

      {/* Header Card */}
      <View style={styles.card}>
        <View style={styles.headerRow}>
          <View>
            <Text style={styles.headerTitle}>Year {item.year}</Text>
            <Text style={styles.headerSubtitle}>Emolument Reference #{item.id.toString().padStart(6, '0')}</Text>
          </View>
          <View style={[styles.statusBadge, { backgroundColor: statusStyle.bg }]}>
            <Text style={[styles.statusText, { color: statusStyle.text }]}>{toCamelCase(item.status)}</Text>
          </View>
        </View>
      </View>

      {/* Payment Information */}
      <Text style={styles.sectionTitle}>Payment Information</Text>
      <View style={styles.card}>

        <View style={styles.infoBlock}>
          <Text style={styles.infoLabel}>BANK DETAILS</Text>
          <View style={styles.readOnlyContainer}>
            <View style={styles.readOnlyRow}>
              <Ionicons name="business-outline" size={18} color="#64748b" />
              <Text style={styles.readOnlyText}>{item.bank_name || 'Not Provided'}</Text>
            </View>
            <View style={[styles.readOnlyRow, { marginTop: 10 }]}>
              <Ionicons name="wallet-outline" size={18} color="#64748b" />
              <Text style={[styles.readOnlyText, { fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace' }]}>
                {item.bank_account_number || 'Not Provided'}
              </Text>
            </View>
          </View>
        </View>

        <View style={[styles.infoBlock, { marginTop: spacing.md }]}>
          <Text style={styles.infoLabel}>PFA DETAILS</Text>
          <View style={styles.readOnlyContainer}>
            <View style={styles.readOnlyRow}>
              <Ionicons name="shield-checkmark-outline" size={18} color="#64748b" />
              <Text style={styles.readOnlyText}>{item.pfa_name || 'Not Provided'}</Text>
            </View>
            <View style={[styles.readOnlyRow, { marginTop: 10 }]}>
              <Ionicons name="keypad-outline" size={18} color="#64748b" />
              <Text style={[styles.readOnlyText, { fontFamily: Platform.OS === 'ios' ? 'Menlo' : 'monospace' }]}>
                RSA: {item.rsa_pin || 'Not Provided'}
              </Text>
            </View>
          </View>
        </View>

      </View>

      {/* Rejection Details (If Applicable) */}
      {item.status === 'REJECTED' && (
        <>
          <Text style={[styles.sectionTitle, { color: '#ef4444' }]}>Rejection Details</Text>
          <View style={[styles.card, { borderColor: '#fca5a5', borderWidth: 1 }]} >
            <View style={[styles.readOnlyContainer, { backgroundColor: '#fef2f2', borderColor: '#fecaca' }]}>
              <Text style={[styles.infoLabel, { color: '#991b1b', marginBottom: 4 }]}>Reason for Rejection</Text>
              <Text style={{ color: '#7f1d1d', fontSize: 13, lineHeight: 20 }}>
                Your emolument was rejected during the assessment/validation phase. Please review and resubmit.
              </Text>
            </View>

            {canResubmit && (
              <TouchableOpacity
                style={[styles.resubmitButton, { backgroundColor: themeColors.primary }]}
                onPress={onResubmit}
                disabled={resubmitting}
              >
                {resubmitting ? (
                  <ActivityIndicator color="#ffffff" />
                ) : (
                  <>
                    <Ionicons name="refresh" size={18} color="#ffffff" style={{ marginRight: 6 }} />
                    <Text style={styles.resubmitText}>Resubmit Emolument</Text>
                  </>
                )}
              </TouchableOpacity>
            )}
          </View>
        </>
      )}

      {/* Timeline Info */}
      <Text style={styles.sectionTitle}>Timeline Info</Text>
      <View style={styles.card}>
        <View style={styles.timelineRow}>
          <Text style={styles.timelineLabel}>Emolument Year</Text>
          <Text style={styles.timelineValue}>{item.timeline?.year ?? item.year}</Text>
        </View>
        <View style={styles.timelineRow}>
          <Text style={styles.timelineLabel}>Submitted On</Text>
          <Text style={styles.timelineValue}>{formatDate(item.submitted_at)}</Text>
        </View>
        {item.assessed_at && (
          <View style={styles.timelineRow}>
            <Text style={styles.timelineLabel}>Assessed On</Text>
            <Text style={styles.timelineValue}>{formatDate(item.assessed_at)}</Text>
          </View>
        )}
        {item.validated_at && (
          <View style={styles.timelineRow}>
            <Text style={styles.timelineLabel}>Validated On</Text>
            <Text style={styles.timelineValue}>{formatDate(item.validated_at)}</Text>
          </View>
        )}
        {item.processed_at && (
          <View style={[styles.timelineRow, { borderBottomWidth: 0 }]}>
            <Text style={styles.timelineLabel}>Processed On</Text>
            <Text style={styles.timelineValue}>{formatDate(item.processed_at)}</Text>
          </View>
        )}
      </View>

      {/* Workflow */}
      <Text style={styles.sectionTitle}>Workflow</Text>
      <View style={styles.card}>
        <View style={{ marginLeft: spacing.sm }}>
          <View style={[styles.timelineTrack]} />

          <View style={styles.workflowStep}>
            <View style={[styles.workflowDot, { backgroundColor: '#10b981' }]}>
              <View style={styles.workflowInnerDot} />
            </View>
            <View style={styles.workflowContent}>
              <Text style={[styles.workflowStepTitle, { color: '#0f172a' }]}>Raised</Text>
              <Text style={styles.workflowStepDate}>{formatDate(item.submitted_at)}</Text>
            </View>
          </View>

          <View style={styles.workflowStep}>
            <View style={[styles.workflowDot, isAssessed ? { backgroundColor: '#10b981' } : undefined]}>
              <View style={styles.workflowInnerDot} />
            </View>
            <View style={styles.workflowContent}>
              <Text style={[styles.workflowStepTitle, isAssessed ? { color: '#0f172a' } : undefined]}>Assessed</Text>
              <Text style={styles.workflowStepDate}>{isAssessed ? formatDate(item.assessed_at) : 'Pending'}</Text>
            </View>
          </View>

          <View style={styles.workflowStep}>
            <View style={[styles.workflowDot, isValidated ? { backgroundColor: '#10b981' } : undefined]}>
              <View style={styles.workflowInnerDot} />
            </View>
            <View style={styles.workflowContent}>
              <Text style={[styles.workflowStepTitle, isValidated ? { color: '#0f172a' } : undefined]}>Validated</Text>
              <Text style={styles.workflowStepDate}>{isValidated ? formatDate(item.validated_at) : 'Pending'}</Text>
            </View>
          </View>

          <View style={styles.workflowStep}>
            <View style={[styles.workflowDot, isAudited ? { backgroundColor: '#10b981' } : undefined]}>
              <View style={styles.workflowInnerDot} />
            </View>
            <View style={styles.workflowContent}>
              <Text style={[styles.workflowStepTitle, isAudited ? { color: '#0f172a' } : undefined]}>Audited</Text>
              <Text style={styles.workflowStepDate}>{isAudited ? formatDate(item.audited_at) : 'Pending'}</Text>
            </View>
          </View>

          <View style={[styles.workflowStep, { marginBottom: 0 }]}>
            <View style={[styles.workflowDot, isProcessed ? { backgroundColor: '#10b981' } : undefined]}>
              <View style={styles.workflowInnerDot} />
            </View>
            <View style={styles.workflowContent}>
              <Text style={[styles.workflowStepTitle, isProcessed ? { color: '#0f172a' } : undefined]}>Processed</Text>
              <Text style={styles.workflowStepDate}>{isProcessed ? formatDate(item.processed_at) : 'Pending'}</Text>
            </View>
          </View>
        </View>
      </View>

      {/* Notes */}
      {item.notes ? (
        <>
          <Text style={styles.sectionTitle}>Notes</Text>
          <View style={styles.card}>
            <Text style={{ fontSize: 14, color: '#334155', lineHeight: 22 }}>{item.notes}</Text>
          </View>
        </>
      ) : null}

    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1 },
  container: { padding: spacing.lg, paddingBottom: spacing['3xl'] },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },

  error: { fontSize: 16, fontWeight: '500', textAlign: 'center' },
  retryBtn: { marginTop: spacing.lg, paddingHorizontal: spacing.xl, paddingVertical: spacing.sm, borderRadius: 8, backgroundColor: '#f1f5f9' },

  card: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: spacing.lg,
    marginBottom: spacing.lg,
    borderWidth: 1,
    borderColor: '#f1f5f9',
  },

  headerRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start' },
  headerTitle: { fontSize: 18, fontWeight: '700', color: '#0f172a', marginBottom: 4 },
  headerSubtitle: { fontSize: 13, color: '#64748b' },

  statusBadge: { paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
  statusText: { fontSize: 12, fontWeight: '700' },

  sectionTitle: { fontSize: 14, fontWeight: '700', color: '#475569', marginLeft: 4, marginBottom: spacing.sm, marginTop: spacing.xs, textTransform: 'uppercase', letterSpacing: 0.5 },

  infoBlock: { marginBottom: 2 },
  infoLabel: { fontSize: 12, fontWeight: '600', color: '#64748b', marginBottom: 6 },

  readOnlyContainer: {
    backgroundColor: '#f8faf9',
    borderRadius: 10,
    padding: spacing.md,
    borderWidth: 1,
    borderColor: '#e2e8f0',
  },
  readOnlyRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  readOnlyText: {
    marginLeft: 10,
    fontSize: 14,
    color: '#334155',
    fontWeight: '500',
  },

  timelineRow: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: 12, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
  timelineLabel: { fontSize: 14, color: '#64748b' },
  timelineValue: { fontSize: 14, fontWeight: '600', color: '#1e293b' },

  resubmitButton: {
    flexDirection: 'row',
    marginTop: spacing.lg,
    paddingVertical: 14,
    justifyContent: 'center',
    alignItems: 'center',
    borderRadius: 12,
  },
  resubmitText: { color: '#ffffff', fontSize: 15, fontWeight: '700' },

  timelineTrack: { position: 'absolute', top: 12, bottom: 12, left: 7, width: 2, backgroundColor: '#e2e8f0' },
  workflowStep: { flexDirection: 'row', marginBottom: 24, paddingLeft: 24, position: 'relative' },
  workflowDot: {
    position: 'absolute',
    left: -1,
    top: 4,
    width: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: '#cbd5e1',
    borderWidth: 2,
    borderColor: '#ffffff',
    zIndex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  workflowInnerDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: '#ffffff',
  },
  workflowContent: { flex: 1, paddingTop: 1 },
  workflowStepTitle: { fontSize: 14, fontWeight: '600', color: '#64748b', marginBottom: 2 },
  workflowStepDate: { fontSize: 13, color: '#94a3b8' },
});
