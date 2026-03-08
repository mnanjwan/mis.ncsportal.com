import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, SafeAreaView, StatusBar, useColorScheme } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import type { ReportType } from '../../api/reportsApi';

type NavProps = any;

const REPORTS: { type: ReportType; title: string; subtitle: string; icon: string; bg: string; color: string }[] = [
    { type: 'service-record', title: 'Service Record', subtitle: 'Complete career history & promotions', icon: 'document-text', bg: '#ecfdf5', color: '#10b981' },
    { type: 'leave-history', title: 'Leave History', subtitle: 'All leave applications & balances', icon: 'airplane', bg: '#eff6ff', color: '#3b82f6' },
    { type: 'pass-history', title: 'Pass History', subtitle: 'All pass applications & short absences', icon: 'ticket', bg: '#fef3c7', color: '#d97706' },
    { type: 'emolument-history', title: 'Emolument History', subtitle: 'Emolument claims and payment history', icon: 'wallet', bg: '#fef2f2', color: '#e11d48' },
    { type: 'posting-history', title: 'Posting History', subtitle: 'All posting & transfer orders', icon: 'map', bg: '#fdf4ff', color: '#a855f7' },
    { type: 'course-history', title: 'Course History', subtitle: 'Training courses & seminars attended', icon: 'school', bg: '#f0f9ff', color: '#0ea5e9' },
    { type: 'duty-roster', title: 'Duty Roster', subtitle: 'Current duty assignments', icon: 'calendar', bg: '#fff7ed', color: '#ea580c' },
    { type: 'quarter-history', title: 'Quarter Allocation', subtitle: 'Current and past quarters', icon: 'home', bg: '#f8fafc', color: '#475569' },
    { type: 'query-history', title: 'Query History', subtitle: 'All queries received and responded', icon: 'help-circle', bg: '#fefce8', color: '#ca8a04' },
];

export function ReportsMenuScreen() {
    const navigation = useNavigation<NavProps>();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
            <ScrollView style={styles.scroll} contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>

                <Text style={[styles.title, { color: themeColors.text }]}>My Reports & Analytics</Text>
                <Text style={[styles.subtitle, { color: themeColors.textSecondary }]}>
                    View and download your official NCS records, history, and analytics.
                </Text>

                <View style={styles.grid}>
                    {REPORTS.map((item) => (
                        <TouchableOpacity
                            key={item.type}
                            style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
                            onPress={() => navigation.navigate('ReportDetail', { type: item.type, title: item.title, icon: item.icon, color: item.color })}
                            activeOpacity={0.7}
                        >
                            <View style={styles.cardHeader}>
                                <View style={[styles.iconWrap, { backgroundColor: item.bg }]}>
                                    <Ionicons name={item.icon as any} size={22} color={item.color} />
                                </View>
                                <Ionicons name="chevron-forward" size={18} color={themeColors.textMuted} />
                            </View>
                            <Text style={[styles.cardTitle, { color: themeColors.text }]} numberOfLines={1}>{item.title}</Text>
                            <Text style={[styles.cardSubtitle, { color: themeColors.textMuted }]} numberOfLines={2}>{item.subtitle}</Text>
                        </TouchableOpacity>
                    ))}
                </View>

            </ScrollView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    scroll: { flex: 1 },
    container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },

    title: { fontSize: 24, fontWeight: fontWeights.bold, marginBottom: 4 },
    subtitle: { fontSize: 13, lineHeight: 18, marginBottom: spacing.xl },

    grid: { flexDirection: 'row', flexWrap: 'wrap', justifyContent: 'space-between', gap: 12 },
    card: {
        width: '48%',
        borderRadius: 16,
        padding: spacing.md,
        marginBottom: spacing.xs,
        borderWidth: 1,
        height: 140,
    },
    cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 12 },
    iconWrap: { width: 44, height: 44, borderRadius: 22, justifyContent: 'center', alignItems: 'center' },
    cardTitle: { fontSize: 14, fontWeight: fontWeights.bold, marginBottom: 4 },
    cardSubtitle: { fontSize: 11, lineHeight: 16 },
});
