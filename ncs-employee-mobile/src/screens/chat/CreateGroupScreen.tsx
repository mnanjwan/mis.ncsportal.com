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

type Nav = NativeStackNavigationProp<ChatStackParamList, 'CreateGroup'>;

export function CreateGroupScreen() {
    const navigation = useNavigation<Nav>();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';

    const [groupName, setGroupName] = useState('');
    const [groupDesc, setGroupDesc] = useState('');
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<OfficerSearchResult[]>([]);
    const [selectedMembers, setSelectedMembers] = useState<OfficerSearchResult[]>([]);
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

    const toggleMember = (officer: OfficerSearchResult) => {
        setSelectedMembers((prev) => {
            const exists = prev.find(m => m.id === officer.id);
            if (exists) return prev.filter(m => m.id !== officer.id);
            return [...prev, officer];
        });
    };

    const isMemberSelected = (id: number) => selectedMembers.some(m => m.id === id);

    const handleCreate = async () => {
        if (!groupName.trim()) {
            Alert.alert('Required', 'Please enter a group name');
            return;
        }
        if (selectedMembers.length === 0) {
            Alert.alert('Required', 'Please add at least one member');
            return;
        }

        setCreating(true);
        try {
            const res = await chatApi.createGroup({
                name: groupName.trim(),
                description: groupDesc.trim() || undefined,
                member_ids: selectedMembers.map(m => m.id),
            });
            if (res.success && res.data) {
                Alert.alert('Group Created', `"${groupName.trim()}" has been created successfully.`, [
                    {
                        text: 'Open Chat', onPress: () => {
                            navigation.replace('ChatRoom', { roomId: res.data!.id, roomName: res.data!.name });
                        }
                    },
                ]);
            } else {
                Alert.alert('Error', res.message || 'Failed to create group');
            }
        } catch {
            Alert.alert('Error', 'Failed to create group. Please try again.');
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
                <FlatList
                    data={searchResults}
                    keyExtractor={(item) => String(item.id)}
                    ListHeaderComponent={
                        <View>
                            {/* Group Info */}
                            <View style={[styles.section, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                <Text style={[styles.label, { color: themeColors.textSecondary }]}>Group Name *</Text>
                                <TextInput
                                    style={[styles.textInput, { backgroundColor: themeColors.background, color: themeColors.text, borderColor: themeColors.border }]}
                                    value={groupName}
                                    onChangeText={setGroupName}
                                    placeholder="e.g. Project Alpha Team"
                                    placeholderTextColor={themeColors.textMuted}
                                    maxLength={100}
                                />

                                <Text style={[styles.label, { color: themeColors.textSecondary, marginTop: spacing.lg }]}>Description (optional)</Text>
                                <TextInput
                                    style={[styles.textInput, styles.textArea, { backgroundColor: themeColors.background, color: themeColors.text, borderColor: themeColors.border }]}
                                    value={groupDesc}
                                    onChangeText={setGroupDesc}
                                    placeholder="What's this group about?"
                                    placeholderTextColor={themeColors.textMuted}
                                    multiline
                                    maxLength={500}
                                />
                            </View>

                            {/* Selected Members */}
                            {selectedMembers.length > 0 && (
                                <View style={styles.selectedSection}>
                                    <Text style={[styles.sectionLabel, { color: themeColors.textMuted }]}>
                                        SELECTED ({selectedMembers.length})
                                    </Text>
                                    <View style={styles.selectedChips}>
                                        {selectedMembers.map((m) => (
                                            <TouchableOpacity
                                                key={m.id}
                                                style={[styles.chip, { backgroundColor: themeColors.primary }]}
                                                onPress={() => toggleMember(m)}
                                            >
                                                <Text style={styles.chipText} numberOfLines={1}>
                                                    {m.rank ? `${m.rank} ` : ''}{m.name.split(' ')[0]}
                                                </Text>
                                                <Ionicons name="close" size={14} color="#ffffff" />
                                            </TouchableOpacity>
                                        ))}
                                    </View>
                                </View>
                            )}

                            {/* Search Officers */}
                            <View style={styles.searchSection}>
                                <Text style={[styles.sectionLabel, { color: themeColors.textMuted }]}>ADD MEMBERS</Text>
                                <View style={[styles.searchRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                    <Ionicons name="search" size={20} color={themeColors.textMuted} />
                                    <TextInput
                                        style={[styles.searchInput, { color: themeColors.text }]}
                                        value={searchQuery}
                                        onChangeText={handleSearch}
                                        placeholder="Search officers by name or service number..."
                                        placeholderTextColor={themeColors.textMuted}
                                    />
                                    {searching && <ActivityIndicator size="small" color={themeColors.primary} />}
                                </View>
                            </View>
                        </View>
                    }
                    renderItem={({ item }) => {
                        const selected = isMemberSelected(item.id);
                        return (
                            <TouchableOpacity
                                style={[styles.officerRow, { backgroundColor: themeColors.surface, borderColor: selected ? themeColors.primary : themeColors.border }]}
                                onPress={() => toggleMember(item)}
                                activeOpacity={0.7}
                            >
                                <View style={[styles.officerAvatar, { backgroundColor: selected ? themeColors.primary : themeColors.surfaceTertiary }]}>
                                    <Text style={[styles.officerAvatarText, { color: selected ? '#ffffff' : themeColors.text }]}>
                                        {item.name.charAt(0).toUpperCase()}
                                    </Text>
                                </View>
                                <View style={styles.officerInfo}>
                                    <Text style={[styles.officerName, { color: themeColors.text }]}>
                                        {item.rank ? `${item.rank} ` : ''}{item.name}
                                    </Text>
                                    <Text style={[styles.officerSN, { color: themeColors.textMuted }]}>
                                        {item.service_number}{item.command?.name ? ` · ${item.command.name}` : ''}
                                    </Text>
                                </View>
                                <Ionicons
                                    name={selected ? 'checkmark-circle' : 'ellipse-outline'}
                                    size={24}
                                    color={selected ? themeColors.primary : themeColors.textMuted}
                                />
                            </TouchableOpacity>
                        );
                    }}
                    ListEmptyComponent={
                        searchQuery.length >= 2 && !searching ? (
                            <Text style={[styles.emptySearch, { color: themeColors.textMuted }]}>No officers found</Text>
                        ) : null
                    }
                    contentContainerStyle={styles.listContent}
                    ListFooterComponent={<View style={{ height: 100 }} />}
                />

                {/* Create Button */}
                <View style={[styles.footer, { backgroundColor: themeColors.surface, borderTopColor: themeColors.border }]}>
                    <TouchableOpacity
                        style={[styles.createBtn, { backgroundColor: themeColors.primary }, creating && styles.createBtnDisabled]}
                        onPress={handleCreate}
                        disabled={creating}
                        activeOpacity={0.8}
                    >
                        {creating ? (
                            <ActivityIndicator color="#ffffff" />
                        ) : (
                            <>
                                <Ionicons name="chatbubbles" size={20} color="#ffffff" style={{ marginRight: spacing.sm }} />
                                <Text style={styles.createBtnText}>Create Group</Text>
                            </>
                        )}
                    </TouchableOpacity>
                </View>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    listContent: { paddingHorizontal: spacing.xl, paddingTop: spacing.md },

    section: {
        borderRadius: 16,
        borderWidth: 1,
        padding: spacing.lg,
        marginBottom: spacing.lg,
    },
    label: {
        fontSize: 13,
        fontWeight: fontWeights.semibold,
        marginBottom: spacing.sm,
    },
    textInput: {
        borderRadius: 12,
        borderWidth: 1,
        paddingHorizontal: spacing.md,
        paddingVertical: 12,
        fontSize: 15,
    },
    textArea: {
        minHeight: 80,
        textAlignVertical: 'top',
    },

    selectedSection: { marginBottom: spacing.lg },
    sectionLabel: {
        fontSize: 12,
        fontWeight: fontWeights.bold,
        letterSpacing: 1.2,
        marginBottom: spacing.md,
        paddingHorizontal: spacing.xs,
    },
    selectedChips: { flexDirection: 'row', flexWrap: 'wrap', gap: spacing.sm },
    chip: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 20,
        paddingHorizontal: 12,
        paddingVertical: 6,
        gap: 6,
    },
    chipText: { color: '#ffffff', fontSize: 13, fontWeight: fontWeights.semibold, maxWidth: 120 },

    searchSection: { marginBottom: spacing.md },
    searchRow: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 12,
        borderWidth: 1,
        paddingHorizontal: spacing.md,
        height: 48,
        gap: spacing.sm,
    },
    searchInput: { flex: 1, fontSize: 15 },

    officerRow: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 12,
        borderWidth: 1,
        padding: spacing.md,
        marginBottom: spacing.xs,
    },
    officerAvatar: {
        width: 40,
        height: 40,
        borderRadius: 20,
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: spacing.md,
    },
    officerAvatarText: { fontSize: 16, fontWeight: fontWeights.bold },
    officerInfo: { flex: 1 },
    officerName: { fontSize: 15, fontWeight: fontWeights.semibold, marginBottom: 2 },
    officerSN: { fontSize: 13 },

    emptySearch: { textAlign: 'center', marginTop: spacing.xl, fontSize: 14 },

    footer: {
        paddingHorizontal: spacing.xl,
        paddingVertical: spacing.md,
        borderTopWidth: 1,
    },
    createBtn: {
        borderRadius: 12,
        height: 50,
        flexDirection: 'row',
        justifyContent: 'center',
        alignItems: 'center',
    },
    createBtnDisabled: { opacity: 0.6 },
    createBtnText: { color: '#ffffff', fontSize: 16, fontWeight: fontWeights.bold },
});
