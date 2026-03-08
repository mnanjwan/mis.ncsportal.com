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
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { chatApi } from '../../api/chatApi';
import type { OfficerSearchResult } from '../../api/chatApi';
import { ChatStackParamList } from '../../navigation/ChatStack';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type RouteParams = { MemberSearch: { roomId: number } };
type Nav = NativeStackNavigationProp<ChatStackParamList, 'MemberSearch'>;

export function MemberSearchScreen() {
    const navigation = useNavigation<Nav>();
    const route = useRoute<RouteProp<RouteParams, 'MemberSearch'>>();
    const { roomId } = route.params;
    const themeColors = useThemeColor();

    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState<OfficerSearchResult[]>([]);
    const [selectedMembers, setSelectedMembers] = useState<OfficerSearchResult[]>([]);
    const [searching, setSearching] = useState(false);
    const [adding, setAdding] = useState(false);

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
            const exists = prev.find((m) => m.id === officer.id);
            if (exists) return prev.filter((m) => m.id !== officer.id);
            return [...prev, officer];
        });
    };

    const isMemberSelected = (id: number) => selectedMembers.some((m) => m.id === id);

    const handleAddMembers = async () => {
        if (selectedMembers.length === 0) {
            Alert.alert('Required', 'Please select at least one officer to add');
            return;
        }

        setAdding(true);
        try {
            const res = await chatApi.addMembers(roomId, selectedMembers.map((m) => m.id));
            if (res.success) {
                Alert.alert('Success', `${selectedMembers.length} members added successfully.`);
                navigation.goBack();
            } else {
                Alert.alert('Error', 'Failed to add members');
            }
        } catch {
            Alert.alert('Error', 'Failed to add members. They might already be in the room.');
        } finally {
            setAdding(false);
        }
    };

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <KeyboardAvoidingView
                style={{ flex: 1 }}
                behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            >
                <View style={styles.header}>
                    <View style={[styles.searchRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                        <Ionicons name="search" size={20} color={themeColors.textMuted} />
                        <TextInput
                            style={[styles.searchInput, { color: themeColors.text }]}
                            value={searchQuery}
                            onChangeText={handleSearch}
                            placeholder="Search by name or SN..."
                            placeholderTextColor={themeColors.textMuted}
                            autoFocus
                        />
                        {searching && <ActivityIndicator size="small" color={themeColors.primary} />}
                    </View>
                </View>

                {selectedMembers.length > 0 && (
                    <View style={styles.selectedSection}>
                        <FlatList
                            horizontal
                            data={selectedMembers}
                            keyExtractor={(item) => String(item.id)}
                            renderItem={({ item }) => (
                                <TouchableOpacity
                                    style={[styles.chip, { backgroundColor: themeColors.primary }]}
                                    onPress={() => toggleMember(item)}
                                >
                                    <Text style={styles.chipText} numberOfLines={1}>
                                        {item.surname || item.full_name?.split(' ')[0]}
                                    </Text>
                                    <Ionicons name="close" size={14} color="#ffffff" />
                                </TouchableOpacity>
                            )}
                            contentContainerStyle={styles.chipList}
                            showsHorizontalScrollIndicator={false}
                        />
                    </View>
                )}

                <FlatList
                    data={searchResults}
                    keyExtractor={(item) => String(item.id)}
                    renderItem={({ item }) => {
                        const selected = isMemberSelected(item.id);
                        return (
                            <TouchableOpacity
                                style={[styles.officerRow, { borderBottomColor: themeColors.border }]}
                                onPress={() => toggleMember(item)}
                                activeOpacity={0.7}
                            >
                                <View style={styles.officerAvatar}>
                                    <Text style={styles.avatarText}>
                                        {(item.full_name || item.surname || '?').charAt(0).toUpperCase()}
                                    </Text>
                                </View>
                                <View style={styles.officerInfo}>
                                    <Text style={[styles.officerName, { color: themeColors.text }]}>
                                        {item.full_name || `${item.initials} ${item.surname}`}
                                    </Text>
                                    <Text style={[styles.officerSN, { color: themeColors.textMuted }]}>
                                        {item.service_number} {item.presentStation?.name ? `· ${item.presentStation.name}` : ''}
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
                />

                {selectedMembers.length > 0 && (
                    <View style={[styles.footer, { borderTopColor: themeColors.border }]}>
                        <TouchableOpacity
                            style={[styles.addBtn, { backgroundColor: themeColors.primary }]}
                            onPress={handleAddMembers}
                            disabled={adding}
                        >
                            {adding ? (
                                <ActivityIndicator color="#ffffff" />
                            ) : (
                                <Text style={styles.addBtnText}>Add {selectedMembers.length} Members</Text>
                            )}
                        </TouchableOpacity>
                    </View>
                )}
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    header: { padding: 16 },
    searchRow: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 12,
        borderWidth: 1,
        paddingHorizontal: 12,
        height: 48,
    },
    searchInput: { flex: 1, fontSize: 15, marginLeft: 8 },
    selectedSection: { height: 50, marginBottom: 8 },
    chipList: { paddingHorizontal: 16, gap: 8 },
    chip: {
        flexDirection: 'row',
        alignItems: 'center',
        borderRadius: 20,
        paddingHorizontal: 12,
        paddingVertical: 6,
        height: 32,
    },
    chipText: { color: '#ffffff', fontSize: 13, fontWeight: fontWeights.semibold, marginRight: 4, maxWidth: 100 },
    officerRow: {
        flexDirection: 'row',
        alignItems: 'center',
        padding: 16,
        borderBottomWidth: StyleSheet.hairlineWidth,
    },
    officerAvatar: {
        width: 44,
        height: 44,
        borderRadius: 22,
        backgroundColor: '#f3f4f6',
        justifyContent: 'center',
        alignItems: 'center',
        marginRight: 12,
    },
    avatarText: { fontSize: 16, fontWeight: fontWeights.bold, color: '#374151' },
    officerInfo: { flex: 1 },
    officerName: { fontSize: 15, fontWeight: fontWeights.semibold },
    officerSN: { fontSize: 13, marginTop: 2 },
    emptySearch: { textAlign: 'center', marginTop: 40 },
    footer: { padding: 16, borderTopWidth: 1 },
    addBtn: { height: 50, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    addBtnText: { color: '#ffffff', fontSize: 16, fontWeight: fontWeights.bold },
});
