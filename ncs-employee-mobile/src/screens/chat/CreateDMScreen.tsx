import React, { useState, useCallback } from 'react';
import {
    View,
    Text,
    StyleSheet,
    TextInput,
    TouchableOpacity,
    FlatList,
    ActivityIndicator,
    Alert,
    SafeAreaView,
    KeyboardAvoidingView,
    Platform,
    useColorScheme,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { chatApi } from '../../api/chatApi';
import type { OfficerSearchResult } from '../../api/chatApi';
import { ChatStackParamList } from '../../navigation/ChatStack';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Nav = NativeStackNavigationProp<ChatStackParamList, 'CreateDM'>;

export function CreateDMScreen() {
    const navigation = useNavigation<Nav>();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';

    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<OfficerSearchResult[]>([]);
    const [searching, setSearching] = useState(false);
    const [creating, setCreating] = useState(false);

    const searchDebounceRef = React.useRef<NodeJS.Timeout | null>(null);

    const handleSearch = useCallback((query: string) => {
        setSearchQuery(query);
        if (searchDebounceRef.current) clearTimeout(searchDebounceRef.current);
        if (query.trim().length < 2) {
            setSearchResults([]);
            return;
        }
        searchDebounceRef.current = setTimeout(async () => {
            setSearching(true);
            try {
                const res = await chatApi.searchOfficers(query.trim());
                if (res.success && res.data) {
                    setSearchResults(res.data);
                }
            } catch {
                // silent
            } finally {
                setSearching(false);
            }
        }, 400);
    }, []);

    const handleCreateDM = async (officer: OfficerSearchResult) => {
        setCreating(true);
        try {
            const dmName = `${officer.substantive_rank || ''} ${officer.surname || officer.full_name || ''}`.trim();
            const res = await chatApi.createGroup({
                name: dmName,
                description: 'Direct Message', // Using group type internally right now 
                member_ids: [officer.id],
            });
            if (res.success && res.data) {
                navigation.replace('ChatRoom', { roomId: res.data.id, roomName: res.data.name });
            } else {
                Alert.alert('Error', res.message || 'Failed to start chat');
            }
        } catch {
            Alert.alert('Error', 'An unexpected error occurred');
        } finally {
            setCreating(false);
        }
    };

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <KeyboardAvoidingView
                style={{ flex: 1 }}
                behavior={Platform.OS === 'ios' ? 'padding' : undefined}
                keyboardVerticalOffset={90}
            >
                <View style={styles.headerArea}>
                    <Text style={[styles.title, { color: themeColors.text }]}>Direct Message</Text>
                    <Text style={[styles.subtitle, { color: themeColors.textMuted }]}>
                        Search for an officer to start a 1-on-1 chat instantly.
                    </Text>

                    <View style={[styles.searchRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                        <Ionicons name="search" size={20} color={themeColors.textMuted} />
                        <TextInput
                            style={[styles.searchInput, { color: themeColors.text }]}
                            value={searchQuery}
                            onChangeText={handleSearch}
                            placeholder="Name or service number..."
                            placeholderTextColor={themeColors.textMuted}
                            autoFocus
                        />
                        {searching && <ActivityIndicator size="small" color={themeColors.primary} />}
                    </View>
                </View>

                <FlatList
                    data={searchResults}
                    keyExtractor={(item) => String(item.id)}
                    renderItem={({ item }) => (
                        <TouchableOpacity
                            style={[styles.officerRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
                            onPress={() => handleCreateDM(item)}
                            activeOpacity={0.7}
                            disabled={creating}
                        >
                            <View style={[styles.officerAvatar, { backgroundColor: themeColors.primaryMuted }]}>
                                <Text style={[styles.officerAvatarText, { color: themeColors.primary }]}>
                                    {(item.full_name || item.surname || String(item.id)).charAt(0).toUpperCase()}
                                </Text>
                            </View>
                            <View style={styles.officerInfo}>
                                <Text style={[styles.officerName, { color: themeColors.text }]}>
                                    {item.substantive_rank ? `${item.substantive_rank} ` : ''}{item.full_name || `${item.initials || ''} ${item.surname || ''}`.trim()}
                                </Text>
                                <Text style={[styles.officerSN, { color: themeColors.textMuted }]}>
                                    {item.service_number}{item.presentStation?.name ? ` · ${item.presentStation.name}` : ''}
                                </Text>
                            </View>
                            <View style={[styles.chatBtn, { backgroundColor: themeColors.primary }]}>
                                <Ionicons name="chatbubble-ellipses" size={16} color="#ffffff" />
                            </View>
                        </TouchableOpacity>
                    )}
                    ListEmptyComponent={
                        searchQuery.length >= 2 && !searching ? (
                            <Text style={[styles.emptySearch, { color: themeColors.textMuted }]}>No officers found matching "{searchQuery}"</Text>
                        ) : null
                    }
                    contentContainerStyle={styles.listContent}
                />
            </KeyboardAvoidingView>

            {creating && (
                <View style={[styles.overlay]}>
                    <ActivityIndicator size="large" color={themeColors.primary} />
                    <Text style={{ marginTop: 12, color: themeColors.text, fontWeight: '600' }}>Starting chat...</Text>
                </View>
            )}
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    headerArea: { paddingHorizontal: spacing.xl, paddingTop: spacing.xl, paddingBottom: spacing.md },
    title: { fontSize: 24, fontWeight: fontWeights.bold, marginBottom: 4 },
    subtitle: { fontSize: 14, marginBottom: spacing.xl },

    searchRow: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 12,
        borderWidth: 1,
        paddingHorizontal: spacing.md,
        height: 50,
        gap: spacing.sm,
    },
    searchInput: { flex: 1, fontSize: 16 },

    listContent: { paddingHorizontal: spacing.xl, paddingBottom: spacing['2xl'] },

    officerRow: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 12,
        borderWidth: 1,
        padding: spacing.md,
        marginBottom: spacing.sm,
    },
    officerAvatar: {
        width: 44,
        height: 44,
        borderRadius: 22,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: spacing.md,
    },
    officerAvatarText: { fontSize: 18, fontWeight: fontWeights.bold },
    officerInfo: { flex: 1 },
    officerName: { fontSize: 16, fontWeight: fontWeights.semibold, marginBottom: 4 },
    officerSN: { fontSize: 13 },

    chatBtn: {
        width: 36,
        height: 36,
        borderRadius: 18,
        justifyContent: 'center',
        alignItems: 'center',
        marginLeft: spacing.md,
    },

    emptySearch: { textAlign: 'center', marginTop: spacing.xl, fontSize: 15 },

    overlay: {
        ...StyleSheet.absoluteFillObject,
        backgroundColor: 'rgba(255,255,255,0.7)',
        justifyContent: 'center',
        alignItems: 'center',
        zIndex: 10,
    },
});
