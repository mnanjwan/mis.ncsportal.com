import React, { useCallback, useEffect, useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  FlatList,
  TextInput,
  TouchableOpacity,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Alert,
  useColorScheme,
} from 'react-native';
import { useRoute, RouteProp } from '@react-navigation/native';
import { useAppSelector } from '../../hooks/redux';
import { chatApi } from '../../api/chatApi';
import type { ChatMessageItem } from '../../api/chatApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';

type RouteParams = { ChatRoom: { roomId: number; roomName: string } };

export function ChatRoomScreen() {
  const route = useRoute<RouteProp<RouteParams, 'ChatRoom'>>();
  const { roomId } = route.params;
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const officerId = useAppSelector((s) => s.auth.user?.officer?.id);
  const userName = useAppSelector((s) => s.auth.user?.officer?.name ?? 'You');

  const [messages, setMessages] = useState<ChatMessageItem[]>([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const flatListRef = useRef<FlatList>(null);

  // Polling interval for real-time feel (until WebSocket is available)
  const pollRef = useRef<NodeJS.Timeout | null>(null);

  const load = useCallback(async (silent = false) => {
    if (!silent) setError(null);
    try {
      const res = await chatApi.messages(roomId);
      if (res.success && res.data) {
        setMessages(res.data);
      }
    } catch {
      if (!silent) setError('Failed to load messages');
    } finally {
      setLoading(false);
    }
  }, [roomId]);

  useEffect(() => {
    setLoading(true);
    load();
    // Poll every 5 seconds for new messages
    pollRef.current = setInterval(() => load(true), 5000);
    return () => {
      if (pollRef.current) clearInterval(pollRef.current);
    };
  }, [load]);

  const send = async () => {
    const text = input.trim();
    if (!text || sending) return;
    setInput('');
    setSending(true);
    try {
      const res = await chatApi.sendMessage(roomId, text);
      if (res.success && res.data) {
        const newMsg: ChatMessageItem = {
          id: res.data.id,
          chat_room_id: roomId,
          sender_id: officerId ?? 0,
          message_text: res.data.message,
          created_at: res.data.created_at,
          sender: { id: officerId ?? 0, name: userName },
        };
        setMessages((prev) => [newMsg, ...prev]);
      }
    } catch {
      setInput(text);
      Alert.alert('Error', 'Failed to send message');
    } finally {
      setSending(false);
    }
  };

  const pickAndSendAttachment = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        copyToCacheDirectory: true,
      });

      if (result.canceled || !result.assets?.length) return;

      const file = result.assets[0];
      const formData = new FormData();
      formData.append('attachment', {
        uri: file.uri,
        name: file.name,
        type: file.mimeType || 'application/octet-stream',
      } as any);
      formData.append('message_text', input.trim() || `📎 ${file.name}`);

      setSending(true);
      setInput('');
      const res = await chatApi.sendAttachment(roomId, formData);
      if (res.success) {
        load(true);
      }
    } catch {
      Alert.alert('Error', 'Failed to send attachment');
    } finally {
      setSending(false);
    }
  };

  const isOwn = (msg: ChatMessageItem) => msg.sender_id === officerId;

  const senderDisplay = (msg: ChatMessageItem) => {
    const s = msg.sender;
    if (!s) return 'Unknown';
    const rank = s.rank ? `${s.rank} ` : '';
    const name = s.full_name || s.name || s.service_number || 'Unknown';
    return `${rank}${name}`;
  };

  const formatMsgTime = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  // Group messages by date
  const getDateLabel = (dateStr: string) => {
    const d = new Date(dateStr);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) return 'Today';
    const yesterday = new Date(now);
    yesterday.setDate(now.getDate() - 1);
    if (d.toDateString() === yesterday.toDateString()) return 'Yesterday';
    return d.toLocaleDateString([], { weekday: 'long', day: 'numeric', month: 'short' });
  };

  const renderItem = ({ item, index }: { item: ChatMessageItem; index: number }) => {
    const own = isOwn(item);
    const isBroadcast = item.is_broadcast;

    // Show date separator
    const currentDate = getDateLabel(item.created_at);
    const prevItem = messages[index + 1]; // inverted list
    const prevDate = prevItem ? getDateLabel(prevItem.created_at) : null;
    const showDateSep = currentDate !== prevDate;

    return (
      <>
        {showDateSep && (
          <View style={styles.dateSep}>
            <View style={[styles.dateSepLine, { backgroundColor: themeColors.border }]} />
            <Text style={[styles.dateSepText, { color: themeColors.textMuted, backgroundColor: themeColors.background }]}>
              {currentDate}
            </Text>
            <View style={[styles.dateSepLine, { backgroundColor: themeColors.border }]} />
          </View>
        )}

        {isBroadcast ? (
          <View style={[styles.broadcastWrap, { backgroundColor: '#fffbeb', borderColor: '#fcd34d' }]}>
            <Ionicons name="megaphone" size={16} color="#f59e0b" />
            <View style={{ flex: 1, marginLeft: spacing.sm }}>
              <Text style={{ fontSize: 11, color: '#92400e', fontWeight: fontWeights.semibold, marginBottom: 2 }}>
                {senderDisplay(item)} · Broadcast
              </Text>
              <Text style={{ fontSize: 14, color: '#78350f' }}>{item.message_text}</Text>
            </View>
          </View>
        ) : (
          <View style={[styles.bubbleWrap, own && styles.bubbleWrapOwn]}>
            {/* Sender name (for others) */}
            {!own && (
              <Text style={[styles.senderName, { color: themeColors.primary }]}>
                {senderDisplay(item)}
              </Text>
            )}

            <View style={[
              styles.bubble,
              own
                ? [styles.bubbleOwn, { backgroundColor: themeColors.primary }]
                : [styles.bubbleOther, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]
            ]}>
              {/* Attachment indicator */}
              {item.attachment_url && (
                <TouchableOpacity style={styles.attachmentRow}>
                  <Ionicons name="document-attach" size={18} color={own ? '#ffffff' : themeColors.primary} />
                  <Text style={[styles.attachmentText, { color: own ? '#ffffff' : themeColors.primary }]}>
                    View Attachment
                  </Text>
                </TouchableOpacity>
              )}

              <Text style={[
                styles.bubbleText,
                { color: own ? '#ffffff' : themeColors.text }
              ]}>
                {item.message_text}
              </Text>
            </View>

            <Text style={[styles.time, { color: themeColors.textMuted }]}>
              {formatMsgTime(item.created_at)}
            </Text>
          </View>
        )}
      </>
    );
  };

  if (loading && messages.length === 0) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  return (
    <KeyboardAvoidingView
      style={[styles.container, { backgroundColor: themeColors.background }]}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 90 : 0}
    >
      {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

      <FlatList
        ref={flatListRef}
        data={messages}
        keyExtractor={(item) => String(item.id)}
        renderItem={renderItem}
        inverted
        contentContainerStyle={styles.list}
        ListEmptyComponent={
          <View style={styles.emptyWrap}>
            <Ionicons name="chatbubble-ellipses-outline" size={48} color={themeColors.textMuted} />
            <Text style={[styles.emptyText, { color: themeColors.textMuted }]}>No messages yet</Text>
            <Text style={[styles.emptySub, { color: themeColors.textMuted }]}>Be the first to say hello!</Text>
          </View>
        }
      />

      {/* Input Bar */}
      <View style={[styles.inputRow, { backgroundColor: themeColors.surface, borderTopColor: themeColors.border }]}>
        <TouchableOpacity
          style={styles.attachBtn}
          onPress={pickAndSendAttachment}
          disabled={sending}
        >
          <Ionicons name="attach" size={24} color={themeColors.textMuted} />
        </TouchableOpacity>

        <TextInput
          style={[styles.input, { backgroundColor: themeColors.background, color: themeColors.text, borderColor: themeColors.border }]}
          value={input}
          onChangeText={setInput}
          placeholder="Type a message..."
          placeholderTextColor={themeColors.textMuted}
          multiline
          maxLength={5000}
          editable={!sending}
        />

        <TouchableOpacity
          style={[styles.sendBtn, { backgroundColor: themeColors.primary }, (!input.trim() || sending) && styles.sendBtnDisabled]}
          onPress={send}
          disabled={!input.trim() || sending}
        >
          {sending ? (
            <ActivityIndicator size="small" color="#ffffff" />
          ) : (
            <Ionicons name="send" size={20} color="#ffffff" />
          )}
        </TouchableOpacity>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  error: { fontSize: 13, padding: spacing.sm, textAlign: 'center' },
  list: { paddingHorizontal: spacing.lg, paddingBottom: spacing.sm, paddingTop: spacing.md },
  emptyWrap: { alignItems: 'center', paddingTop: 60, gap: spacing.sm },
  emptyText: { fontSize: 16, fontWeight: fontWeights.semibold },
  emptySub: { fontSize: 13 },

  // Date separator
  dateSep: { flexDirection: 'row', alignItems: 'center', marginVertical: spacing.lg },
  dateSepLine: { flex: 1, height: 1 },
  dateSepText: { paddingHorizontal: spacing.md, fontSize: 12, fontWeight: fontWeights.semibold },

  // Messages
  bubbleWrap: { marginBottom: spacing.md, alignItems: 'flex-start', maxWidth: '85%' },
  bubbleWrapOwn: { alignSelf: 'flex-end', alignItems: 'flex-end' },
  senderName: { fontSize: 12, fontWeight: fontWeights.semibold, marginBottom: 3, marginLeft: spacing.sm },
  bubble: { paddingHorizontal: spacing.md, paddingVertical: 10, borderRadius: 18 },
  bubbleOwn: { borderBottomRightRadius: 4 },
  bubbleOther: { borderBottomLeftRadius: 4, borderWidth: 1 },
  bubbleText: { fontSize: 15, lineHeight: 21 },
  time: { fontSize: 11, marginTop: 3, marginHorizontal: spacing.sm },

  // Broadcast
  broadcastWrap: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    borderRadius: 12,
    borderWidth: 1,
    padding: spacing.md,
    marginBottom: spacing.md,
  },

  // Attachment
  attachmentRow: { flexDirection: 'row', alignItems: 'center', marginBottom: spacing.xs, gap: 6 },
  attachmentText: { fontSize: 13, fontWeight: fontWeights.semibold },

  // Input bar
  inputRow: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    paddingHorizontal: spacing.md,
    paddingVertical: spacing.sm,
    borderTopWidth: 1,
    gap: spacing.sm,
  },
  attachBtn: {
    paddingVertical: 10,
    paddingHorizontal: 4,
  },
  input: {
    flex: 1,
    minHeight: 42,
    maxHeight: 100,
    borderRadius: 21,
    paddingHorizontal: spacing.lg,
    paddingVertical: Platform.OS === 'ios' ? 12 : 8,
    fontSize: 15,
    borderWidth: 1,
  },
  sendBtn: {
    width: 42,
    height: 42,
    borderRadius: 21,
    justifyContent: 'center',
    alignItems: 'center',
  },
  sendBtnDisabled: { opacity: 0.5 },
});
