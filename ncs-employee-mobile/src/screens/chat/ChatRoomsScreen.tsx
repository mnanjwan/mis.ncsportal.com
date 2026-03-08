import React, { useCallback, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TouchableOpacity,
  ActivityIndicator,
  RefreshControl,
  Platform,
  SafeAreaView,
  StatusBar,
  useColorScheme,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { chatApi } from '../../api/chatApi';
import type { ChatRoomItem } from '../../api/chatApi';
import { ChatStackParamList } from '../../navigation/ChatStack';
import { useThemeColor, spacing, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Nav = NativeStackNavigationProp<ChatStackParamList, 'ChatRooms'>;

function formatTime(dateStr?: string) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const now = new Date();
  const isToday = d.toDateString() === now.toDateString();
  if (isToday) return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  const yesterday = new Date(now);
  yesterday.setDate(now.getDate() - 1);
  if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
  return d.toLocaleDateString([], { day: 'numeric', month: 'short' });
}

/** True if room is a 1-on-1 DM */
const isDM = (room: ChatRoomItem) =>
  (room.room_type === 'group' || room.room_type === 'UNIT') &&
  (room.member_count == null || room.member_count <= 2);

/** Get initials from name */
const getInitials = (name: string) => {
  const parts = name.trim().split(/\s+/);
  if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
  return name.slice(0, 2).toUpperCase();
};

const DM_BG = ['#fef3c7', '#fce7f3', '#ede9fe', '#dbeafe', '#d1fae5', '#fee2e2'];
const DM_FG = ['#b45309', '#be185d', '#7c3aed', '#1d4ed8', '#065f46', '#991b1b'];
const dmIdx = (name: string) => {
  let h = 0;
  for (let i = 0; i < name.length; i++) h = name.charCodeAt(i) + ((h << 5) - h);
  return Math.abs(h) % DM_BG.length;
};

const officialBg: Record<string, string> = {
  command: '#ecfdf5', COMMAND: '#ecfdf5',
  management: '#eef2ff', MANAGEMENT: '#eef2ff',
};
const officialFg: Record<string, string> = {
  command: '#10b981', COMMAND: '#10b981',
  management: '#6366f1', MANAGEMENT: '#6366f1',
};
const officialIconName = (type: string): any =>
  type === 'command' || type === 'COMMAND' ? 'business' : 'shield';

// ─── Total unread count across all rooms (used by parent for tab badge) ───────
export function useTotalChatUnread(rooms: ChatRoomItem[]) {
  return rooms.reduce((sum, r) => sum + (r.unread_count ?? 0), 0);
}

export function ChatRoomsScreen() {
  const navigation = useNavigation<Nav>();
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const [rooms, setRooms] = useState<ChatRoomItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setError(null);
    try {
      const res = await chatApi.rooms();
      if (res.success && res.data) setRooms(res.data);
    } catch {
      setError('Failed to load chats');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => {
    setLoading(true);
    // Sync auto-joined rooms (Command, UNIT, Management) before loading the list
    chatApi.syncRooms().finally(() => load());
  }, [load]));

  const onRoom = (room: ChatRoomItem) =>
    navigation.navigate('ChatRoom', { roomId: room.id, roomName: room.name });

  // Sort by most recent message (WhatsApp style)
  const sortedRooms = [...rooms].sort((a, b) => {
    const ta = a.last_message?.created_at ? new Date(a.last_message.created_at).getTime() : 0;
    const tb = b.last_message?.created_at ? new Date(b.last_message.created_at).getTime() : 0;
    return tb - ta;
  });

  const totalUnread = rooms.reduce((s, r) => s + (r.unread_count ?? 0), 0);

  const lastPreview = (room: ChatRoomItem) => {
    const last = room.last_message;
    if (!last) return 'Tap to start chatting';
    const prefix = last.sender?.name ? `${last.sender.name.split(' ')[0]}: ` : '';
    const text = last.attachment_url ? '📎 Attachment' : (last.message_text ?? '');
    const full = prefix + text;
    return full.length > 55 ? full.slice(0, 55) + '…' : full;
  };

  const renderAvatar = (room: ChatRoomItem) => {
    const isOfficial = officialBg[room.room_type] != null;
    if (isOfficial) {
      return (
        <View style={[styles.avatar, { backgroundColor: officialBg[room.room_type] }]}>
          <Ionicons name={officialIconName(room.room_type)} size={24} color={officialFg[room.room_type]} />
        </View>
      );
    }
    if (isDM(room)) {
      const i = dmIdx(room.name);
      return (
        <View style={[styles.avatar, { backgroundColor: DM_BG[i] }]}>
          <Text style={[styles.avatarInitials, { color: DM_FG[i] }]}>{getInitials(room.name)}</Text>
        </View>
      );
    }
    return (
      <View style={[styles.avatar, { backgroundColor: '#e0e7ff' }]}>
        <Ionicons name="people" size={24} color="#4f46e5" />
      </View>
    );
  };

  const renderRoom = ({ item }: { item: ChatRoomItem }) => {
    const unread = item.unread_count ?? 0;
    const hasUnread = unread > 0;

    return (
      <TouchableOpacity
        style={[styles.roomRow, { borderBottomColor: themeColors.border }]}
        onPress={() => onRoom(item)}
        activeOpacity={0.6}
      >
        {/* Avatar */}
        <View style={styles.avatarWrap}>
          {renderAvatar(item)}
        </View>

        {/* Content */}
        <View style={styles.roomContent}>
          {/* Top row: name + timestamp */}
          <View style={styles.roomTopRow}>
            <Text
              style={[styles.roomName, { color: themeColors.text }, hasUnread && styles.roomNameUnread]}
              numberOfLines={1}
              suppressHighlighting
            >
              {item.name}
            </Text>
            <Text style={[styles.roomTime, { color: hasUnread ? '#25d366' : themeColors.textMuted }]}>
              {formatTime(item.last_message?.created_at)}
            </Text>
          </View>

          {/* Bottom row: preview + badge */}
          <View style={styles.roomBottomRow}>
            <Text
              style={[
                styles.roomPreview,
                { color: hasUnread ? themeColors.text : themeColors.textSecondary },
                hasUnread && styles.roomPreviewUnread,
              ]}
              numberOfLines={1}
            >
              {lastPreview(item)}
            </Text>
            {hasUnread && (
              <View style={styles.unreadBadge}>
                <Text style={styles.unreadText}>{unread > 99 ? '99+' : unread}</Text>
              </View>
            )}
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  if (loading && rooms.length === 0) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color="#25d366" />
      </View>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

      {/* WhatsApp-style sticky header with total unread */}
      {totalUnread > 0 && (
        <View style={[styles.unreadBanner, { backgroundColor: '#25d36618', borderColor: '#25d366' }]}>
          <Ionicons name="chatbubble-ellipses" size={14} color="#25d366" />
          <Text style={styles.unreadBannerText}>
            {totalUnread} unread message{totalUnread !== 1 ? 's' : ''}
          </Text>
        </View>
      )}

      {error ? <Text style={[styles.error, { color: '#ef4444' }]}>{error}</Text> : null}

      <FlatList
        data={sortedRooms}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderRoom}
        contentContainerStyle={styles.list}
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={() => { setRefreshing(true); load(); }}
            colors={['#25d366']}
            tintColor="#25d366"
          />
        }
        ListEmptyComponent={
          <View style={[styles.emptyWrap, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
            <Ionicons name="chatbubbles-outline" size={56} color={themeColors.textMuted} />
            <Text style={[styles.emptyTitle, { color: themeColors.text }]}>No chats yet</Text>
            <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>
              Tap the green button to start chatting
            </Text>
          </View>
        }
      />

      {/* FABs — WhatsApp style */}
      <View style={styles.fabContainer}>
        <TouchableOpacity
          style={[styles.fabSecondary, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
          onPress={() => navigation.navigate('CreateGroup')}
          activeOpacity={0.8}
        >
          <Ionicons name="people" size={22} color={themeColors.textSecondary} />
        </TouchableOpacity>

        <TouchableOpacity
          style={styles.fab}
          onPress={() => navigation.navigate('CreateDM')}
          activeOpacity={0.8}
        >
          <Ionicons name="chatbubble-ellipses" size={26} color="#ffffff" />
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  error: { fontSize: 13, padding: 12, textAlign: 'center' },
  list: { paddingBottom: 120 },

  unreadBanner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderBottomWidth: 1,
  },
  unreadBannerText: { fontSize: 13, color: '#25d366', fontWeight: fontWeights.semibold },

  // ─── Each room row (WhatsApp style: full-width, bottom border) ───
  roomRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    borderBottomWidth: StyleSheet.hairlineWidth,
  },
  avatarWrap: { marginRight: 14 },
  avatar: {
    width: 52,
    height: 52,
    borderRadius: 26,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarInitials: { fontSize: 20, fontWeight: fontWeights.bold },
  roomContent: { flex: 1 },
  roomTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 3,
  },
  roomName: { fontSize: 16, fontWeight: '400', flex: 1, marginRight: 8 },
  roomNameUnread: { fontWeight: fontWeights.bold },
  roomTime: { fontSize: 12 },
  roomBottomRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  roomPreview: { fontSize: 14, flex: 1, marginRight: 8 },
  roomPreviewUnread: { fontWeight: fontWeights.semibold },

  unreadBadge: {
    backgroundColor: '#25d366',
    borderRadius: 12,
    minWidth: 22,
    height: 22,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
  },
  unreadText: { color: '#fff', fontSize: 12, fontWeight: 'bold' },

  emptyWrap: {
    borderRadius: 16,
    borderWidth: 1,
    margin: 24,
    padding: 32,
    alignItems: 'center',
    gap: 10,
  },
  emptyTitle: { fontSize: 18, fontWeight: fontWeights.bold },
  emptySub: { fontSize: 14, textAlign: 'center' },

  fabContainer: {
    position: 'absolute',
    bottom: Platform.OS === 'ios' ? 40 : 24,
    right: 20,
    alignItems: 'center',
    gap: 12,
  },
  fabSecondary: {
    width: 46,
    height: 46,
    borderRadius: 23,
    borderWidth: 1,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 3,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.12,
    shadowRadius: 4,
  },
  fab: {
    width: 58,
    height: 58,
    borderRadius: 29,
    backgroundColor: '#25d366',
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 6,
    shadowColor: '#25d366',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.35,
    shadowRadius: 8,
  },
});
