import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, ActivityIndicator, RefreshControl } from 'react-native';
import { useAppSelector } from '../../hooks/redux';
import { dutyRosterApi } from '../../api/dutyRosterApi';
import type { RosterAssignmentItem } from '../../api/dutyRosterApi';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

export function MyScheduleScreen() {
  const officerId = useAppSelector((s) => s.auth.user?.officer?.id);
  const [items, setItems] = useState<RosterAssignmentItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setError(null);
    if (officerId == null) {
      setLoading(false);
      return;
    }
    try {
      const res = await dutyRosterApi.officerSchedule(officerId);
      if (res.success && res.data) setItems(res.data);
    } catch {
      setError('Failed to load schedule');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [officerId]);

  useEffect(() => {
    setLoading(true);
    load();
  }, [load]);

  if (loading && items.length === 0) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        data={items}
        keyExtractor={(i) => String(i.id)}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <Text style={styles.date}>{item.duty_date}</Text>
            <Text style={styles.dutyType}>{item.duty_type}</Text>
            {item.roster?.command?.name ? <Text style={styles.command}>{item.roster.command.name}</Text> : null}
          </View>
        )}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[colors.primary]} />}
        ListEmptyComponent={<Text style={styles.empty}>No duty assignments</Text>}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.background },
  error: { color: colors.danger, fontSize: fontSizes.sm, padding: spacing.base, textAlign: 'center' },
  list: { padding: spacing.base, paddingBottom: spacing['3xl'] },
  empty: { fontSize: fontSizes.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
  row: { backgroundColor: colors.surface, borderRadius: 12, padding: spacing.base, marginBottom: spacing.sm, borderWidth: 1, borderColor: colors.borderLight },
  date: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold, color: colors.text },
  dutyType: { fontSize: fontSizes.sm, color: colors.primary, marginTop: 4 },
  command: { fontSize: fontSizes.sm, color: colors.textSecondary, marginTop: 2 },
});
