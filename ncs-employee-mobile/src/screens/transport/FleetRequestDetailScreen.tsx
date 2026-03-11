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
    TextInput,
} from 'react-native';
import { useRoute, RouteProp, useNavigation } from '@react-navigation/native';
import { fleetApi } from '../../api/fleetApi';
import type { FleetRequest, FleetRequestStep } from '../../api/fleetApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import { useAppSelector } from '../../hooks/redux';

export function FleetRequestDetailScreen() {
    const route = useRoute<RouteProp<{ Detail: { id: number } }, 'Detail'>>();
    const navigation = useNavigation();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';
    const { user } = useAppSelector((s) => s.auth);

    const requestId = route.params?.id;
    const [request, setRequest] = useState<FleetRequest | null>(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [acting, setActing] = useState(false);
    const [actionComment, setActionComment] = useState('');

    const load = useCallback(async () => {
        if (!requestId) return;
        try {
            const res = await fleetApi.requestDetail(requestId);
            if (res.success && res.data) setRequest(res.data);
        } catch {
            setError('Failed to load request details');
        } finally {
            setLoading(false);
        }
    }, [requestId]);

    React.useEffect(() => { load(); }, [load]);

    const handleAction = async (decision: 'APPROVED' | 'REJECTED') => {
        if (!request) return;
        if (decision === 'REJECTED' && !actionComment.trim()) {
            Alert.alert('Remarks Required', 'Please provide a reason for rejection.');
            return;
        }
        setActing(true);
        try {
            const res = await fleetApi.act(request.id, { decision, comment: actionComment });
            if (res.success) {
                Alert.alert('Success', `Request ${decision.toLowerCase()} successfully.`);
                load();
            } else {
                Alert.alert('Error', res.message || 'Failed to process action.');
            }
        } catch {
            Alert.alert('Error', 'An unexpected error occurred.');
        } finally {
            setActing(false);
        }
    };

    const handleSubmitDraft = async () => {
        if (!request) return;
        setActing(true);
        try {
            const res = await fleetApi.submitRequest(request.id);
            if (res.success) {
                Alert.alert('Success', 'Draft submitted successfully.');
                load();
            } else {
                Alert.alert('Error', res.message || 'Failed to submit draft.');
            }
        } catch {
            Alert.alert('Error', 'An unexpected error occurred.');
        } finally {
            setActing(false);
        }
    };

    if (loading) {
        return (
            <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
                <ActivityIndicator size="large" color={themeColors.primary} />
            </View>
        );
    }

    if (error || !request) {
        return (
            <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
                <Text style={[styles.error, { color: themeColors.danger }]}>{error || 'Request not found'}</Text>
            </View>
        );
    }

    const activeStep = request.steps?.find(s => s.status === 'pending');
    const userRoles = user?.roles || [];
    const canAct = activeStep && activeStep.actor_role && userRoles.includes(activeStep.actor_role);

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
            <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>

                {/* Header Card */}
                <View style={[styles.headerCard, { backgroundColor: themeColors.primary }]}>
                    <Text style={styles.headerTitle}>
                        {request.request_type.replace('_', ' ').toUpperCase()} REQUEST
                    </Text>
                    <Text style={styles.headerSub}>
                        {request.requested_quantity ? `${request.requested_quantity}x ` : ''}
                        {request.requested_make || request.vehicle?.make || 'Vehicle'} {request.requested_model || request.vehicle?.model || 'Details'}
                    </Text>
                    <View style={[styles.statusBadge, { backgroundColor: 'rgba(255,255,255,0.2)' }]}>
                        <Text style={styles.statusText}>{request.status.toUpperCase()}</Text>
                    </View>
                </View>

                {/* Details Card */}
                <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                    <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>REQUEST INFO</Text>

                    {request.target_command && (
                        <>
                            <View style={styles.row}>
                                <Text style={[styles.label, { color: themeColors.textMuted }]}>Target Command</Text>
                                <Text style={[styles.value, { color: themeColors.text }]}>{request.target_command.name}</Text>
                            </View>
                            <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                        </>
                    )}

                    {request.request_type === 'FLEET_NEW_VEHICLE' && (
                        <>
                            <View style={styles.row}>
                                <Text style={[styles.label, { color: themeColors.textMuted }]}>Requested Vehicle Type</Text>
                                <Text style={[styles.value, { color: themeColors.text }]}>{request.requested_vehicle_type || 'N/A'}</Text>
                            </View>
                            <View style={[styles.divider, { backgroundColor: themeColors.border }]} />

                            <View style={styles.row}>
                                <Text style={[styles.label, { color: themeColors.textMuted }]}>Requested Quantity</Text>
                                <Text style={[styles.value, { color: themeColors.text }]}>{request.requested_quantity || 1}</Text>
                            </View>
                            <View style={[styles.divider, { backgroundColor: themeColors.border }]} />

                            {(request.requested_make || request.requested_model || request.requested_year) && (
                                <>
                                    <View style={styles.row}>
                                        <Text style={[styles.label, { color: themeColors.textMuted }]}>Make / Model / Year</Text>
                                        <Text style={[styles.value, { color: themeColors.text }]}>
                                            {[request.requested_make, request.requested_model, request.requested_year].filter(Boolean).join(' ') || 'N/A'}
                                        </Text>
                                    </View>
                                    <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                                </>
                            )}
                        </>
                    )}

                    {request.vehicle && (
                        <>
                            <View style={styles.row}>
                                <Text style={[styles.label, { color: themeColors.textMuted }]}>Target Vehicle</Text>
                                <Text style={[styles.value, { color: themeColors.text }]}>
                                    {request.vehicle.make} {request.vehicle.model} ({request.vehicle.reg_no || 'No Reg'})
                                </Text>
                            </View>
                            <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                        </>
                    )}

                    {!!request.amount && (
                        <>
                            <View style={styles.row}>
                                <Text style={[styles.label, { color: themeColors.textMuted }]}>Total Amount</Text>
                                <Text style={[styles.value, { color: themeColors.text, fontWeight: '700' }]}>
                                    ₦{request.amount.toLocaleString()}
                                </Text>
                            </View>
                            <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                        </>
                    )}

                    <View style={{ paddingTop: 10 }}>
                        <Text style={[styles.label, { color: themeColors.textMuted }]}>Notes / Justification</Text>
                        <Text style={[styles.notesValue, { color: themeColors.text }]}>{request.notes || 'No notes provided.'}</Text>
                    </View>
                </View>

                {/* Timeline */}
                <View style={styles.timelineWrap}>
                    <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>APPROVAL TIMELINE</Text>

                    {request.steps?.map((step: FleetRequestStep, index, arr) => {
                        const isLast = index === arr.length - 1;
                        const isApproved = step.status === 'approved';
                        const isPending = step.status === 'pending';
                        const isRejected = step.status === 'rejected';

                        let iconCode = 'time-outline';
                        let iconColor = themeColors.textMuted;
                        if (isApproved) { iconCode = 'checkmark-circle'; iconColor = '#16a34a'; }
                        if (isRejected) { iconCode = 'close-circle'; iconColor = '#dc2626'; }
                        if (isPending) { iconCode = 'ellipsis-horizontal-circle'; iconColor = '#d97706'; }

                        return (
                            <View key={step.id} style={styles.stepRow}>
                                <View style={styles.stepIconCol}>
                                    <Ionicons name={iconCode as any} size={24} color={iconColor} style={{ backgroundColor: themeColors.background }} />
                                    {!isLast && <View style={[styles.stepLine, { backgroundColor: isApproved ? '#16a34a' : themeColors.border }]} />}
                                </View>
                                <View style={[styles.stepCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                    <Text style={[styles.stepTitle, { color: themeColors.text }]}>{step.title}</Text>
                                    <Text style={[styles.stepActor, { color: themeColors.textSecondary }]}>{step.actor_role?.toUpperCase().replace('_', ' ') || 'SYSTEM'}</Text>
                                    {step.remarks ? (
                                        <Text style={[styles.stepRemarks, { color: themeColors.textMuted }]}>"{step.remarks}"</Text>
                                    ) : null}
                                    {step.action_at ? (
                                        <Text style={[styles.stepDate, { color: themeColors.textMuted }]}>
                                            {new Date(step.action_at).toLocaleString()}
                                        </Text>
                                    ) : null}
                                </View>
                            </View>
                        );
                    })}
                </View>

                {canAct && (
                    <View style={[styles.actionSection, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                        <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>YOUR ACTION REQUIRED</Text>
                        <TextInput
                            style={[styles.input, { backgroundColor: themeColors.background, color: themeColors.text, borderColor: themeColors.border }]}
                            placeholder="Add remarks or justification..."
                            placeholderTextColor={themeColors.textMuted}
                            value={actionComment}
                            onChangeText={setActionComment}
                            multiline
                            numberOfLines={3}
                        />
                        <View style={styles.actionBtnRow}>
                            <TouchableOpacity
                                style={[styles.actionBtn, styles.btnReject]}
                                onPress={() => handleAction('REJECTED')}
                                disabled={acting}
                            >
                                <Ionicons name="close-circle" size={20} color="#fff" />
                                <Text style={styles.btnText}>Reject</Text>
                            </TouchableOpacity>
                            <TouchableOpacity
                                style={[styles.actionBtn, styles.btnApprove]}
                                onPress={() => handleAction('APPROVED')}
                                disabled={acting}
                            >
                                <Ionicons name="checkmark-circle" size={20} color="#fff" />
                                <Text style={styles.btnText}>Approve</Text>
                            </TouchableOpacity>
                        </View>
                        {acting && <ActivityIndicator style={{ marginTop: 12 }} color={themeColors.primary} />}
                    </View>
                )}

                {request.status.toLowerCase() === 'draft' &&
                    (request.created_by === user?.id || (request as any).created_by?.id === user?.id || (request as any).createdBy?.id === user?.id) && (
                        <View style={[styles.actionSection, { backgroundColor: themeColors.surface, borderColor: themeColors.border, marginBottom: 20 }]}>
                            <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>DRAFT ACTIONS</Text>
                            <TouchableOpacity
                                style={[styles.actionBtn, styles.btnApprove]}
                                onPress={handleSubmitDraft}
                                disabled={acting}
                            >
                                <Ionicons name="send" size={20} color="#fff" />
                                <Text style={styles.btnText}>Submit Request</Text>
                            </TouchableOpacity>
                            {acting && <ActivityIndicator style={{ marginTop: 12 }} color={themeColors.primary} />}
                        </View>
                    )}

            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    container: { padding: spacing.xl, paddingBottom: 60 },
    error: { fontSize: 13, padding: spacing.base, textAlign: 'center' },

    headerCard: {
        borderRadius: 16,
        padding: spacing.xl,
        paddingVertical: spacing['2xl'],
        alignItems: 'center',
        marginBottom: spacing.lg,
    },
    headerTitle: { fontSize: 14, fontWeight: fontWeights.bold, color: '#fff', letterSpacing: 1.2, marginBottom: 8 },
    headerSub: { fontSize: 20, fontWeight: fontWeights.bold, color: '#fff', marginBottom: 16 },
    statusBadge: { paddingHorizontal: 16, paddingVertical: 6, borderRadius: 20 },
    statusText: { fontSize: 11, fontWeight: fontWeights.bold, color: '#fff' },

    card: { borderRadius: 16, borderWidth: 1, padding: spacing.lg, marginBottom: spacing.xl },
    cardLabel: { fontSize: 11, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginBottom: spacing.md },

    row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10 },
    label: { fontSize: 13, flex: 1 },
    value: { fontSize: 14, fontWeight: fontWeights.semibold, flex: 1, textAlign: 'right' },
    divider: { height: StyleSheet.hairlineWidth },
    notesValue: { fontSize: 14, marginTop: 6, lineHeight: 22 },

    timelineWrap: { marginTop: spacing.sm },
    stepRow: { flexDirection: 'row', marginBottom: 16 },
    stepIconCol: { width: 30, alignItems: 'center', marginRight: 12 },
    stepLine: { width: 2, flex: 1, marginVertical: 4 },

    stepCard: { flex: 1, borderRadius: 12, borderWidth: 1, padding: 14 },
    stepTitle: { fontSize: 15, fontWeight: fontWeights.bold, marginBottom: 4 },
    stepActor: { fontSize: 12, fontWeight: fontWeights.medium, marginBottom: 8 },
    stepRemarks: { fontSize: 13, fontStyle: 'italic', marginBottom: 8 },
    stepDate: { fontSize: 11 },

    actionSection: { borderRadius: 16, borderWidth: 1, padding: spacing.lg, marginTop: spacing.xl },
    input: { borderWidth: 1, borderRadius: 12, padding: spacing.md, fontSize: 13, minHeight: 80, textAlignVertical: 'top', marginBottom: spacing.md },
    actionBtnRow: { flexDirection: 'row', gap: 12 },
    actionBtn: { flex: 1, flexDirection: 'row', alignItems: 'center', justifyContent: 'center', paddingVertical: 12, borderRadius: 12, gap: 6 },
    btnReject: { backgroundColor: '#dc2626' },
    btnApprove: { backgroundColor: '#16a34a' },
    btnText: { color: '#fff', fontSize: 14, fontWeight: fontWeights.bold },
});
