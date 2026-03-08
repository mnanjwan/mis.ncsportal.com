import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  Switch,
  StyleSheet,
  ScrollView,
  ActivityIndicator,
  SafeAreaView,
  Platform,
  StatusBar,
  useColorScheme,
} from 'react-native';
import {
  getNotificationPreferences,
  setNotificationPreferences,
  type NotificationPreferences,
} from '../../utils/notificationPreferences';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type PrefKey = keyof NotificationPreferences;

type PrefEntry = {
  key: PrefKey;
  label: string;
  description: string;
  icon: string;
  iconColor: string;
  iconBg: string;
};

const PREF_CONFIG: PrefEntry[] = [
  { key: 'leave_notifications', label: 'Leave Applications', description: 'Approvals, rejections & updates', icon: 'calendar', iconColor: '#f97316', iconBg: '#fef2f2' },
  { key: 'pass_notifications', label: 'Pass Applications', description: 'Pass request status changes', icon: 'card', iconColor: '#10b981', iconBg: '#ecfdf5' },
  { key: 'emolument_notifications', label: 'Emoluments', description: 'Document review and processing', icon: 'cash', iconColor: '#3b82f6', iconBg: '#eff6ff' },
  { key: 'chat_notifications', label: 'Chat Messages', description: 'New messages in your rooms', icon: 'chatbubbles', iconColor: '#0ea5e9', iconBg: '#f0f9ff' },
  { key: 'fleet_notifications', label: 'Fleet & Transport', description: 'Vehicle requests and approvals', icon: 'car', iconColor: '#d97706', iconBg: '#fef3c7' },
  { key: 'pharmacy_notifications', label: 'Health & Pharmacy', description: 'Drug requisitions and stock alerts', icon: 'medkit', iconColor: '#a855f7', iconBg: '#fdf4ff' },
  { key: 'quarters_notifications', label: 'Quarters', description: 'Accommodation requests', icon: 'home', iconColor: '#2563eb', iconBg: '#f0f9ff' },
  { key: 'system_notifications', label: 'System & Role', description: 'Account changes and admin alerts', icon: 'settings', iconColor: '#64748b', iconBg: '#f8fafc' },
];

export function NotificationSettingsScreen() {
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const [prefs, setPrefs] = useState<NotificationPreferences | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getNotificationPreferences().then((p) => {
      setPrefs(p);
      setLoading(false);
    });
  }, []);

  const update = async (key: PrefKey, value: boolean) => {
    if (!prefs) return;
    const next = { ...prefs, [key]: value };
    setPrefs(next);
    await setNotificationPreferences({ [key]: value });
  };

  if (loading || !prefs) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  const allEnabled = PREF_CONFIG.every(e => prefs[e.key]);

  const toggleAll = async () => {
    const newVal = !allEnabled;
    const next = { ...prefs };
    PREF_CONFIG.forEach(e => { (next as any)[e.key] = newVal; });
    setPrefs(next);
    for (const e of PREF_CONFIG) {
      await setNotificationPreferences({ [e.key]: newVal });
    }
  };

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
      <ScrollView
        style={{ flex: 1 }}
        contentContainerStyle={styles.container}
        showsVerticalScrollIndicator={false}
      >
        {/* Header Card */}
        <View style={[styles.headerCard, { backgroundColor: themeColors.primary }]}>
          <Ionicons name="notifications" size={32} color="#ffffff" />
          <View style={{ flex: 1, marginLeft: spacing.md }}>
            <Text style={styles.headerTitle}>Push Notifications</Text>
            <Text style={styles.headerSub}>Stay updated on your NCS activity</Text>
          </View>
        </View>

        {/* Toggle All Row */}
        <View style={[styles.toggleAllRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          <Text style={[styles.toggleAllLabel, { color: themeColors.text }]}>All Notifications</Text>
          <Switch
            value={allEnabled}
            onValueChange={toggleAll}
            trackColor={{ false: themeColors.border, true: themeColors.primaryLight }}
            thumbColor={allEnabled ? themeColors.primary : themeColors.textMuted}
          />
        </View>

        {/* Per-Type Preferences */}
        <Text style={[styles.sectionLabel, { color: themeColors.textMuted }]}>BY MODULE</Text>
        <View style={[styles.prefCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          {PREF_CONFIG.map((entry, idx) => (
            <View key={entry.key}>
              <View style={styles.prefRow}>
                <View style={[styles.prefIcon, { backgroundColor: entry.iconBg }]}>
                  <Ionicons name={entry.icon as any} size={20} color={entry.iconColor} />
                </View>
                <View style={styles.prefInfo}>
                  <Text style={[styles.prefLabel, { color: themeColors.text }]}>{entry.label}</Text>
                  <Text style={[styles.prefDesc, { color: themeColors.textMuted }]}>{entry.description}</Text>
                </View>
                <Switch
                  value={prefs[entry.key]}
                  onValueChange={(v) => update(entry.key, v)}
                  trackColor={{ false: themeColors.border, true: themeColors.primaryLight }}
                  thumbColor={prefs[entry.key] ? themeColors.primary : themeColors.textMuted}
                />
              </View>
              {idx < PREF_CONFIG.length - 1 && (
                <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
              )}
            </View>
          ))}
        </View>

        <Text style={[styles.footerNote, { color: themeColors.textMuted }]}>
          Changes take effect immediately. Notifications are delivered to your device even when the app is closed.
        </Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  container: { padding: spacing.xl, paddingBottom: 60 },

  headerCard: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 20,
    padding: spacing.xl,
    marginBottom: spacing['2xl'],
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: fontWeights.bold,
    color: '#ffffff',
    marginBottom: 2,
  },
  headerSub: {
    fontSize: 13,
    color: 'rgba(255,255,255,0.8)',
  },

  toggleAllRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    borderRadius: 16,
    borderWidth: 1,
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
    marginBottom: spacing.xl,
  },
  toggleAllLabel: {
    fontSize: 16,
    fontWeight: fontWeights.semibold,
  },

  sectionLabel: {
    fontSize: 12,
    fontWeight: fontWeights.bold,
    letterSpacing: 1.2,
    marginBottom: spacing.md,
    paddingHorizontal: spacing.xs,
  },
  prefCard: {
    borderRadius: 16,
    borderWidth: 1,
    overflow: 'hidden',
    marginBottom: spacing.xl,
  },
  prefRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: spacing.md,
  },
  prefIcon: {
    width: 38,
    height: 38,
    borderRadius: 19,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  prefInfo: { flex: 1 },
  prefLabel: { fontSize: 15, fontWeight: fontWeights.medium, marginBottom: 2 },
  prefDesc: { fontSize: 12 },
  divider: { height: StyleSheet.hairlineWidth, marginLeft: 70 },

  footerNote: {
    fontSize: 12,
    textAlign: 'center',
    lineHeight: 18,
    paddingHorizontal: spacing.xl,
  },
});
