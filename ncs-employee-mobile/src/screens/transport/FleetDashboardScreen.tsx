import React, { useState, useCallback } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    TouchableOpacity,
    ActivityIndicator,
    RefreshControl,
    SafeAreaView,
    StatusBar,
    useColorScheme,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAppSelector } from '../../hooks/redux';
import { fleetApi } from '../../api/fleetApi';
import type { FleetRequest } from '../../api/fleetApi';
import type { TransportStackParamList } from '../../navigation/TransportStack';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Nav = NativeStackNavigationProp<TransportStackParamList, 'FleetDashboard'>;

function getStatusColor(status: string) {
    if (status === 'approved') return '#16a34a'; // green
    if (status === 'rejected') return '#dc2626'; // red
    if (status === 'fulfilled') return '#2563eb'; // blue
    return '#d97706'; // pending/in_progress amber
}

function getRequestTypeConfig(type: string) {
    switch (type) {
        case 'new_vehicle': return { label: 'New Vehicle', icon: 'car', bg: '#ecfdf5', color: '#10b981' };
        case 'reallocation': return { label: 'Re-Allocation', icon: 'swap-horizontal', bg: '#eff6ff', color: '#3b82f6' };
        case 'requisition': return { label: 'Requisition', icon: 'key', bg: '#fdf4ff', color: '#a855f7' };
        case 'repair': return { label: 'Repair', icon: 'build', bg: '#fff7ed', color: '#f97316' };
        default: return { label: type, icon: 'car-sport', bg: '#f8fafc', color: '#64748b' };
    }
}

export function FleetDashboardScreen() {
    const navigation = useNavigation<Nav>();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';
    const { user } = useAppSelector((s) => s.auth);

    const isApprover = user?.roles?.some(role => ['CGC', 'DCG FATS', 'ACG TS', 'CC T&L', 'Staff Officer T&L'].includes(role));
    const isTLOfficer = user?.roles?.some(role => ['CC T&L', 'O/C T&L', 'T&L Officer', 'Staff Officer T&L'].includes(role));
    const [activeTab, setActiveTab] = useState<'my_requests' | 'inbox'>(isApprover ? 'inbox' : 'my_requests');
    const [requests, setRequests] = useState<FleetRequest[]>([]);
    const [inbox, setInbox] = useState<FleetRequest[]>([]);
    const [stats, setStats] = useState({ total: 0, active: 0, maintenance: 0 });
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const load = useCallback(async () => {
        setError(null);
        try {
            // Fetch requests
            const res = await fleetApi.requests();
            if (res.success && res.data) {
                setRequests(res.data.myRequests || []);
                setInbox(res.data.inbox || []);
            }

            // Fetch vehicles for stats if T&L officer
            if (isTLOfficer) {
                const vRes = await fleetApi.commandVehicles();
                if (vRes.success && vRes.data) {
                    const vehicles = vRes.data;
                    setStats({
                        total: vehicles.length,
                        active: vehicles.filter(v => v.service_status === 'active').length,
                        maintenance: vehicles.filter(v => v.service_status === 'maintenance').length,
                    });
                }
            }
        } catch {
            setError('Failed to load fleet dashboard');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [isTLOfficer]);

    useFocusEffect(
        useCallback(() => {
            setLoading(true);
            load();
        }, [load])
    );

    const renderItem = ({ item }: { item: FleetRequest }) => {
        const cfg = getRequestTypeConfig(item.request_type);
        const dateStr = item.submitted_at || item.created_at;
        const formattedDate = dateStr ? new Date(dateStr).toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' }) : '—';

        return (
            <TouchableOpacity
                style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
                onPress={() => navigation.navigate('FleetRequestDetail' as any, { id: item.id })}
                activeOpacity={0.7}
            >
                <View style={styles.cardHeader}>
                    <View style={[styles.typeBadge, { backgroundColor: cfg.bg }]}>
                        <Ionicons name={cfg.icon as any} size={14} color={cfg.color} />
                        <Text style={[styles.typeText, { color: cfg.color }]}>{cfg.label}</Text>
                    </View>
                    <View style={[styles.statusBadge, { borderColor: getStatusColor(item.status) }]}>
                        <Text style={[styles.statusText, { color: getStatusColor(item.status) }]}>
                            {item.status.replace('_', ' ').toUpperCase()}
                        </Text>
                    </View>
                </View>

                <View style={styles.cardBody}>
                    {item.request_type === 'new_vehicle' || item.request_type === 'requisition' ? (
                        <Text style={[styles.vehicleTitle, { color: themeColors.text }]} numberOfLines={1}>
                            {item.requested_quantity}x {item.requested_make} {item.requested_model} ({item.requested_vehicle_type})
                        </Text>
                    ) : (
                        <Text style={[styles.vehicleTitle, { color: themeColors.text }]} numberOfLines={1}>
                            {item.vehicle ? `${item.vehicle.make} ${item.vehicle.model} (${item.vehicle.reg_no})` : 'Vehicle Request'}
                        </Text>
                    )}

                    <View style={styles.metaRow}>
                        <Ionicons name="calendar-outline" size={14} color={themeColors.textMuted} />
                        <Text style={[styles.metaText, { color: themeColors.textMuted }]}>{formattedDate}</Text>
                    </View>
                </View>
            </TouchableOpacity>
        );
    };

    if (loading && requests.length === 0) {
        return (
            <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
                <ActivityIndicator size="large" color={themeColors.primary} />
            </View>
        );
    }

    const currentList = activeTab === 'inbox' ? inbox : requests;

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

            <View style={[styles.header, { backgroundColor: themeColors.primary }]}>
                <View style={styles.headerTitleRow}>
                    <Ionicons name="car-sport" size={28} color="#ffffff" />
                    <View>
                        <Text style={styles.headerTitle}>Fleet Dashboard</Text>
                        <Text style={styles.headerSubtitle}>{user?.officer?.command?.name} Command</Text>
                    </View>
                </View>

                {isTLOfficer && (
                    <View style={styles.statsContainer}>
                        <View style={styles.statBox}>
                            <Text style={styles.statValue}>{stats.total}</Text>
                            <Text style={styles.statLabel}>Total</Text>
                        </View>
                        <View style={[styles.statDivider, { backgroundColor: 'rgba(255,255,255,0.2)' }]} />
                        <View style={styles.statBox}>
                            <Text style={styles.statValue}>{stats.active}</Text>
                            <Text style={styles.statLabel}>Active</Text>
                        </View>
                        <View style={[styles.statDivider, { backgroundColor: 'rgba(255,255,255,0.2)' }]} />
                        <View style={styles.statBox}>
                            <Text style={styles.statValue}>{stats.maintenance}</Text>
                            <Text style={styles.statLabel}>Repair</Text>
                        </View>
                    </View>
                )}
            </View>

            {isApprover && (
                <View style={[styles.tabContainer, { backgroundColor: themeColors.surface }]}>
                    <TouchableOpacity
                        style={[styles.tabBtn, activeTab === 'my_requests' && { borderBottomColor: themeColors.primary, borderBottomWidth: 2 }]}
                        onPress={() => setActiveTab('my_requests')}
                        activeOpacity={0.7}
                    >
                        <Text style={[styles.tabText, { color: activeTab === 'my_requests' ? themeColors.primary : themeColors.textMuted }]}>
                            My Requests
                        </Text>
                    </TouchableOpacity>
                    <TouchableOpacity
                        style={[styles.tabBtn, activeTab === 'inbox' && { borderBottomColor: themeColors.primary, borderBottomWidth: 2 }]}
                        onPress={() => setActiveTab('inbox')}
                        activeOpacity={0.7}
                    >
                        <Text style={[styles.tabText, { color: activeTab === 'inbox' ? themeColors.primary : themeColors.textMuted }]}>
                            Inbox ({inbox.length})
                        </Text>
                    </TouchableOpacity>
                </View>
            )}

            <View style={styles.listContainer}>
                {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

                <FlatList
                    data={currentList}
                    keyExtractor={(item) => String(item.id)}
                    renderItem={renderItem}
                    contentContainerStyle={styles.list}
                    refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[themeColors.primary]} tintColor={themeColors.primary} />}
                    ListEmptyComponent={
                        <View style={styles.emptyWrap}>
                            <Ionicons name="document-text-outline" size={48} color={themeColors.border} />
                            <Text style={[styles.emptyTitle, { color: themeColors.textMuted }]}>No Requests</Text>
                            <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>No fleet requests have been made.</Text>
                        </View>
                    }
                />
            </View>

            {/* FAB to create new request */}
            <TouchableOpacity
                style={[styles.fab, { backgroundColor: themeColors.primary }]}
                onPress={() => navigation.navigate('CreateFleetRequest' as any)}
                activeOpacity={0.8}
            >
                <Ionicons name="add" size={30} color="#ffffff" />
            </TouchableOpacity>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { padding: spacing.xl, paddingBottom: spacing.lg, borderBottomLeftRadius: 24, borderBottomRightRadius: 24 },
    headerTitleRow: { flexDirection: 'row', alignItems: 'center', gap: 12 },
    headerTitle: { fontSize: 20, fontWeight: fontWeights.bold, color: '#ffffff' },
    headerSubtitle: { fontSize: 13, color: 'rgba(255,255,255,0.8)' },
    statsContainer: { flexDirection: 'row', justifyContent: 'space-between', marginTop: spacing.xl, backgroundColor: 'rgba(0,0,0,0.15)', padding: spacing.md, borderRadius: 16 },
    statBox: { flex: 1, alignItems: 'center' },
    statValue: { fontSize: 24, fontWeight: fontWeights.bold, color: '#ffffff' },
    statLabel: { fontSize: 12, color: 'rgba(255,255,255,0.8)', marginTop: 2, textTransform: 'uppercase', letterSpacing: 0.5 },
    statDivider: { width: 1, height: '80%', alignSelf: 'center' },
    tabContainer: { flexDirection: 'row', borderBottomWidth: 1, borderBottomColor: 'rgba(0,0,0,0.05)' },
    tabBtn: { flex: 1, paddingVertical: spacing.md, alignItems: 'center' },
    tabText: { fontSize: 14, fontWeight: fontWeights.semibold },
    listContainer: { flex: 1 },
    list: { padding: spacing.xl, paddingBottom: 100 },
    error: { fontSize: 13, padding: spacing.base, textAlign: 'center' },

    card: { borderRadius: 16, borderWidth: 1, padding: spacing.md, marginBottom: spacing.md },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: spacing.sm },
    typeBadge: { flexDirection: 'row', alignItems: 'center', gap: 6, paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    typeText: { fontSize: 12, fontWeight: fontWeights.bold },
    statusBadge: { borderWidth: 1, paddingHorizontal: 8, paddingVertical: 2, borderRadius: 8 },
    statusText: { fontSize: 10, fontWeight: fontWeights.bold },

    cardBody: { marginLeft: 2 },
    vehicleTitle: { fontSize: 15, fontWeight: fontWeights.semibold, marginBottom: 6 },
    metaRow: { flexDirection: 'row', alignItems: 'center', gap: 6 },
    metaText: { fontSize: 12 },

    emptyWrap: { alignItems: 'center', paddingTop: 60, gap: 8 },
    emptyTitle: { fontSize: 16, fontWeight: fontWeights.semibold },
    emptySub: { fontSize: 13, textAlign: 'center' },

    fab: {
        position: 'absolute',
        bottom: 24,
        right: 24,
        width: 56,
        height: 56,
        borderRadius: 28,
        justifyContent: 'center',
        alignItems: 'center',
        elevation: 4,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 2 },
        shadowOpacity: 0.25,
        shadowRadius: 4,
    },
});
