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
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Nav = NativeStackNavigationProp<ChatStackParamList, 'ChatRooms'>;

// Helpers
const roomIcon = (type: string) => {
  if (type === 'command') return 'business';
  if (type === 'management') return 'people';
  return 'chatbubbles';
};
const roomIconColor = (type: string) => {
  if (type === 'command') return '#10b981';
  if (type === 'management') return '#6366f1';
  return '#f59e0b';
};
const roomIconBg = (type: string) => {
  if (type === 'command') return '#ecfdf5';
  if (type === 'management') return '#eef2ff';
  return '#fffbeb';
};

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
      if (res.success && res.data) {
        setRooms(res.data);
      }
    } catch {
      setError('Failed to load chat rooms');
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

  const officialRooms = rooms.filter(r => r.room_type === 'command' || r.room_type === 'management');
  const groupRooms = rooms.filter(r => r.room_type === 'group');

  const onRoom = (room: ChatRoomItem) => {
    navigation.navigate('ChatRoom', { roomId: room.id, roomName: room.name });
  };

  const lastPreview = (room: ChatRoomItem) => {
    const last = room.last_message;
    if (!last) return 'No messages yet';
    const prefix = last.sender?.name ? `${last.sender.name.split(' ')[0]}: ` : '';
    const text = last.attachment_url ? '📎 Attachment' : (last.message_text ?? '');
    const full = prefix + text;
    return full.length > 45 ? full.slice(0, 45) + '…' : full;
  };

  const renderRoom = (item: ChatRoomItem) => {
    const unread = item.unread_count ?? 0;
    return (
      <TouchableOpacity
        style={[styles.roomRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
        onPress={() => onRoom(item)}
        activeOpacity={0.7}
      >
        {/* Avatar */}
        <View style={[styles.roomAvatar, { backgroundColor: roomIconBg(item.room_type) }]}>
          <Ionicons name={roomIcon(item.room_type) as any} size={24} color={roomIconColor(item.room_type)} />
        </View>

        {/* Content */}
        <View style={styles.roomContent}>
          <View style={styles.roomTopRow}>
            <Text style={[styles.roomName, { color: themeColors.text }]} numberOfLines={1}>
              {item.name}
            </Text>
            <Text style={[styles.roomTime, { color: themeColors.textMuted }]}>
              {formatTime(item.last_message?.created_at)}
            </Text>
          </View>
          <View style={styles.roomBottomRow}>
            <Text style={[styles.roomPreview, { color: themeColors.textSecondary }]} numberOfLines={1}>
              {lastPreview(item)}
            </Text>
            {unread > 0 && (
              <View style={styles.unreadBadge}>
                <Text style={styles.unreadText}>{unread > 99 ? '99+' : unread}</Text>
              </View>
            )}
          </View>
        </View>
      </TouchableOpacity>
    );
  };

  const renderSectionHeader = (title: string) => (
    <Text style={[styles.sectionHeader, { color: themeColors.textMuted }]}>{title}</Text>
  );

  if (loading && rooms.length === 0) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
      <View style={[styles.container, { backgroundColor: themeColors.background }]}>
        {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

        <FlatList
          data={[]}
          keyExtractor={() => ''}
          renderItem={null}
          ListHeaderComponent={
            <View>
              {/* Official Rooms Section */}
              {officialRooms.length > 0 && (
                <>
                  {renderSectionHeader('OFFICIAL ROOMS')}
                  {officialRooms.map(room => (
                    <React.Fragment key={`official-${room.id}`}>
                      {renderRoom(room)}
                    </React.Fragment>
                  ))}
                </>
              )}

              {/* Group Rooms Section */}
              {renderSectionHeader('MY GROUPS')}
              {groupRooms.length > 0 ? (
                groupRooms.map(room => (
                  <React.Fragment key={`group-${room.id}`}>
                    {renderRoom(room)}
                  </React.Fragment>
                ))
              ) : (
                <View style={[styles.emptyGroup, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                  <Ionicons name="chatbubbles-outline" size={32} color={themeColors.textMuted} />
                  <Text style={[styles.emptyGroupText, { color: themeColors.textMuted }]}>No groups yet</Text>
                  <Text style={[styles.emptyGroupSub, { color: themeColors.textMuted }]}>Create a group to start chatting with your team</Text>
                </View>
              )}
            </View>
          }
          contentContainerStyle={styles.list}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => { setRefreshing(true); load(); }}
              colors={[themeColors.primary]}
              tintColor={themeColors.primary}
            />
          }
        />

        {/* FAB - New Group */}
        <TouchableOpacity
          style={[styles.fab, { backgroundColor: themeColors.primary }]}
          onPress={() => navigation.navigate('CreateGroup')}
          activeOpacity={0.8}
        >
          <Ionicons name="add" size={28} color="#fff" />
        </TouchableOpacity>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  container: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  error: { fontSize: 13, padding: spacing.base, textAlign: 'center' },
  list: {
    paddingHorizontal: spacing.xl,
    paddingTop: spacing.md,
    paddingBottom: 100,
  },
  sectionHeader: {
    fontSize: 12,
    fontWeight: fontWeights.bold,
    letterSpacing: 1.2,
    textTransform: 'uppercase',
    marginBottom: spacing.md,
    marginTop: spacing.xl,
    paddingHorizontal: spacing.xs,
  },
  roomRow: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.sm,
    borderWidth: 1,
  },
  roomAvatar: {
    width: 52,
    height: 52,
    borderRadius: 26,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  roomContent: {
    flex: 1,
  },
  roomTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 4,
  },
  roomName: {
    fontSize: 15,
    fontWeight: fontWeights.semibold,
    flex: 1,
    marginRight: spacing.sm,
  },
  roomTime: {
    fontSize: 12,
  },
  roomBottomRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  roomPreview: {
    fontSize: 13,
    flex: 1,
    marginRight: spacing.sm,
  },
  unreadBadge: {
    backgroundColor: '#ef4444',
    borderRadius: 12,
    minWidth: 22,
    height: 22,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 6,
  },
  unreadText: {
    color: '#fff',
    fontSize: 11,
    fontWeight: 'bold',
  },
  emptyGroup: {
    borderRadius: 16,
    borderWidth: 1,
    padding: spacing.xl,
    alignItems: 'center',
    gap: spacing.sm,
  },
  emptyGroupText: {
    fontSize: 15,
    fontWeight: fontWeights.semibold,
  },
  emptyGroupSub: {
    fontSize: 13,
    textAlign: 'center',
  },
  fab: {
    position: 'absolute',
    bottom: 30,
    right: 24,
    width: 60,
    height: 60,
    borderRadius: 30,
    justifyContent: 'center',
    alignItems: 'center',
    elevation: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
  },
});
