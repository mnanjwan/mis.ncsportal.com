import React, { useEffect, useState } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    ActivityIndicator,
    Image,
} from 'react-native';
import { useRoute, RouteProp } from '@react-navigation/native';
import { chatApi } from '../../api/chatApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type RouteParams = { MessageInfo: { roomId: number; messageId: number } };

export function MessageInfoScreen() {
    const route = useRoute<RouteProp<RouteParams, 'MessageInfo'>>();
    const { roomId, messageId } = route.params;
    const themeColors = useThemeColor();

    const [loading, setLoading] = useState(true);
    const [data, setData] = useState<{ read_by: any[]; total_readers: number } | null>(null);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        load();
    }, []);

    const load = async () => {
        setLoading(true);
        try {
            const res = await chatApi.getMessageInfo(roomId, messageId);
            if (res.success) {
                setData(res.data);
            }
        } catch (err: any) {
            setError(err.message || 'Failed to load info');
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
                <ActivityIndicator size="large" color={themeColors.primary} />
            </View>
        );
    }

    if (error) {
        return (
            <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
                <Text style={{ color: themeColors.danger }}>{error}</Text>
            </View>
        );
    }

    return (
        <View style={[styles.container, { backgroundColor: themeColors.background }]}>
            <View style={[styles.header, { backgroundColor: themeColors.surface }]}>
                <Ionicons name="eye-outline" size={32} color={themeColors.primary} />
                <Text style={[styles.totalText, { color: themeColors.text }]}>
                    Read by {data?.total_readers || 0} people
                </Text>
            </View>

            <FlatList
                data={data?.read_by || []}
                keyExtractor={(item) => String(item.id)}
                renderItem={({ item }) => (
                    <View style={[styles.memberRow, { borderBottomColor: themeColors.border }]}>
                        <View style={[styles.avatar, { backgroundColor: themeColors.primary + '20' }]}>
                            {item.avatar ? (
                                <Image source={{ uri: item.avatar }} style={styles.avatarImg} />
                            ) : (
                                <Text style={[styles.avatarInitial, { color: themeColors.primary }]}>
                                    {item.name?.charAt(0)}
                                </Text>
                            )}
                        </View>
                        <View style={styles.memberInfo}>
                            <Text style={[styles.memberName, { color: themeColors.text }]}>{item.name}</Text>
                            <Text style={[styles.readAt, { color: themeColors.textMuted }]}>
                                Read {new Date(item.read_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                            </Text>
                        </View>
                    </View>
                )}
                contentContainerStyle={styles.list}
                ListEmptyComponent={
                    <Text style={[styles.empty, { color: themeColors.textMuted }]}>No one has read this yet.</Text>
                }
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: {
        padding: spacing.xl,
        alignItems: 'center',
        gap: spacing.sm,
        borderBottomWidth: 1,
        borderBottomColor: '#eee',
    },
    totalText: {
        fontSize: 18,
        fontWeight: fontWeights.bold,
    },
    list: { paddingVertical: spacing.sm },
    memberRow: {
        flexDirection: 'row',
        padding: spacing.md,
        alignItems: 'center',
        borderBottomWidth: 1,
    },
    avatar: {
        width: 44,
        height: 44,
        borderRadius: 22,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: spacing.md,
        overflow: 'hidden',
    },
    avatarImg: { width: '100%', height: '100%' },
    avatarInitial: { fontSize: 18, fontWeight: fontWeights.bold },
    memberInfo: { flex: 1 },
    memberName: { fontSize: 16, fontWeight: fontWeights.semibold },
    readAt: { fontSize: 12, marginTop: 2 },
    empty: { textAlign: 'center', marginTop: 40, fontSize: 14 },
});
