import React, { useCallback, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  SafeAreaView,
  Platform,
  StatusBar,
  useColorScheme,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { notificationApi } from '../../api/notificationApi';
import type { NotificationItem } from '../../api/notificationApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

// --- Icon mapping per entity type ---
const typeConfig: Record<string, { icon: string; bg: string; color: string }> = {
  leave_application: { icon: 'calendar', bg: '#fef2f2', color: '#f97316' },
  pass_application: { icon: 'card', bg: '#eff6ff', color: '#10b981' },
  emolument: { icon: 'cash', bg: '#ecfdf5', color: '#3b82f6' },
  chat_message: { icon: 'chatbubbles', bg: '#f0f9ff', color: '#0ea5e9' },
  fleet_request: { icon: 'car', bg: '#fef3c7', color: '#d97706' },
  fleet_vehicle: { icon: 'car-sport', bg: '#fef3c7', color: '#d97706' },
  pharmacy_requisition: { icon: 'medkit', bg: '#fdf4ff', color: '#a855f7' },
  pharmacy_stock: { icon: 'medkit', bg: '#fdf4ff', color: '#a855f7' },
  officer: { icon: 'person', bg: '#f0fdf4', color: '#16a34a' },
  manning_request: { icon: 'briefcase', bg: '#fff7ed', color: '#ea580c' },
  quarter_allocation: { icon: 'home', bg: '#f0f9ff', color: '#2563eb' },
  posting: { icon: 'navigate', bg: '#fefce8', color: '#ca8a04' },
  query: { icon: 'help-circle', bg: '#fff1f2', color: '#e11d48' },
  course: { icon: 'school', bg: '#f0fdf4', color: '#15803d' },
  duty_roster: { icon: 'today', bg: '#eff6ff', color: '#1d4ed8' },
  internal_staff_order: { icon: 'document-text', bg: '#fafafa', color: '#374151' },
  user: { icon: 'settings', bg: '#f8fafc', color: '#64748b' },
};

const defaultConfig = { icon: 'notifications', bg: '#f0fdf4', color: '#088a56' };

function getConfig(type: string | null) {
  if (!type) return defaultConfig;
  // Try exact match
  if (typeConfig[type]) return typeConfig[type];
  // Fuzzy prefix match (e.g. "leave_application_approved" → "leave_application")
  const key = Object.keys(typeConfig).find((k) => type.startsWith(k) || type.includes(k.replace('_', '')));
  return key ? typeConfig[key] : defaultConfig;
}

function formatSmartTime(dateStr: string): string {
  const d = new Date(dateStr);
  const now = new Date();
  const diff = (now.getTime() - d.getTime()) / 1000;
  if (diff < 60) return 'Just now';
  if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
  if (diff < 604800) return `${Math.floor(diff / 86400)}d ago`;
  return d.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' });
}

export function NotificationListScreen() {
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const [items, setItems] = useState<NotificationItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [expandedId, setExpandedId] = useState<number | null>(null);

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

  useFocusEffect(
    useCallback(() => {
      setLoading(true);
      load();
    }, [load])
  );

  const handlePress = async (item: NotificationItem) => {
    setExpandedId((prev) => (prev === item.id ? null : item.id));
    if (!item.is_read) {
      try {
        await notificationApi.markAsRead(item.id);
        setItems((prev) => prev.map((n) => (n.id === item.id ? { ...n, is_read: true } : n)));
      } catch { }
    }
  };

  const markAllRead = async () => {
    try {
      await notificationApi.markAllAsRead();
      setItems((prev) => prev.map((n) => ({ ...n, is_read: true })));
    } catch { }
  };

  const unreadCount = items.filter((n) => !n.is_read).length;

  const renderItem = ({ item }: { item: NotificationItem }) => {
    const cfg = getConfig(item.entity_type || item.notification_type);

    return (
      <TouchableOpacity
        style={[
          styles.row,
          { backgroundColor: themeColors.surface, borderColor: item.is_read ? themeColors.border : themeColors.primary },
          !item.is_read && styles.rowUnread,
        ]}
        onPress={() => handlePress(item)}
        activeOpacity={0.7}
      >
        {/* Icon */}
        <View style={[styles.iconBox, { backgroundColor: cfg.bg }]}>
          <Ionicons name={cfg.icon as any} size={22} color={cfg.color} />
        </View>

        {/* Content */}
        <View style={styles.rowContent}>
          <View style={styles.rowTopRow}>
            <Text style={[styles.rowTitle, { color: themeColors.text }]} numberOfLines={1}>
              {item.title ?? 'Notification'}
            </Text>
            <Text style={[styles.rowTime, { color: themeColors.textMuted }]}>
              {formatSmartTime(item.created_at)}
            </Text>
          </View>
          {item.message ? (
            <Text style={[styles.rowMessage, { color: themeColors.textSecondary }]} numberOfLines={expandedId === item.id ? undefined : 2}>
              {item.message}
            </Text>
          ) : null}
        </View>

        {/* Unread dot */}
        {!item.is_read && <View style={[styles.unreadDot, { backgroundColor: themeColors.primary }]} />}
      </TouchableOpacity>
    );
  };

  if (loading && items.length === 0) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

      {/* "Mark all read" banner */}
      {unreadCount > 0 && (
        <TouchableOpacity
          style={[styles.markAllBar, { backgroundColor: themeColors.primaryMuted, borderBottomColor: themeColors.border }]}
          onPress={markAllRead}
        >
          <Ionicons name="checkmark-done" size={16} color={themeColors.primary} />
          <Text style={[styles.markAllText, { color: themeColors.primary }]}>
            Mark all {unreadCount} as read
          </Text>
        </TouchableOpacity>
      )}

      {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

      <FlatList
        data={items}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderItem}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => { setRefreshing(true); load(); }}
            colors={[themeColors.primary]}
            tintColor={themeColors.primary}
          />
        }
        ListEmptyComponent={
          <View style={styles.emptyWrap}>
            <Ionicons name="notifications-off-outline" size={56} color={themeColors.textMuted} />
            <Text style={[styles.emptyTitle, { color: themeColors.textMuted }]}>All caught up</Text>
            <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>No notifications yet</Text>
          </View>
        }
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  markAllBar: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 10,
    borderBottomWidth: 1,
    gap: 6,
  },
  markAllText: { fontSize: 13, fontWeight: fontWeights.semibold },
  error: { fontSize: 13, padding: spacing.base, textAlign: 'center' },
  list: { paddingHorizontal: spacing.xl, paddingTop: spacing.md, paddingBottom: 40 },

  row: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderWidth: 1,
  },
  rowUnread: {
    borderLeftWidth: 3,
  },
  iconBox: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  rowContent: { flex: 1 },
  rowTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 4,
  },
  rowTitle: { fontSize: 14, fontWeight: fontWeights.semibold, flex: 1, marginRight: 8 },
  rowTime: { fontSize: 11, flexShrink: 0 },
  rowMessage: { fontSize: 13, lineHeight: 18 },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    marginLeft: spacing.sm,
    marginTop: 8,
    flexShrink: 0,
  },

  emptyWrap: { alignItems: 'center', paddingTop: 80, gap: spacing.sm },
  emptyTitle: { fontSize: 17, fontWeight: fontWeights.semibold },
  emptySub: { fontSize: 14 },
});
