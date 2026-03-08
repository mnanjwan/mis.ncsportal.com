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
  Modal,
  Pressable,
  Dimensions,
  ScrollView,
} from 'react-native';
import { useRoute, useNavigation, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAppSelector } from '../../hooks/redux';
import { getEcho } from '../../utils/echo';
import { chatApi } from '../../api/chatApi';
import type { ChatMessageItem } from '../../api/chatApi';
import { ChatStackParamList } from '../../navigation/ChatStack';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import * as DocumentPicker from 'expo-document-picker';
import * as ImagePicker from 'expo-image-picker';

type RouteParams = { ChatRoom: { roomId: number; roomName: string } };
type Nav = NativeStackNavigationProp<ChatStackParamList, 'ChatRoom'>;

export function ChatRoomScreen() {
  const navigation = useNavigation<Nav>();
  const route = useRoute<RouteProp<RouteParams, 'ChatRoom'>>();
  const { roomId } = route.params;
  const themeColors = useThemeColor();
  const token = useAppSelector((s) => s.auth.token);
  const officerId = useAppSelector((s) => s.auth.user?.officer?.id);
  const userName = useAppSelector((s) => s.auth.user?.officer?.name ?? 'You');

  const [messages, setMessages] = useState<ChatMessageItem[]>([]);
  const [input, setInput] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [selectedMsg, setSelectedMsg] = useState<ChatMessageItem | null>(null);
  const [replyTo, setReplyTo] = useState<ChatMessageItem | null>(null);
  const [pinnedMessages, setPinnedMessages] = useState<any[]>([]);
  const flatListRef = useRef<FlatList>(null);

  // Polling interval for real-time feel (until WebSocket is available)
  const pollRef = useRef<NodeJS.Timeout | null>(null);

  const load = useCallback(async (silent = false) => {
    if (!silent) setError(null);
    try {
      const resData = await chatApi.messages(roomId);
      setPinnedMessages(resData.pinned_messages);
      setMessages(prev => {
        if (!silent) return resData.messages;
        const serverIds = new Set(resData.messages.map(m => m.id));
        const optimistic = prev.filter(m => !serverIds.has(m.id));
        return [...optimistic, ...resData.messages];
      });
    } catch {
      if (!silent) setError('Failed to load messages');
    } finally {
      setLoading(false);
    }
  }, [roomId]);

  useEffect(() => {
    navigation.setOptions({
      headerRight: () => (
        <TouchableOpacity
          style={{ marginRight: 10 }}
          onPress={() => navigation.navigate('RoomDetails', { roomId, roomName: route.params.roomName })}
        >
          <Ionicons name="information-circle-outline" size={26} color={themeColors.textOnPrimary} />
        </TouchableOpacity>
      ),
    });

    setLoading(true);
    load();
    // Mark as read immediately on mount
    chatApi.markRoomRead(roomId).catch(() => { });

    // Poll Fallback (only if Echo is not initialized)
    if (!getEcho(token || undefined)) {
      pollRef.current = setInterval(() => load(true), 4000);
    }

    return () => {
      if (pollRef.current) clearInterval(pollRef.current);
    };
  }, [load, roomId, token]);

  // Real-time via Laravel Echo
  useEffect(() => {
    if (!token || !roomId) return;

    const echo = getEcho(token);
    if (!echo) return;

    const channel = echo.join(`chat.room.${roomId}`);

    channel
      .listen('ChatMessageSent', (e: { message: ChatMessageItem }) => {
        setMessages((prev) => {
          if (prev.some((m) => m.id === e.message.id)) return prev;
          return [e.message, ...prev];
        });
      })
      .listen('ChatMessageDeleted', (e: { messageId: number }) => {
        setMessages((prev) => prev.filter((m) => m.id !== e.messageId));
      })
      .listen('ChatMessageReacted', (e: { messageId: number; reactions: any }) => {
        setMessages((prev) =>
          prev.map((m) =>
            m.id === e.messageId ? { ...m, reactions: e.reactions } : m
          )
        );
      })
      .listen('ChatMessagePinned', (e: { message: ChatMessageItem }) => {
        setMessages((prev) => prev.map((m) => (m.id === e.message.id ? e.message : m)));
        load(true); // Refresh pinning summary
      })
      .listen('ChatMessageBroadcasted', (e: { message: ChatMessageItem }) => {
        setMessages((prev) => prev.map((m) => (m.id === e.message.id ? e.message : m)));
      });

    return () => {
      echo.leave(`chat.room.${roomId}`);
    };
  }, [token, roomId, load]);

  const send = async () => {
    const text = input.trim();
    if (!text || sending) return;
    const parentId = replyTo?.id;
    setInput('');
    setReplyTo(null);
    setSending(true);
    try {
      const res = await chatApi.sendMessage(roomId, text, parentId);
      if (res.success && res.data) {
        load(true);
      }
    } catch {
      setInput(text);
      Alert.alert('Error', 'Failed to send message');
    } finally {
      setSending(false);
    }
  };

  const pickAndSendAttachment = async () => {
    Alert.alert('Attach', 'Choose a type', [
      {
        text: 'Photo / Video',
        onPress: async () => {
          const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
          if (status !== 'granted') {
            Alert.alert('Permission needed', 'Allow gallery access.');
            return;
          }
          const result = await ImagePicker.launchImageLibraryAsync({
            mediaTypes: ['images', 'videos'],
            quality: 0.7, // Compression here
          });

          if (!result.canceled && result.assets?.[0]) {
            await uploadFile(result.assets[0].uri, result.assets[0].fileName || 'image.jpg', result.assets[0].mimeType || 'image/jpeg');
          }
        }
      },
      {
        text: 'Document',
        onPress: async () => {
          const result = await DocumentPicker.getDocumentAsync({
            type: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            copyToCacheDirectory: true,
          });

          if (!result.canceled && result.assets?.[0]) {
            const file = result.assets[0];
            await uploadFile(file.uri, file.name, file.mimeType || 'application/octet-stream');
          }
        }
      },
      { text: 'Cancel', style: 'cancel' }
    ]);
  };

  const uploadFile = async (uri: string, name: string, mimeType: string) => {
    const parentId = replyTo?.id;
    setReplyTo(null);
    const formData = new FormData();
    formData.append('attachment', {
      uri,
      name,
      type: mimeType,
    } as any);
    formData.append('message_text', input.trim() || `📎 ${name}`);
    if (parentId) {
      formData.append('parent_id', parentId.toString());
    }

    setSending(true);
    setInput('');
    try {
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

  const toggleReaction = async (msg: ChatMessageItem, emoji: string) => {
    try {
      const res = await chatApi.toggleReaction(roomId, msg.id, emoji);
      if (res.success) {
        load(true);
      }
    } catch {
      Alert.alert('Error', 'Failed to update reaction');
    }
  };

  const renderPinnedMessages = () => {
    if (pinnedMessages.length === 0) return null;
    return (
      <View style={styles.pinnedContainer}>
        <ScrollView horizontal showsHorizontalScrollIndicator={false}>
          {pinnedMessages.map((m) => (
            <TouchableOpacity
              key={m.id}
              style={styles.pinnedItem}
              onPress={() => {
                // Scroll to message logic could be added here
              }}
            >
              <Ionicons name="pin" size={14} color={themeColors.primary} />
              <Text style={styles.pinnedText} numberOfLines={1}>
                {m.sender_name}: {m.message_text}
              </Text>
            </TouchableOpacity>
          ))}
        </ScrollView>
      </View>
    );
  };

  const renderReplyPreview = () => {
    if (!replyTo) return null;
    return (
      <View style={[styles.replyPreviewContainer, { backgroundColor: themeColors.surface, borderTopColor: themeColors.border }]}>
        <View style={[styles.replyPreviewBar, { backgroundColor: themeColors.primary }]} />
        <View style={{ flex: 1 }}>
          <Text style={[styles.replySender, { color: themeColors.primary }]}>{replyTo.sender?.name || 'User'}</Text>
          <Text style={[styles.replyText, { color: themeColors.textSecondary }]} numberOfLines={1}>{replyTo.message_text}</Text>
        </View>
        <TouchableOpacity onPress={() => setReplyTo(null)}>
          <Ionicons name="close-circle" size={20} color={themeColors.textMuted} />
        </TouchableOpacity>
      </View>
    );
  };

  const showOptions = (msg: ChatMessageItem) => {
    if (msg.is_deleted) return;
    setSelectedMsg(msg);
  };

  const renderReactionModal = () => {
    if (!selectedMsg) return null;

    const emojis = ['❤️', '👍', '😂', '🔥', '😮', '😢', '🙏'];
    const own = isOwn(selectedMsg);

    return (
      <Modal
        visible={!!selectedMsg}
        transparent
        animationType="fade"
        onRequestClose={() => setSelectedMsg(null)}
      >
        <Pressable
          style={styles.modalBackdrop}
          onPress={() => setSelectedMsg(null)}
        >
          <View style={styles.reactionBarContainer}>
            <View style={[styles.reactionBar, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
              {emojis.map((emoji) => (
                <TouchableOpacity
                  key={emoji}
                  onPress={() => {
                    toggleReaction(selectedMsg, emoji);
                    setSelectedMsg(null);
                  }}
                  style={styles.emojiBtn}
                >
                  <Text style={styles.emojiText}>{emoji}</Text>
                </TouchableOpacity>
              ))}
            </View>

            <View style={[styles.actionsContainer, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
              <TouchableOpacity style={styles.deleteActionBtn} onPress={() => { setReplyTo(selectedMsg); setSelectedMsg(null); }}>
                <Ionicons name="arrow-undo-outline" size={18} color={themeColors.primary} />
                <Text style={[styles.deleteActionText, { color: themeColors.text }]}>Reply</Text>
              </TouchableOpacity>
              <View style={styles.actionDivider} />

              <TouchableOpacity style={styles.deleteActionBtn} onPress={() => { setSelectedMsg(null); navigation.navigate('MessageInfo', { roomId, messageId: selectedMsg.id }); }}>
                <Ionicons name="information-circle-outline" size={18} color={themeColors.text} />
                <Text style={[styles.deleteActionText, { color: themeColors.text }]}>Message Info</Text>
              </TouchableOpacity>
              <View style={styles.actionDivider} />

              <TouchableOpacity
                style={styles.deleteActionBtn}
                onPress={async () => {
                  try {
                    const res = await chatApi.togglePin(roomId, selectedMsg.id);
                    if (res.success) {
                      setSelectedMsg(null);
                      load(true);
                    }
                  } catch {
                    Alert.alert('Error', 'Failed to pin');
                  }
                }}
              >
                <Ionicons name={selectedMsg.is_pinned ? "pin" : "pin-outline"} size={18} color={themeColors.primary} />
                <Text style={[styles.deleteActionText, { color: themeColors.text }]}>
                  {selectedMsg.is_pinned ? 'Unpin Message' : 'Pin Message'}
                </Text>
              </TouchableOpacity>
              <View style={styles.actionDivider} />

              <TouchableOpacity
                style={styles.deleteActionBtn}
                onPress={async () => {
                  try {
                    const res = await chatApi.toggleBroadcast(roomId, selectedMsg.id);
                    if (res.success) {
                      setSelectedMsg(null);
                      load(true);
                    }
                  } catch {
                    Alert.alert('Error', 'Only Staff Officers can toggle broadcast');
                  }
                }}
              >
                <Ionicons name={selectedMsg.is_broadcast ? "megaphone" : "megaphone-outline"} size={18} color="orange" />
                <Text style={[styles.deleteActionText, { color: themeColors.text }]}>
                  {selectedMsg.is_broadcast ? 'Remove Broadcast' : 'Mark as Broadcast'}
                </Text>
              </TouchableOpacity>

              {own && (
                <>
                  <View style={styles.actionDivider} />
                  <TouchableOpacity
                    style={styles.deleteActionBtn}
                    onPress={() => {
                      setSelectedMsg(null);
                      confirmDelete(selectedMsg);
                    }}
                  >
                    <Ionicons name="trash-outline" size={18} color={themeColors.danger} />
                    <Text style={[styles.deleteActionText, { color: themeColors.danger }]}>Delete Message</Text>
                  </TouchableOpacity>
                </>
              )}
            </View>
          </View>
        </Pressable>
      </Modal>
    );
  };

  const confirmDelete = (msg: ChatMessageItem) => {
    Alert.alert(
      'Delete Message',
      'Are you sure you want to delete this message?',
      [
        { text: 'Cancel', style: 'cancel' },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: async () => {
            try {
              const res = await chatApi.deleteMessage(roomId, msg.id);
              if (res.success) {
                setMessages(prev => prev.map(m =>
                  m.id === msg.id ? { ...m, is_deleted: true, message_text: 'This message was deleted' } : m
                ));
              }
            } catch (err) {
              Alert.alert('Error', 'Failed to delete message');
            }
          }
        }
      ]
    );
  };

  const isOwn = (msg: ChatMessageItem) => msg.sender_id === officerId;

  const renderReactions = (msg: ChatMessageItem) => {
    if (!msg.reactions || Object.keys(msg.reactions).length === 0) return null;
    return (
      <View style={[styles.reactionsRow, isOwn(msg) && { justifyContent: 'flex-end' }]}>
        {Object.entries(msg.reactions).map(([emoji, count]) => (
          <TouchableOpacity
            key={emoji}
            onPress={() => toggleReaction(msg, emoji)}
            style={[
              styles.reactionBadge,
              { backgroundColor: themeColors.surface, borderColor: themeColors.border },
              msg.my_reaction === emoji && { borderColor: themeColors.primary, backgroundColor: themeColors.primaryLight + '20' }
            ]}
          >
            <Text style={styles.reactionText}>{emoji} {count > 1 ? count : ''}</Text>
          </TouchableOpacity>
        ))}
      </View>
    );
  };

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
    const isDeleted = item.is_deleted;

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

            <TouchableOpacity
              onLongPress={() => !isDeleted && showOptions(item)}
              delayLongPress={500}
              activeOpacity={0.8}
              style={[
                styles.bubble,
                own
                  ? [styles.bubbleOwn, { backgroundColor: themeColors.primary }]
                  : [styles.bubbleOther, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]
              ]}
            >
              {/* Attachment indicator */}
              {!isDeleted && item.attachment_url && (
                <View style={styles.attachmentRow}>
                  <Ionicons name="document-attach" size={18} color={own ? '#ffffff' : themeColors.primary} />
                  <Text style={[styles.attachmentText, { color: own ? '#ffffff' : themeColors.primary }]}>
                    View Attachment
                  </Text>
                </View>
              )}

              {/* Reply Context */}
              {item.parent && (
                <View style={[
                  styles.replyContext,
                  {
                    backgroundColor: own ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.05)',
                    borderLeftColor: own ? '#ffffff' : themeColors.primary
                  }
                ]}>
                  <Text style={[
                    styles.replyContextSender,
                    { color: own ? '#ffffff' : themeColors.primary }
                  ]}>
                    {item.parent.sender_name}
                  </Text>
                  <Text style={[
                    styles.replyContextText,
                    { color: own ? 'rgba(255,255,255,0.8)' : themeColors.textMuted }
                  ]} numberOfLines={2}>
                    {item.parent.message_text}
                  </Text>
                </View>
              )}

              <Text style={[
                styles.bubbleText,
                { color: own ? '#ffffff' : themeColors.text },
                isDeleted && {
                  color: own ? 'rgba(255,255,255,0.7)' : themeColors.textMuted,
                  fontStyle: 'italic',
                  fontSize: 13
                }
              ]}>
                {isDeleted ? 'This message was deleted' : item.message_text}
              </Text>
            </TouchableOpacity>

            {!isDeleted && renderReactions(item)}

            <View style={styles.timeRow}>
              <Text style={[styles.time, { color: themeColors.textMuted }]}>
                {formatMsgTime(item.created_at)}
              </Text>
              {own && !isDeleted && (
                <Ionicons
                  name="checkmark-done"
                  size={14}
                  color={themeColors.primary}
                  style={{ marginLeft: 4 }}
                />
              )}
            </View>
          </View>
        )}
      </>
    );
  };

  useEffect(() => {
    const parent = navigation.getParent();
    if (parent) {
      parent.setOptions({ tabBarStyle: { display: 'none' } });
    }
    return () => {
      if (parent) {
        parent.setOptions({ tabBarStyle: undefined });
      }
    };
  }, [navigation]);

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

      {renderPinnedMessages()}
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

      {renderReplyPreview()}
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
      {renderReactionModal()}
    </KeyboardAvoidingView >
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
  timeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 3,
    marginHorizontal: spacing.sm,
  },

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

  // Reactions
  reactionsRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: -8,
    marginBottom: 4,
    marginHorizontal: spacing.sm,
    gap: 4,
  },
  reactionBadge: {
    paddingHorizontal: 6,
    paddingVertical: 2,
    borderRadius: 12,
    borderWidth: 1,
  },
  reactionText: {
    fontSize: 12,
  },

  // Pinned Messages
  pinnedContainer: {
    backgroundColor: '#ffffff',
    borderBottomWidth: 1,
    borderBottomColor: '#cccccc',
    paddingVertical: spacing.xs,
  },
  pinnedItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f0f0f0',
    paddingHorizontal: spacing.sm,
    paddingVertical: 4,
    borderRadius: 16,
    marginHorizontal: spacing.xs,
    borderWidth: 1,
    borderColor: 'rgba(0,0,0,0.1)',
    maxWidth: 250,
  },
  pinnedText: {
    fontSize: 12,
    color: '#000000',
    marginLeft: 4,
  },

  // Reply Preview
  replyPreviewContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: spacing.sm,
    backgroundColor: '#ffffff',
    borderTopWidth: 1,
    borderTopColor: '#cccccc',
    gap: 8,
  },
  replyPreviewBar: {
    width: 4,
    height: '100%',
    backgroundColor: '#000000',
    borderRadius: 2,
  },
  replySender: {
    fontSize: 12,
    fontWeight: fontWeights.bold,
    color: '#000000',
  },
  replyText: {
    fontSize: 12,
    color: '#666666',
  },

  // Reply Context in Message Bubble
  replyContext: {
    backgroundColor: 'rgba(0,0,0,0.05)',
    padding: 8,
    borderRadius: 8,
    marginBottom: 4,
    borderLeftWidth: 3,
    borderLeftColor: '#000000',
  },
  replyContextSender: {
    fontSize: 11,
    fontWeight: 'bold',
    color: '#000000',
  },
  replyContextText: {
    fontSize: 11,
    color: '#666',
  },

  // Reaction Modal
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.4)',
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
  },
  reactionBarContainer: {
    alignItems: 'center',
    gap: spacing.sm,
  },
  reactionBar: {
    flexDirection: 'row',
    paddingHorizontal: spacing.sm,
    paddingVertical: spacing.xs,
    borderRadius: 30,
    borderWidth: 1,
    elevation: 5,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.25,
    shadowRadius: 3.84,
  },
  emojiBtn: {
    padding: spacing.sm,
  },
  emojiText: {
    fontSize: 24,
  },
  deleteActionBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.lg,
    paddingVertical: 10,
    gap: spacing.md,
  },
  deleteActionText: {
    fontSize: 14,
    fontWeight: fontWeights.medium,
  },
  actionDivider: {
    height: 1,
    backgroundColor: 'rgba(0,0,0,0.05)',
    marginHorizontal: spacing.lg,
  },
  actionsContainer: {
    borderRadius: 14,
    borderWidth: 1,
    overflow: 'hidden',
    width: '100%',
    marginTop: spacing.md,
  },

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
