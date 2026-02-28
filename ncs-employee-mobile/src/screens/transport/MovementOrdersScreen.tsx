import React, { useCallback, useEffect, useState } from 'react';
import { View, Text, StyleSheet, FlatList, TouchableOpacity, ActivityIndicator, RefreshControl } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAppSelector } from '../../hooks/redux';
import { movementApi } from '../../api/movementApi';
import type { MovementOrderItem } from '../../api/movementApi';
import { TransportStackParamList } from '../../navigation/TransportStack';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type Nav = NativeStackNavigationProp<TransportStackParamList, 'MovementOrders'>;

export function MovementOrdersScreen() {
  const navigation = useNavigation<Nav>();
  const officerId = useAppSelector((s) => s.auth.user?.officer?.id);
  const [items, setItems] = useState<MovementOrderItem[]>([]);
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
      const res = await movementApi.list({ officer_id: officerId, per_page: 50 });
      if (res.success && res.data) setItems(res.data);
    } catch {
      setError('Failed to load movement orders');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, [officerId]);

  useEffect(() => {
    setLoading(true);
    load();
  }, [load]);

  const onItem = (item: MovementOrderItem) => navigation.navigate('MovementOrderDetail', { id: item.id });

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
          <TouchableOpacity style={styles.row} onPress={() => onItem(item)} activeOpacity={0.7}>
            <View style={styles.rowContent}>
              <Text style={styles.orderNumber}>{item.order_number}</Text>
              <Text style={styles.subtitle}>
                {item.from_command?.name ?? '—'} → {item.to_command?.name ?? '—'}
              </Text>
              <Text style={styles.date}>Effective: {item.effective_date}</Text>
            </View>
            <Text style={styles.chevron}>›</Text>
          </TouchableOpacity>
        )}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[colors.primary]} />}
        ListEmptyComponent={<Text style={styles.empty}>No movement orders</Text>}
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
  row: { flexDirection: 'row', alignItems: 'center', backgroundColor: colors.surface, borderRadius: 12, padding: spacing.base, marginBottom: spacing.sm, borderWidth: 1, borderColor: colors.borderLight },
  rowContent: { flex: 1 },
  orderNumber: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold, color: colors.text },
  subtitle: { fontSize: fontSizes.sm, color: colors.textSecondary, marginTop: 4 },
  date: { fontSize: fontSizes.xs, color: colors.textMuted, marginTop: 4 },
  chevron: { fontSize: fontSizes['2xl'], color: colors.textMuted, marginLeft: spacing.sm },
});
