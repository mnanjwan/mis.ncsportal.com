import React, { useState, useCallback } from 'react';
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
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { pharmacyApi } from '../../api/pharmacyApi';
import type { PharmacyRequisition, PharmacyStock } from '../../api/pharmacyApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import { useAppSelector } from '../../hooks/redux';

type NavProps = any;

function getStatusColor(status: string) {
    switch (status) {
        case 'APPROVED': return '#2563eb'; // blue
        case 'ISSUED': return '#8b5cf6'; // purple
        case 'DISPENSED': return '#16a34a'; // green
        case 'REJECTED': return '#dc2626'; // red
        case 'SUBMITTED': return '#d97706'; // amber
        default: return '#64748b'; // slate for DRAFT
    }
}

export function PharmacyDashboardScreen() {
    const navigation = useNavigation<NavProps>();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';
    const { user } = useAppSelector((s) => s.auth);

    const [requisitions, setRequisitions] = useState<PharmacyRequisition[]>([]);
    const [stockCount, setStockCount] = useState(0);
    const [lowStockCount, setLowStockCount] = useState(0);

    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const loadData = useCallback(async () => {
        setError(null);
        try {
            const [reqRes, stockRes, lowRes] = await Promise.all([
                pharmacyApi.requisitions(),
                pharmacyApi.stock(),
                pharmacyApi.lowStock(),
            ]);

            if (reqRes.success && reqRes.data) setRequisitions(reqRes.data);
            if (stockRes.success && stockRes.data) setStockCount(stockRes.data.length);
            if (lowRes.success && lowRes.data) setLowStockCount(lowRes.data.length);

        } catch {
            setError('Failed to load pharmacy dashboard.');
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

    const renderHeaderCards = () => (
        <View style={styles.metricsWrap}>
            <View style={[styles.metricCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                <Ionicons name="medical" size={24} color={themeColors.primary} style={styles.metricIcon} />
                <Text style={[styles.metricCount, { color: themeColors.text }]}>{stockCount}</Text>
                <Text style={[styles.metricLabel, { color: themeColors.textMuted }]}>Drugs in Stock</Text>
            </View>
            <View style={[styles.metricCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                <Ionicons name="warning" size={24} color="#dc2626" style={styles.metricIcon} />
                <Text style={[styles.metricCount, { color: themeColors.text }]}>{lowStockCount}</Text>
                <Text style={[styles.metricLabel, { color: themeColors.textMuted }]}>Low Stock Alerts</Text>
            </View>
        </View>
    );

    const renderItem = ({ item }: { item: PharmacyRequisition }) => {
        const statusColor = getStatusColor(item.status);
        const dateStr = item.submitted_at ? new Date(item.submitted_at).toLocaleDateString() : 'Draft';
        const totalItems = item.items?.length || 0;

        return (
            <TouchableOpacity
                style={[styles.itemCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
                onPress={() => navigation.navigate('RequisitionDetail', { id: item.id })}
                activeOpacity={0.7}
            >
                <View style={styles.itemHeader}>
                    <View style={[styles.itemBadge, { borderColor: statusColor }]}>
                        <Ionicons name="ellipse" size={8} color={statusColor} style={{ marginRight: 6 }} />
                        <Text style={[styles.itemBadgeText, { color: statusColor }]}>{item.status}</Text>
                    </View>
                    <Text style={[styles.itemDate, { color: themeColors.textMuted }]}>{dateStr}</Text>
                </View>

                <Text style={[styles.itemTitle, { color: themeColors.text }]}>{item.reference_number}</Text>
                <Text style={[styles.itemSubtitle, { color: themeColors.textSecondary }]}>
                    {item.command?.name || 'Local Command'} • {totalItems} drug type{totalItems !== 1 ? 's' : ''} requested
                </Text>

                {item.notes ? (
                    <Text style={[styles.itemNotes, { color: themeColors.textMuted }]} numberOfLines={1}>"{item.notes}"</Text>
                ) : null}
            </TouchableOpacity>
        );
    };

    if (loading && requisitions.length === 0 && stockCount === 0) {
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
                <View style={styles.headerTitleRow}>
                    <Ionicons name="medkit" size={28} color="#ffffff" />
                    <View>
                        <Text style={styles.headerTitle}>Pharmacy</Text>
                        <Text style={styles.headerSubtitle}>{user?.officer?.command?.name} Command</Text>
                    </View>
                </View>
            </View>

            <FlatList
                data={requisitions}
                keyExtractor={(item) => String(item.id)}
                renderItem={renderItem}
                ListHeaderComponent={
                    <View>
                        {renderHeaderCards()}
                        <Text style={[styles.sectionTitle, { color: themeColors.textSecondary }]}>RECENT REQUISITIONS</Text>
                    </View>
                }
                contentContainerStyle={styles.list}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); loadData(); }} colors={[themeColors.primary]} tintColor={themeColors.primary} />}
                ListEmptyComponent={
                    <View style={styles.emptyWrap}>
                        <Ionicons name="document-text-outline" size={48} color={themeColors.border} />
                        <Text style={[styles.emptyTitle, { color: themeColors.textMuted }]}>No Requisitions</Text>
                        <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>No drug requests have been made.</Text>
                    </View>
                }
            />

            {/* FAB to create new requisition */}
            <TouchableOpacity
                style={[styles.fab, { backgroundColor: themeColors.primary }]}
                onPress={() => navigation.navigate('CreateRequisition')}
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

    metricsWrap: { flexDirection: 'row', gap: 16, paddingHorizontal: spacing.xl, marginTop: spacing.lg, marginBottom: spacing.xl },
    metricCard: { flex: 1, borderRadius: 16, borderWidth: 1, padding: spacing.lg },
    metricIcon: { marginBottom: 8 },
    metricCount: { fontSize: 24, fontWeight: fontWeights.bold, marginBottom: 2 },
    metricLabel: { fontSize: 12, fontWeight: fontWeights.medium },

    sectionTitle: { fontSize: 12, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginHorizontal: spacing.xl, marginBottom: spacing.sm },
    list: { paddingBottom: 100 },

    itemCard: { marginHorizontal: spacing.xl, marginBottom: spacing.md, borderRadius: 16, borderWidth: 1, padding: spacing.lg },
    itemHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: spacing.sm },
    itemBadge: { flexDirection: 'row', alignItems: 'center', borderWidth: 1, paddingHorizontal: 10, paddingVertical: 4, borderRadius: 12 },
    itemBadgeText: { fontSize: 10, fontWeight: fontWeights.bold, letterSpacing: 0.5 },
    itemDate: { fontSize: 12 },

    itemTitle: { fontSize: 16, fontWeight: fontWeights.bold, marginBottom: 4 },
    itemSubtitle: { fontSize: 13, marginBottom: 8 },
    itemNotes: { fontSize: 13, fontStyle: 'italic', marginTop: 4 },

    emptyWrap: { alignItems: 'center', paddingTop: 60, paddingHorizontal: 24 },
    emptyTitle: { fontSize: 16, fontWeight: fontWeights.bold, marginTop: 12, marginBottom: 4 },
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
