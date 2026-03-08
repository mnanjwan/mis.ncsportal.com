import React, { useEffect, useState, useCallback } from 'react';
import {
    View,
    Text,
    StyleSheet,
    FlatList,
    TouchableOpacity,
    ActivityIndicator,
    Alert,
    SafeAreaView,
} from 'react-native';
import { useRoute, useNavigation, RouteProp, useFocusEffect } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { chatApi } from '../../api/chatApi';
import { ChatStackParamList } from '../../navigation/ChatStack';
import { useThemeColor, spacing, fontWeights, fontSizes } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import { useAppSelector } from '../../hooks/redux';

type RouteParams = { RoomDetails: { roomId: number; roomName: string } };
type Nav = NativeStackNavigationProp<ChatStackParamList, 'RoomDetails'>;

export function RoomDetailsScreen() {
    const route = useRoute<RouteProp<RouteParams, 'RoomDetails'>>();
    const navigation = useNavigation<Nav>();
    const { roomId, roomName } = route.params;
    const themeColors = useThemeColor();
    const user = useAppSelector((s) => s.auth.user);
    const isStaffOfficer = user?.roles?.includes('Staff Officer');

    const [members, setMembers] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);

    const loadMembers = useCallback(async () => {
        try {
            const res = await chatApi.members(roomId);
            if (res.success && res.data) {
                setMembers(res.data);
            }
        } catch {
            Alert.alert('Error', 'Failed to load group members');
        } finally {
            setLoading(false);
        }
    }, [roomId]);

    useFocusEffect(
        useCallback(() => {
            loadMembers();
        }, [loadMembers])
    );

    const onRemoveMember = (memberId: number, name: string) => {
        Alert.alert(
            'Remove Member',
            `Are you sure you want to remove ${name} from this room?`,
            [
                { text: 'Cancel', style: 'cancel' },
                {
                    text: 'Remove',
                    style: 'destructive',
                    onPress: async () => {
                        try {
                            const res = await chatApi.removeMember(roomId, memberId);
                            if (res.success) {
                                setMembers((prev) => prev.filter((m) => m.officer_id !== memberId));
                            } else {
                                Alert.alert('Removal Failed', res.message || 'You do not have permission to remove this member');
                            }
                        } catch (err: any) {
                            Alert.alert('Error', err.response?.data?.message || 'Failed to remove member. Please check your connection.');
                        }
                    },
                },
            ]
        );
    };

    const renderMember = ({ item }: { item: any }) => {
        const isSelf = item.officer_id === user?.officer?.id;

        // Defensive name handling
        const displayName = item.name
            ? item.name
            : (item.initials || item.surname)
                ? `${item.initials || ''} ${item.surname || ''}`.trim()
                : 'Unknown Officer';

        return (
            <View style={[styles.memberRow, { borderBottomColor: themeColors.border }]}>
                <View style={styles.memberAvatar}>
                    <Text style={styles.avatarText}>{displayName[0].toUpperCase()}</Text>
                </View>
                <View style={styles.memberInfo}>
                    <Text style={[styles.memberName, { color: themeColors.text }]}>
                        {displayName} {isSelf && '(You)'}
                    </Text>
                    <Text style={[styles.memberRank, { color: themeColors.textMuted }]}>
                        {item.rank || item.substantive_rank || 'No Rank'}
                    </Text>
                </View>
                {isStaffOfficer && !isSelf && (
                    <TouchableOpacity onPress={() => onRemoveMember(item.officer_id, displayName)}>
                        <Ionicons name="person-remove" size={20} color="#ef4444" />
                    </TouchableOpacity>
                )}
            </View>
        );
    };

    return (
        <SafeAreaView style={[styles.container, { backgroundColor: themeColors.background }]}>
            <View style={styles.header}>
                <View style={[styles.largeAvatar, { backgroundColor: themeColors.primary + '20' }]}>
                    <Ionicons name="people" size={48} color={themeColors.primary} />
                </View>
                <Text style={[styles.headerName, { color: themeColors.text }]}>{roomName}</Text>
                <Text style={[styles.memberCount, { color: themeColors.textMuted }]}>
                    {members.length} Members
                </Text>
            </View>

            <View style={[styles.sectionHeader, { backgroundColor: themeColors.surface }]}>
                <Text style={[styles.sectionTitle, { color: themeColors.textSecondary }]}>Room Members</Text>
                {isStaffOfficer && (
                    <TouchableOpacity onPress={() => navigation.navigate('MemberSearch', { roomId })}>
                        <Ionicons name="person-add" size={20} color={themeColors.primary} />
                    </TouchableOpacity>
                )}
            </View>

            {loading ? (
                <ActivityIndicator style={{ marginTop: 20 }} color={themeColors.primary} />
            ) : (
                <FlatList
                    data={members}
                    keyExtractor={(item) => String(item.id)}
                    renderItem={renderMember}
                    contentContainerStyle={styles.list}
                />
            )}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1 },
    header: { alignItems: 'center', padding: 24 },
    largeAvatar: { width: 100, height: 100, borderRadius: 50, justifyContent: 'center', alignItems: 'center', marginBottom: 16 },
    headerName: { fontSize: 20, fontWeight: fontWeights.bold, marginBottom: 4 },
    memberCount: { fontSize: 14 },
    sectionHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
        paddingHorizontal: 16,
        paddingVertical: 10,
    },
    sectionTitle: { fontSize: 12, fontWeight: fontWeights.bold, textTransform: 'uppercase', letterSpacing: 1 },
    list: { paddingBottom: 40 },
    memberRow: { flexDirection: 'row', alignItems: 'center', padding: 16, borderBottomWidth: StyleSheet.hairlineWidth },
    memberAvatar: { width: 40, height: 40, borderRadius: 20, backgroundColor: '#f3f4f6', justifyContent: 'center', alignItems: 'center', marginRight: 12 },
    avatarText: { fontSize: 16, fontWeight: fontWeights.bold, color: '#374151' },
    memberInfo: { flex: 1 },
    memberName: { fontSize: 15, fontWeight: fontWeights.semibold },
    memberRank: { fontSize: 13 },
});
