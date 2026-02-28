import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl, Alert } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { quarterApi } from '../../api/quarterApi';
import type { OfficerQuarterItem } from '../../api/quarterApi';
import { TransportStackParamList } from '../../navigation/TransportStack';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type Nav = NativeStackNavigationProp<TransportStackParamList, 'QuarterAllocations'>;

export function QuarterAllocationsScreen() {
  const navigation = useNavigation<Nav>();
  const [items, setItems] = useState<OfficerQuarterItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setError(null);
    try {
      const res = await quarterApi.myAllocations();
      if (res.success && res.data) setItems(res.data);
    } catch {
      setError('Failed to load allocations');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    setLoading(true);
    load();
  }, [load]);

  const accept = (item: OfficerQuarterItem) => {
    if (item.status !== 'PENDING') return;
    Alert.alert('Accept allocation', `Accept quarter ${item.quarter?.quarter_number ?? item.quarter_id}?`, [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Accept',
        onPress: async () => {
          try {
            await quarterApi.acceptAllocation(item.id);
            load();
          } catch {
            Alert.alert('Error', 'Failed to accept');
          }
        },
      },
    ]);
  };

  const reject = (item: OfficerQuarterItem) => {
    if (item.status !== 'PENDING') return;
    Alert.alert('Reject allocation', 'Reject this quarter allocation?', [
      { text: 'Cancel', style: 'cancel' },
      {
        text: 'Reject',
        style: 'destructive',
        onPress: async () => {
          try {
            await quarterApi.rejectAllocation(item.id);
            load();
          } catch {
            Alert.alert('Error', 'Failed to reject');
          }
        },
      },
    ]);
  };

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
            <View style={styles.rowContent}>
              <Text style={styles.quarter}>{item.quarter?.quarter_number ?? `Quarter #${item.quarter_id}`}</Text>
              <Text style={[styles.status, { color: item.status === 'PENDING' ? colors.textSecondary : item.status === 'ACCEPTED' ? colors.success : colors.danger }]}>{item.status}</Text>
              {item.allocated_at ? <Text style={styles.date}>Allocated: {new Date(item.allocated_at).toLocaleDateString()}</Text> : null}
            </View>
            {item.status === 'PENDING' && (
              <View style={styles.actions}>
                <TouchableOpacity style={styles.acceptBtn} onPress={() => accept(item)}>
                  <Text style={styles.acceptBtnText}>Accept</Text>
                </TouchableOpacity>
                <TouchableOpacity style={styles.rejectBtn} onPress={() => reject(item)}>
                  <Text style={styles.rejectBtnText}>Reject</Text>
                </TouchableOpacity>
              </View>
            )}
          </View>
        )}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[colors.primary]} />}
        ListEmptyComponent={<Text style={styles.empty}>No allocations</Text>}
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
  rowContent: { marginBottom: spacing.sm },
  quarter: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold, color: colors.text },
  status: { fontSize: fontSizes.sm, marginTop: 4 },
  date: { fontSize: fontSizes.xs, color: colors.textMuted, marginTop: 2 },
  actions: { flexDirection: 'row', gap: spacing.sm, marginTop: spacing.sm },
  acceptBtn: { flex: 1, backgroundColor: colors.primaryLight, paddingVertical: spacing.sm, borderRadius: 8, alignItems: 'center' },
  acceptBtnText: { fontSize: fontSizes.sm, fontWeight: fontWeights.semibold, color: colors.primary },
  rejectBtn: { flex: 1, backgroundColor: colors.dangerLight, paddingVertical: spacing.sm, borderRadius: 8, alignItems: 'center' },
  rejectBtnText: { fontSize: fontSizes.sm, fontWeight: fontWeights.semibold, color: colors.danger },
});
