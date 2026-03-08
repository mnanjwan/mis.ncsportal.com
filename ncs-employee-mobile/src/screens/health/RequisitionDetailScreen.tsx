import React, { useState, useCallback } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ActivityIndicator,
    ScrollView,
    SafeAreaView,
    StatusBar,
    useColorScheme,
    TouchableOpacity,
    Alert,
} from 'react-native';
import { useRoute, RouteProp, useNavigation, useFocusEffect } from '@react-navigation/native';
import { pharmacyApi } from '../../api/pharmacyApi';
import type { PharmacyRequisition, RequisitionStatus } from '../../api/pharmacyApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

const WORKFLOW_STEPS: { status: RequisitionStatus; label: string; icon: string }[] = [
    { status: 'DRAFT', label: 'Drafted', icon: 'document-text' },
    { status: 'SUBMITTED', label: 'Submitted', icon: 'send' },
    { status: 'APPROVED', label: 'Approved', icon: 'checkmark-circle' },
    { status: 'ISSUED', label: 'Issued', icon: 'cube' },
    { status: 'DISPENSED', label: 'Dispensed', icon: 'medical' },
];

export function RequisitionDetailScreen() {
    const route = useRoute<RouteProp<{ Detail: { id: number } }, 'Detail'>>();
    const navigation = useNavigation();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';
    const { id } = route.params;

    const [req, setReq] = useState<PharmacyRequisition | null>(null);
    const [loading, setLoading] = useState(true);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const load = useCallback(async () => {
        setError(null);
        try {
            const res = await pharmacyApi.requisitionDetail(id);
            if (res.success && res.data) setReq(res.data);
        } catch {
            setError('Failed to load requisition details.');
        } finally {
            setLoading(false);
        }
    }, [id]);

    useFocusEffect(useCallback(() => { load(); }, [load]));

    const handleSubmit = async () => {
        Alert.alert('Submit Requisition', 'Are you sure you want to submit this request to the Medical Officer for approval?', [
            { text: 'Cancel', style: 'cancel' },
            {
                text: 'Submit', style: 'default', onPress: async () => {
                    setSubmitting(true);
                    try {
                        const res = await pharmacyApi.submitRequisition(id);
                        if (res.success) {
                            Alert.alert('Success', 'Requisition submitted.');
                            load();
                        }
                    } catch (err: any) {
                        Alert.alert('Error', err.message || 'Failed to submit requisition.');
                    } finally {
                        setSubmitting(false);
                    }
                }
            }
        ]);
    };

    const getStepIndex = (status: RequisitionStatus) => {
        if (status === 'REJECTED') return -1;
        return WORKFLOW_STEPS.findIndex(s => s.status === status);
    };

    const renderTimeline = () => {
        if (!req) return null;
        const currentIndex = getStepIndex(req.status);
        const isRejected = req.status === 'REJECTED';

        if (isRejected) {
            return (
                <View style={[styles.rejectedBanner, { backgroundColor: '#fef2f2', borderColor: '#f87171' }]}>
                    <Ionicons name="close-circle" size={24} color="#dc2626" />
                    <View style={{ flex: 1, marginLeft: 12 }}>
                        <Text style={{ color: '#dc2626', fontWeight: fontWeights.bold, fontSize: 16 }}>Requisition Rejected</Text>
                        <Text style={{ color: '#b91c1c', marginTop: 4 }}>This request was denied by the Medical Officer.</Text>
                    </View>
                </View>
            );
        }

        return (
            <View style={[styles.timelineWrap, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                {WORKFLOW_STEPS.map((step, idx) => {
                    const isCompleted = idx <= currentIndex;
                    const isCurrent = idx === currentIndex;
                    const isLast = idx === WORKFLOW_STEPS.length - 1;
                    const color = isCurrent ? themeColors.primary : isCompleted ? '#10b981' : themeColors.border;

                    return (
                        <View key={step.status} style={styles.timelineStep}>
                            <View style={styles.timelineNodeBlock}>
                                <View style={[styles.timelineIcon, { backgroundColor: isCompleted ? color : themeColors.background, borderColor: color }]}>
                                    <Ionicons name={step.icon as any} size={16} color={isCompleted ? '#fff' : color} />
                                </View>
                                {!isLast && <View style={[styles.timelineLine, { backgroundColor: isCompleted && !isCurrent ? color : themeColors.border }]} />}
                            </View>
                            <View style={styles.timelineContent}>
                                <Text style={[styles.timelineLabel, { color: isCurrent ? themeColors.primary : themeColors.text, fontWeight: isCurrent ? fontWeights.bold : fontWeights.medium }]}>
                                    {step.label}
                                </Text>
                                {/* Could map timestamps here if needed, omitting for brevity */}
                            </View>
                        </View>
                    );
                })}
            </View>
        );
    };

    if (loading && !req) {
        return (
            <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
                <ActivityIndicator size="large" color={themeColors.primary} />
            </View>
        );
    }

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

            <View style={[styles.header, { backgroundColor: themeColors.primary }]}>
                <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                    <Ionicons name="arrow-back" size={28} color="#fff" />
                </TouchableOpacity>
                <Text style={styles.headerTitle}>{req?.reference_number}</Text>
                <View style={{ width: 40 }} />
            </View>

            <ScrollView contentContainerStyle={styles.container}>
                {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

                {req && (
                    <>
                        {renderTimeline()}

                        <Text style={[styles.sectionTitle, { color: themeColors.textSecondary, marginTop: 24 }]}>REQUESTED DRUGS</Text>
                        {req.items.map((item, idx) => (
                            <View key={item.id} style={[styles.drugCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                <View style={{ flex: 1 }}>
                                    <Text style={[styles.drugName, { color: themeColors.text }]}>{item.drug.name}</Text>
                                    <Text style={[styles.drugCategory, { color: themeColors.textMuted }]}>{item.drug.category} • {item.drug.unit}</Text>
                                </View>

                                <View style={styles.qtyBlock}>
                                    <View style={styles.qtyRow}>
                                        <Text style={[styles.qtyLabel, { color: themeColors.textMuted }]}>Requested:</Text>
                                        <Text style={[styles.qtyValue, { color: themeColors.text }]}>{item.requested_quantity}</Text>
                                    </View>
                                    {item.approved_quantity !== null && item.approved_quantity !== undefined && (
                                        <View style={styles.qtyRow}>
                                            <Text style={[styles.qtyLabel, { color: themeColors.textMuted }]}>Approved:</Text>
                                            <Text style={[styles.qtyValue, { color: '#10b981' }]}>{item.approved_quantity}</Text>
                                        </View>
                                    )}
                                    {item.issued_quantity !== null && item.issued_quantity !== undefined && (
                                        <View style={styles.qtyRow}>
                                            <Text style={[styles.qtyLabel, { color: themeColors.textMuted }]}>Issued:</Text>
                                            <Text style={[styles.qtyValue, { color: themeColors.primary }]}>{item.issued_quantity}</Text>
                                        </View>
                                    )}
                                </View>
                            </View>
                        ))}

                        {req.notes && (
                            <>
                                <Text style={[styles.sectionTitle, { color: themeColors.textSecondary, marginTop: 24 }]}>NOTES</Text>
                                <View style={[styles.notesCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                    <Text style={{ color: themeColors.text, lineHeight: 22 }}>"{req.notes}"</Text>
                                </View>
                            </>
                        )}

                        {req.status === 'DRAFT' && (
                            <View style={{ marginTop: 40 }}>
                                <TouchableOpacity
                                    style={[styles.primaryBtn, { backgroundColor: themeColors.primary }]}
                                    onPress={handleSubmit}
                                    disabled={submitting}
                                >
                                    {submitting ? <ActivityIndicator color="#fff" /> : <Text style={styles.primaryBtnText}>Submit to Medical Officer</Text>}
                                </TouchableOpacity>
                            </View>
                        )}
                    </>
                )}
            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    container: { padding: spacing.xl, paddingBottom: 60 },
    error: { fontSize: 13, textAlign: 'center', marginBottom: 20 },

    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: spacing.lg, paddingTop: spacing.sm, paddingBottom: spacing.lg, borderBottomLeftRadius: 24, borderBottomRightRadius: 24 },
    backBtn: { padding: 4 },
    headerTitle: { fontSize: 18, fontWeight: fontWeights.bold, color: '#fff' },

    sectionTitle: { fontSize: 12, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginBottom: spacing.sm, marginLeft: 4 },

    rejectedBanner: { flexDirection: 'row', alignItems: 'center', padding: spacing.lg, borderRadius: 16, borderWidth: 1, marginBottom: 16 },

    timelineWrap: { borderRadius: 16, borderWidth: 1, padding: spacing.xl },
    timelineStep: { flexDirection: 'row', minHeight: 60 },
    timelineNodeBlock: { width: 32, alignItems: 'center', position: 'relative' },
    timelineIcon: { width: 32, height: 32, borderRadius: 16, borderWidth: 2, justifyContent: 'center', alignItems: 'center', zIndex: 2 },
    timelineLine: { position: 'absolute', top: 32, bottom: -8, width: 2, zIndex: 1 },
    timelineContent: { flex: 1, marginLeft: 16, paddingTop: 6 },
    timelineLabel: { fontSize: 15 },

    drugCard: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', borderRadius: 16, borderWidth: 1, padding: spacing.md, marginBottom: 8 },
    drugName: { fontSize: 15, fontWeight: fontWeights.bold, marginBottom: 4 },
    drugCategory: { fontSize: 12 },
    qtyBlock: { alignItems: 'flex-end', gap: 4 },
    qtyRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    qtyLabel: { fontSize: 11, fontStyle: 'italic' },
    qtyValue: { fontSize: 13, fontWeight: fontWeights.bold },

    notesCard: { borderRadius: 16, borderWidth: 1, padding: spacing.lg },

    primaryBtn: { height: 50, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    primaryBtnText: { color: '#fff', fontSize: 16, fontWeight: fontWeights.bold },
});
