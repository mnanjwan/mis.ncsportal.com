import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { quarterApi } from '../../api/quarterApi';
import type { QuarterRequestItem } from '../../api/quarterApi';
import { TransportStackParamList } from '../../navigation/TransportStack';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type Nav = NativeStackNavigationProp<TransportStackParamList, 'QuarterRequests'>;

export function QuarterRequestsScreen() {
  const navigation = useNavigation<Nav>();
  const [items, setItems] = useState<QuarterRequestItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setError(null);
    try {
      const res = await quarterApi.myRequests();
      if (res.success && res.data) setItems(res.data);
    } catch {
      setError('Failed to load requests');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    setLoading(true);
    load();
  }, [load]);

  const statusColor = (s: string) =>
    s === 'APPROVED' ? colors.success : s === 'REJECTED' ? colors.danger : colors.textSecondary;

  if (loading && items.length === 0) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <TouchableOpacity style={styles.addBtn} onPress={() => navigation.navigate('QuarterRequestSubmit')}>
        <Text style={styles.addBtnText}>+ New request</Text>
      </TouchableOpacity>
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        data={items}
        keyExtractor={(i) => String(i.id)}
        renderItem={({ item }) => (
          <View style={styles.row}>
            <Text style={styles.id}>Request #{item.id}</Text>
            <Text style={styles.quarter}>{item.quarter?.quarter_number ?? item.preferred_quarter_type ?? '—'}</Text>
            <Text style={[styles.status, { color: statusColor(item.status) }]}>{item.status}</Text>
            {item.created_at ? <Text style={styles.date}>{new Date(item.created_at).toLocaleDateString()}</Text> : null}
          </View>
        )}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[colors.primary]} />}
        ListEmptyComponent={<Text style={styles.empty}>No quarter requests</Text>}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.background },
  addBtn: { padding: spacing.base, backgroundColor: colors.primaryMuted, alignItems: 'center', borderBottomWidth: 1, borderBottomColor: colors.borderLight },
  addBtnText: { fontSize: fontSizes.sm, fontWeight: fontWeights.semibold, color: colors.primary },
  error: { color: colors.danger, fontSize: fontSizes.sm, padding: spacing.base, textAlign: 'center' },
  list: { padding: spacing.base, paddingBottom: spacing['3xl'] },
  empty: { fontSize: fontSizes.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
  row: { backgroundColor: colors.surface, borderRadius: 12, padding: spacing.base, marginBottom: spacing.sm, borderWidth: 1, borderColor: colors.borderLight },
  id: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold, color: colors.text },
  quarter: { fontSize: fontSizes.sm, color: colors.textSecondary, marginTop: 4 },
  status: { fontSize: fontSizes.xs, fontWeight: fontWeights.medium, marginTop: 4 },
  date: { fontSize: fontSizes.xs, color: colors.textMuted, marginTop: 2 },
});
