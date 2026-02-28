import React, { useCallback, useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
} from 'react-native';
import { notificationApi } from '../../api/notificationApi';
import type { NotificationItem } from '../../api/notificationApi';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

export function NotificationListScreen() {
  const [items, setItems] = useState<NotificationItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setError(null);
    try {
      const res = await notificationApi.list({ per_page: 50 });
      if (res.success && res.data) {
        setItems(res.data);
      }
    } catch {
      setError('Failed to load notifications');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useEffect(() => {
    setLoading(true);
    load();
  }, [load]);

  const markRead = async (item: NotificationItem) => {
    if (item.is_read) return;
    try {
      await notificationApi.markAsRead(item.id);
      setItems((prev) => prev.map((n) => (n.id === item.id ? { ...n, is_read: true } : n)));
    } catch {
      // ignore
    }
  };

  const markAllRead = async () => {
    try {
      await notificationApi.markAllAsRead();
      setItems((prev) => prev.map((n) => ({ ...n, is_read: true })));
    } catch {
      // ignore
    }
  };

  const unreadCount = items.filter((n) => !n.is_read).length;

  const renderItem = ({ item }: { item: NotificationItem }) => (
    <TouchableOpacity
      style={[styles.row, !item.is_read && styles.rowUnread]}
      onPress={() => markRead(item)}
      activeOpacity={0.7}
    >
      <View style={styles.content}>
        <Text style={styles.title} numberOfLines={1}>{item.title ?? 'Notification'}</Text>
        {item.message ? <Text style={styles.message} numberOfLines={2}>{item.message}</Text> : null}
        <Text style={styles.time}>{new Date(item.created_at).toLocaleDateString()} {new Date(item.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</Text>
      </View>
      {!item.is_read && <View style={styles.unreadDot} />}
    </TouchableOpacity>
  );

  if (loading && items.length === 0) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <View style={styles.container}>
      {unreadCount > 0 ? (
        <TouchableOpacity style={styles.markAllBtn} onPress={markAllRead}>
          <Text style={styles.markAllText}>Mark all as read</Text>
        </TouchableOpacity>
      ) : null}
      {error ? <Text style={styles.error}>{error}</Text> : null}
      <FlatList
        data={items}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[colors.primary]} />}
        ListEmptyComponent={<Text style={styles.empty}>No notifications</Text>}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: colors.background },
  markAllBtn: { padding: spacing.base, backgroundColor: colors.primaryMuted, alignItems: 'center', borderBottomWidth: 1, borderBottomColor: colors.borderLight },
  markAllText: { fontSize: fontSizes.sm, fontWeight: fontWeights.semibold, color: colors.primary },
  error: { color: colors.danger, fontSize: fontSizes.sm, padding: spacing.base, textAlign: 'center' },
  list: { padding: spacing.base, paddingBottom: spacing['3xl'] },
  empty: { fontSize: fontSizes.sm, color: colors.textMuted, textAlign: 'center', marginTop: spacing.xl },
  row: { flexDirection: 'row', alignItems: 'flex-start', backgroundColor: colors.surface, borderRadius: 12, padding: spacing.base, marginBottom: spacing.sm, borderWidth: 1, borderColor: colors.borderLight },
  rowUnread: { borderLeftWidth: 4, borderLeftColor: colors.primary },
  content: { flex: 1 },
  title: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold, color: colors.text },
  message: { fontSize: fontSizes.sm, color: colors.textSecondary, marginTop: 4 },
  time: { fontSize: fontSizes.xs, color: colors.textMuted, marginTop: 6 },
  unreadDot: { width: 8, height: 8, borderRadius: 4, backgroundColor: colors.primary, marginLeft: spacing.sm, marginTop: 6 },
});
