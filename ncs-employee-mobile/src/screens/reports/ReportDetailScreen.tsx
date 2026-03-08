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
    Alert,
    TouchableOpacity,
    RefreshControl,
} from 'react-native';
import { useRoute, RouteProp, useNavigation } from '@react-navigation/native';
import { reportsApi } from '../../api/reportsApi';
import type { ReportType } from '../../api/reportsApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import * as FileSystem from 'expo-file-system';
import * as Sharing from 'expo-sharing';

export function ReportDetailScreen() {
    const route = useRoute<RouteProp<{ Detail: { type: ReportType; title: string; icon: string; color: string } }, 'Detail'>>();
    const navigation = useNavigation();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';

    const { type, title, icon, color } = route.params;

    const [data, setData] = useState<any>(null);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [downloading, setDownloading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const load = useCallback(async () => {
        setError(null);
        try {
            const res = await reportsApi.getReportData(type);
            if (res.success && res.data) {
                // Automatically unwrap if data is nested inside data.data or similar
                const trueData = res.data.data ? res.data.data : res.data;
                setData(trueData);
            }
        } catch {
            setError(`Failed to load ${title}.`);
        } finally {
            setLoading(false);
            setRefreshing(false);
        }
    }, [type, title]);

    React.useEffect(() => { load(); }, [load]);

    const handleDownload = async () => {
        setDownloading(true);
        try {
            const blob = await reportsApi.downloadReportPdf(type);

            // Save blob to local temp file to allow sharing/viewing
            const fr = new FileReader();
            fr.onload = async () => {
                const base64data = (fr.result as string).split(',')[1];
                const fileUri = `${FileSystem.documentDirectory}${type}_report.pdf`;

                await FileSystem.writeAsStringAsync(fileUri, base64data, { encoding: FileSystem.EncodingType.Base64 });

                if (await Sharing.isAvailableAsync()) {
                    await Sharing.shareAsync(fileUri, { menuTitle: `Download ${title} PDF`, UTI: 'com.adobe.pdf' });
                } else {
                    Alert.alert('Success', `Saved to ${fileUri}`);
                }
                setDownloading(false);
            };
            fr.readAsDataURL(blob as any);

        } catch (err: any) {
            setDownloading(false);
            Alert.alert('Download Failed', err.message || 'Could not generate PDF.');
        }
    };

    // Helper to format generic JSON values inside cards
    const formatValue = (val: any): string => {
        if (val === null || val === undefined) return '—';
        if (typeof val === 'boolean') return val ? 'Yes' : 'No';
        if (typeof val === 'object') return JSON.stringify(val);
        // Rough date check
        if (typeof val === 'string' && val.includes('T') && val.endsWith('Z')) {
            return new Date(val).toLocaleDateString();
        }
        return String(val);
    };

    const renderGenericDataList = () => {
        // If the data is an array (e.g. LeaveHistory, PassHistory), render a list of cards
        if (Array.isArray(data)) {
            if (data.length === 0) return renderEmpty();

            return data.map((item, idx) => (
                <View key={idx} style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                    {Object.entries(item).map(([k, v], i, arr) => {
                        if (k === 'id' || k.endsWith('_id')) return null; // Skip IDs mostly

                        return (
                            <View key={k}>
                                <View style={styles.row}>
                                    <Text style={[styles.label, { color: themeColors.textMuted }]}>{k.replace(/_/g, ' ').toUpperCase()}</Text>
                                    <Text style={[styles.value, { color: themeColors.text }]} numberOfLines={2}>{formatValue(v)}</Text>
                                </View>
                                {i !== arr.length - 1 && <View style={[styles.divider, { backgroundColor: themeColors.border }]} />}
                            </View>
                        );
                    })}
                </View>
            ));
        }

        // If data is an object with array keys (e.g. ServiceRecord matching doc structure)
        if (typeof data === 'object' && data !== null) {
            return Object.entries(data).map(([sectionKey, sectionValue]) => (
                <View key={sectionKey} style={{ marginBottom: spacing.lg }}>
                    <Text style={[styles.sectionTitle, { color: themeColors.textSecondary }]}>
                        {sectionKey.replace(/_/g, ' ').toUpperCase()}
                    </Text>

                    {Array.isArray(sectionValue) ? (
                        sectionValue.length === 0 ? <Text style={[styles.emptyInline, { color: themeColors.textMuted }]}>No records found.</Text> :
                            sectionValue.map((item, idx) => (
                                <View key={idx} style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                    {Object.entries(item).map(([k, v], i, arr) => (
                                        <View key={k}>
                                            <View style={styles.row}>
                                                <Text style={[styles.label, { color: themeColors.textMuted }]}>{k.replace(/_/g, ' ').toUpperCase()}</Text>
                                                <Text style={[styles.value, { color: themeColors.text }]} numberOfLines={2}>{formatValue(v)}</Text>
                                            </View>
                                            {i !== arr.length - 1 && <View style={[styles.divider, { backgroundColor: themeColors.border }]} />}
                                        </View>
                                    ))}
                                </View>
                            ))
                    ) : (
                        // A flat object inside a section (e.g. `officer` details inside Service Record)
                        <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                            {Object.entries(sectionValue as object).map(([k, v], i, arr) => (
                                <View key={k}>
                                    <View style={styles.row}>
                                        <Text style={[styles.label, { color: themeColors.textMuted }]}>{k.replace(/_/g, ' ').toUpperCase()}</Text>
                                        <Text style={[styles.value, { color: themeColors.text }]} numberOfLines={2}>{formatValue(v)}</Text>
                                    </View>
                                    {i !== arr.length - 1 && <View style={[styles.divider, { backgroundColor: themeColors.border }]} />}
                                </View>
                            ))}
                        </View>
                    )}
                </View>
            ));
        }

        return renderEmpty();
    };

    const renderEmpty = () => (
        <View style={styles.emptyWrap}>
            <Ionicons name="document-text" size={56} color={themeColors.border} />
            <Text style={[styles.emptyTitle, { color: themeColors.textMuted }]}>No Data Found</Text>
            <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>There are no matching records for this report type.</Text>
        </View>
    );

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

            {/* Hero Header */}
            <View style={[styles.header, { backgroundColor: themeColors.primary }]}>
                <View style={styles.headerTop}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="arrow-back" size={24} color="#fff" />
                    </TouchableOpacity>
                    <TouchableOpacity
                        style={[styles.downloadBtn, { backgroundColor: 'rgba(255,255,255,0.2)' }]}
                        onPress={handleDownload}
                        disabled={downloading || loading || !data}
                    >
                        {downloading ? <ActivityIndicator size="small" color="#fff" /> : <Ionicons name="download-outline" size={20} color="#fff" />}
                        <Text style={styles.downloadText}>PDF</Text>
                    </TouchableOpacity>
                </View>

                <View style={styles.headerBody}>
                    <View style={[styles.heroIcon, { backgroundColor: 'rgba(255,255,255,0.2)' }]}>
                        <Ionicons name={icon as any} size={32} color="#fff" />
                    </View>
                    <View style={{ flex: 1 }}>
                        <Text style={styles.heroTitle}>{title}</Text>
                        <Text style={styles.heroSub}>Official NCS Document</Text>
                    </View>
                </View>
            </View>

            <ScrollView
                contentContainerStyle={styles.container}
                showsVerticalScrollIndicator={false}
                refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} tintColor={themeColors.primary} />}
            >
                {loading && !data ? (
                    <ActivityIndicator size="large" color={themeColors.primary} style={{ marginTop: 60 }} />
                ) : error ? (
                    <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text>
                ) : (
                    renderGenericDataList()
                )}
            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    container: { padding: spacing.xl, paddingBottom: 80 },
    error: { fontSize: 13, padding: spacing.base, textAlign: 'center', marginTop: 40 },

    header: { padding: spacing.lg, paddingBottom: spacing.xl, borderBottomLeftRadius: 24, borderBottomRightRadius: 24 },
    headerTop: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: spacing.xl },
    backBtn: { padding: 4 },
    downloadBtn: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 6, borderRadius: 16, gap: 6 },
    downloadText: { color: '#fff', fontSize: 13, fontWeight: fontWeights.bold },

    headerBody: { flexDirection: 'row', alignItems: 'center', gap: 16, paddingHorizontal: 4 },
    heroIcon: { width: 56, height: 56, borderRadius: 28, justifyContent: 'center', alignItems: 'center' },
    heroTitle: { fontSize: 24, fontWeight: fontWeights.bold, color: '#fff', marginBottom: 2 },
    heroSub: { fontSize: 13, color: 'rgba(255,255,255,0.8)' },

    sectionTitle: { fontSize: 13, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginBottom: spacing.sm, marginLeft: 4 },

    card: { borderRadius: 16, borderWidth: 1, padding: spacing.lg, marginBottom: spacing.md },
    row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10, gap: 16 },
    label: { fontSize: 12, flex: 0.8 },
    value: { fontSize: 14, fontWeight: fontWeights.semibold, flex: 1.2, textAlign: 'right' },
    divider: { height: StyleSheet.hairlineWidth },

    emptyWrap: { alignItems: 'center', paddingTop: 60, paddingHorizontal: 24 },
    emptyTitle: { fontSize: 18, fontWeight: fontWeights.bold, marginTop: 12, marginBottom: 8 },
    emptySub: { fontSize: 14, textAlign: 'center', lineHeight: 20 },
    emptyInline: { fontSize: 14, fontStyle: 'italic', marginLeft: 4, marginBottom: 12 },
});
