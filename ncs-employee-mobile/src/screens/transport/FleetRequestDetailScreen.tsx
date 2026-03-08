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
                        {request.requested_quantity}x {request.requested_make} {request.requested_model}
                    </Text>
                    <View style={[styles.statusBadge, { backgroundColor: 'rgba(255,255,255,0.2)' }]}>
                        <Text style={styles.statusText}>{request.status.toUpperCase()}</Text>
                    </View>
                </View>

                {/* Details Card */}
                <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                    <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>REQUEST INFO</Text>

                    <View style={styles.row}>
                        <Text style={[styles.label, { color: themeColors.textMuted }]}>Target Command</Text>
                        <Text style={[styles.value, { color: themeColors.text }]}>{request.target_command?.name || 'N/A'}</Text>
                    </View>
                    <View style={[styles.divider, { backgroundColor: themeColors.border }]} />

                    <View style={styles.row}>
                        <Text style={[styles.label, { color: themeColors.textMuted }]}>Budget Amount</Text>
                        <Text style={[styles.value, { color: themeColors.text }]}>
                            {request.amount ? `₦${request.amount.toLocaleString()}` : 'N/A'}
                        </Text>
                    </View>
                    <View style={[styles.divider, { backgroundColor: themeColors.border }]} />

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
});
