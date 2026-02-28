import React, { useState, useEffect } from 'react';
import { View, Text, Switch, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import {
  getNotificationPreferences,
  setNotificationPreferences,
  type NotificationPreferences,
} from '../../utils/notificationPreferences';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

const LABELS: Record<keyof NotificationPreferences, string> = {
  leave_notifications: 'Leave applications',
  pass_notifications: 'Pass applications',
  emolument_notifications: 'Emolument',
  chat_notifications: 'Chat',
  fleet_notifications: 'Fleet & transport',
  pharmacy_notifications: 'Health & pharmacy',
  system_notifications: 'System & role',
  quarters_notifications: 'Quarters & accommodation',
};

export function NotificationSettingsScreen() {
  const [prefs, setPrefs] = useState<NotificationPreferences | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    getNotificationPreferences().then((p) => {
      setPrefs(p);
      setLoading(false);
    });
  }, []);

  const update = async (key: keyof NotificationPreferences, value: boolean) => {
    if (!prefs) return;
    const next = { ...prefs, [key]: value };
    setPrefs(next);
    await setNotificationPreferences({ [key]: value });
  };

  if (loading || !prefs) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <ScrollView style={styles.scroll} contentContainerStyle={styles.container}>
      <Text style={styles.title}>Notification preferences</Text>
      <Text style={styles.subtitle}>Choose which notifications to receive (Expo Push).</Text>
      {(Object.keys(LABELS) as (keyof NotificationPreferences)[]).map((key) => (
        <View key={key} style={styles.row}>
          <Text style={styles.label}>{LABELS[key]}</Text>
          <Switch
            value={prefs[key]}
            onValueChange={(v) => update(key, v)}
            trackColor={{ false: colors.border, true: colors.primaryLight }}
            thumbColor={prefs[key] ? colors.primary : colors.textMuted}
          />
        </View>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1, backgroundColor: colors.background },
  container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.background },
  title: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.bold,
    color: colors.text,
    marginBottom: spacing.xs,
  },
  subtitle: {
    fontSize: fontSizes.sm,
    color: colors.textSecondary,
    marginBottom: spacing.xl,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.base,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: colors.borderLight,
  },
  label: { fontSize: fontSizes.base, color: colors.text },
});
