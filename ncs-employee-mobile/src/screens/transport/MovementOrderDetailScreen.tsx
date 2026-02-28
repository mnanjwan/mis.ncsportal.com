import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet, ScrollView, ActivityIndicator } from 'react-native';
import { useRoute, RouteProp } from '@react-navigation/native';
import { movementApi } from '../../api/movementApi';
import type { MovementOrderItem } from '../../api/movementApi';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type RouteParams = { MovementOrderDetail: { id: number } };

export function MovementOrderDetailScreen() {
  const route = useRoute<RouteProp<RouteParams, 'MovementOrderDetail'>>();
  const id = route.params?.id ?? 0;
  const [order, setOrder] = useState<MovementOrderItem | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const res = await movementApi.show(id);
        if (!cancelled && res.success && res.data) setOrder(res.data);
        else if (!cancelled && !res.success) setError('Failed to load order');
      } catch {
        if (!cancelled) setError('Failed to load order');
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [id]);

  if (loading) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }
  if (error || !order) {
    return (
      <View style={styles.centered}>
        <Text style={styles.error}>{error ?? 'Order not found'}</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.scroll} contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>
      <View style={styles.section}>
        <Text style={styles.label}>Order number</Text>
        <Text style={styles.value}>{order.order_number}</Text>
      </View>
      <View style={styles.section}>
        <Text style={styles.label}>From command</Text>
        <Text style={styles.value}>{order.from_command?.name ?? '—'}</Text>
      </View>
      <View style={styles.section}>
        <Text style={styles.label}>To command</Text>
        <Text style={styles.value}>{order.to_command?.name ?? '—'}</Text>
      </View>
      <View style={styles.section}>
        <Text style={styles.label}>Effective date</Text>
        <Text style={styles.value}>{order.effective_date}</Text>
      </View>
      {order.reason ? (
        <View style={styles.section}>
          <Text style={styles.label}>Reason</Text>
          <Text style={styles.value}>{order.reason}</Text>
        </View>
      ) : null}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1, backgroundColor: colors.background },
  container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.background },
  error: { color: colors.danger, fontSize: fontSizes.base },
  section: { marginBottom: spacing.lg },
  label: { fontSize: fontSizes.sm, color: colors.textMuted, marginBottom: 4 },
  value: { fontSize: fontSizes.base, fontWeight: fontWeights.medium, color: colors.text },
});
