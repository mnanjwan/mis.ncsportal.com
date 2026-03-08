import React, { useState, useCallback, useMemo } from 'react';
import {
    View,
    Text,
    StyleSheet,
    ActivityIndicator,
    FlatList,
    RefreshControl,
    TouchableOpacity,
    SafeAreaView,
    StatusBar,
    useColorScheme,
    ScrollView,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { useAppSelector } from '../../hooks/redux';
import { approvalsApi } from '../../api/approvalsApi';
import type { ApprovalsDashboardStats, PendingApprovalItem, ApprovalModuleType } from '../../api/approvalsApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type NavProps = any; // Stack params to be defined later

function getModuleConfig(module: string) {
    switch (module) {
        case 'pass': return { icon: 'ticket', bg: '#ecfdf5', color: '#10b981', label: 'Pass' };
        case 'leave': return { icon: 'airplane', bg: '#eff6ff', color: '#3b82f6', label: 'Leave' };
        case 'emolument': return { icon: 'wallet', bg: '#fef2f2', color: '#e11d48', label: 'Emolument' };
        case 'manning': return { icon: 'people', bg: '#fff7ed', color: '#ea580c', label: 'Manning' };
        case 'quarters': return { icon: 'home', bg: '#fdf4ff', color: '#a855f7', label: 'Quarters' };
        case 'fleet': return { icon: 'car-sport', bg: '#f0f9ff', color: '#0ea5e9', label: 'Fleet' };
        case 'pharmacy': return { icon: 'medkit', bg: '#fefce8', color: '#ca8a04', label: 'Pharmacy' };
        default: return { icon: 'document', bg: '#f1f5f9', color: '#64748b', label: 'Other' };
    }
}

export function ApprovalsDashboardScreen() {
    const navigation = useNavigation<NavProps>();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';
    const { user } = useAppSelector((s) => s.auth);

    const [stats, setStats] = useState<ApprovalsDashboardStats | null>(null);
    const [pendingItems, setPendingItems] = useState<PendingApprovalItem[]>([]);
    const [selectedModule, setSelectedModule] = useState<ApprovalModuleType | 'all'>('all');

    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const loadData = useCallback(async () => {
        setError(null);
        try {
            const [statsRes, pendingRes] = await Promise.all([
                approvalsApi.dashboardStats(),
                approvalsApi.pendingItems({ per_page: 50 }) // Load top 50 pending
            ]);

            if (statsRes.success && statsRes.data) setStats(statsRes.data);
            if (pendingRes.success && pendingRes.data) setPendingItems(pendingRes.data);
        } catch {
            setError('Failed to load unified inbox.');
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, []);

    useFocusEffect(
        useCallback(() => {
            setLoading(true);
            loadData();
        }, [loadData])
    );

    const filteredItems = useMemo(() => {
        if (selectedModule === 'all') return pendingItems;
        return pendingItems.filter(item => item.module === selectedModule);
    }, [pendingItems, selectedModule]);

    const handleItemPress = (item: PendingApprovalItem) => {
        if (item.module === 'pass') navigation.navigate('MyRequests', { screen: 'PassDetail', params: { id: item.id } });
        else if (item.module === 'leave') navigation.navigate('MyRequests', { screen: 'LeaveDetail', params: { id: item.id } });
        else if (item.module === 'fleet') navigation.navigate('Transport', { screen: 'FleetRequestDetail', params: { id: item.id } });
        else if (item.module === 'emolument') navigation.navigate('MyRequests', { screen: 'EmolumentDetail', params: { id: item.id } });
        // Future routing for remaining modules...
        else alert(`Routing for module [${item.module}] requested...`);
    };

    const renderStatsCards = () => {
        if (!stats) return null;

        // Convert by_module object to array to render easily
        const moduleEntries = Object.entries(stats.by_module) as [ApprovalModuleType, { pending: number; label: string }][];

        return (
            <View style={styles.statsWrap}>
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.statsScroll}>
                    {moduleEntries.map(([mod, data]) => {
                        const cfg = getModuleConfig(mod);
                        if (data.pending === 0 && mod !== 'pass' && mod !== 'leave' && mod !== 'emolument') return null; // Hide sterile 0s unless core
                        const selected = selectedModule === mod;

                        return (
                            <TouchableOpacity
                                key={mod}
                                style={[
                                    styles.statCard,
                                    { backgroundColor: themeColors.surface, borderColor: selected ? themeColors.primary : themeColors.border },
                                    selected && { backgroundColor: themeColors.primaryLight + '20' }
                                ]}
                                onPress={() => setSelectedModule(selected ? 'all' : mod)}
                                activeOpacity={0.7}
                            >
                                <View style={[styles.statIconWrap, { backgroundColor: cfg.bg }]}>
                                    <Ionicons name={cfg.icon as any} size={18} color={cfg.color} />
                                </View>
                                <Text style={[styles.statCount, { color: themeColors.text }]}>{data.pending}</Text>
                                <Text style={[styles.statLabel, { color: themeColors.textMuted }]}>{cfg.label}</Text>
                            </TouchableOpacity>
                        );
                    })}
                </ScrollView>
            </View>
        );
    };

    const renderItem = ({ item }: { item: PendingApprovalItem }) => {
        const cfg = getModuleConfig(item.module);
        const dateStr = new Date(item.submitted_at).toLocaleDateString([], { day: 'numeric', month: 'short' });
        const isUrgent = item.priority === 'urgent' || item.priority === 'high';

        return (
            <TouchableOpacity
                style={[styles.itemCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
                onPress={() => handleItemPress(item)}
                activeOpacity={0.7}
            >
                <View style={styles.itemHeader}>
                    <View style={[styles.itemBadge, { backgroundColor: cfg.bg }]}>
                        <Ionicons name={cfg.icon as any} size={12} color={cfg.color} />
                        <Text style={[styles.itemBadgeText, { color: cfg.color }]}>{cfg.label.toUpperCase()}</Text>
                    </View>
                    {isUrgent && (
                        <View style={[styles.urgentBadge, { backgroundColor: '#fef2f2', borderColor: '#f87171' }]}>
                            <Text style={[styles.urgentText, { color: '#dc2626' }]}>URGENT</Text>
                        </View>
                    )}
                    <Text style={[styles.itemDate, { color: themeColors.textMuted, marginLeft: 'auto' }]}>{dateStr}</Text>
                </View>

                <Text style={[styles.itemOfficer, { color: themeColors.text }]}>
                    {item.officer.rank} {item.officer.name}
                </Text>
                <Text style={[styles.itemSummary, { color: themeColors.textSecondary }]} numberOfLines={2}>
                    {item.summary}
                </Text>

                <View style={[styles.itemDivider, { backgroundColor: themeColors.border }]} />

                <View style={styles.itemFooter}>
                    <Text style={[styles.itemAction, { color: themeColors.primary }]}>
                        Requires: {item.action_required.toUpperCase()}
                    </Text>
                    <Ionicons name="chevron-forward" size={16} color={themeColors.primary} />
                </View>
            </TouchableOpacity>
        );
    };

    if (loading && pendingItems.length === 0 && !stats) {
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
                <Text style={styles.headerTitle}>Approvals Inbox</Text>
                <Text style={styles.headerSub}>
                    {stats ? `${stats.total_pending} items pending your action` : 'Loading unified inbox...'}
                </Text>
            </View>

            <FlatList
                data={filteredItems}
                keyExtractor={(item) => `${item.module}-${item.id}`}
                renderItem={renderItem}
                ListHeaderComponent={
                    <View>
                        {renderStatsCards()}
                        <View style={styles.listHeaderRow}>
                            <Text style={[styles.sectionTitle, { color: themeColors.textSecondary }]}>
                                {selectedModule === 'all' ? 'PENDING ACTIONS' : `${getModuleConfig(selectedModule).label.toUpperCase()} REQUESTS`}
                            </Text>
                            {selectedModule !== 'all' && (
                                <TouchableOpacity onPress={() => setSelectedModule('all')}>
                                    <Text style={[styles.clearFilter, { color: themeColors.primary }]}>Clear Filter</Text>
                                </TouchableOpacity>
                            )}
                        </View>
                    </View>
                }
                contentContainerStyle={styles.list}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[themeColors.primary]} tintColor={themeColors.primary} />}
                ListEmptyComponent={
                    <View style={styles.emptyWrap}>
                        <Ionicons name="checkmark-done-circle" size={56} color={themeColors.border} />
                        <Text style={[styles.emptyTitle, { color: themeColors.textMuted }]}>Inbox Zero</Text>
                        <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>
                            {selectedModule === 'all'
                                ? "You don't have any pending approvals across any module."
                                : `No pending ${getModuleConfig(selectedModule).label} requests.`}
                        </Text>
                    </View>
                }
            />
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },

    header: { padding: spacing.xl, paddingBottom: spacing.xl, borderBottomLeftRadius: 24, borderBottomRightRadius: 24 },
    headerTitle: { fontSize: 24, fontWeight: fontWeights.bold, color: '#fff', marginBottom: 4 },
    headerSub: { fontSize: 13, color: 'rgba(255,255,255,0.8)' },

    statsWrap: { marginTop: spacing.md, marginBottom: spacing.lg },
    statsScroll: { paddingHorizontal: spacing.xl, paddingBottom: 4, gap: 12 },
    statCard: { width: 100, height: 110, borderRadius: 16, borderWidth: 1, padding: spacing.md, alignItems: 'center', justifyContent: 'center' },
    statIconWrap: { width: 36, height: 36, borderRadius: 18, justifyContent: 'center', alignItems: 'center', marginBottom: 8 },
    statCount: { fontSize: 22, fontWeight: fontWeights.bold, marginBottom: 2 },
    statLabel: { fontSize: 11, fontWeight: fontWeights.medium },

    list: { paddingBottom: 100 },
    listHeaderRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: spacing.xl, marginBottom: spacing.sm },
    sectionTitle: { fontSize: 12, fontWeight: fontWeights.bold, letterSpacing: 1.1 },
    clearFilter: { fontSize: 12, fontWeight: fontWeights.bold },

    itemCard: { marginHorizontal: spacing.xl, marginBottom: spacing.md, borderRadius: 16, borderWidth: 1, padding: spacing.md },
    itemHeader: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.sm },
    itemBadge: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 8, paddingVertical: 4, borderRadius: 8, gap: 4 },
    itemBadgeText: { fontSize: 10, fontWeight: fontWeights.bold, letterSpacing: 0.5 },
    urgentBadge: { borderWidth: 1, paddingHorizontal: 6, paddingVertical: 2, borderRadius: 6, marginLeft: 8 },
    urgentText: { fontSize: 9, fontWeight: fontWeights.bold },
    itemDate: { fontSize: 11 },

    itemOfficer: { fontSize: 15, fontWeight: fontWeights.bold, marginBottom: 4 },
    itemSummary: { fontSize: 13, lineHeight: 18 },

    itemDivider: { height: StyleSheet.hairlineWidth, marginVertical: 12 },
    itemFooter: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center' },
    itemAction: { fontSize: 12, fontWeight: fontWeights.bold, letterSpacing: 0.5 },

    emptyWrap: { alignItems: 'center', paddingTop: 60, paddingHorizontal: 24 },
    emptyTitle: { fontSize: 18, fontWeight: fontWeights.bold, marginTop: 12, marginBottom: 8 },
    emptySub: { fontSize: 14, textAlign: 'center', lineHeight: 20 },
});
