import React, { useState, useEffect } from 'react';
import {
    View,
    Text,
    TextInput,
    TouchableOpacity,
    StyleSheet,
    Alert,
    ActivityIndicator,
    KeyboardAvoidingView,
    Platform,
    ScrollView,
    SafeAreaView,
    StatusBar,
    useColorScheme,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { pharmacyApi } from '../../api/pharmacyApi';
import type { PharmacyDrug } from '../../api/pharmacyApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

export function CreateRequisitionScreen() {
    const navigation = useNavigation();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';

    const [notes, setNotes] = useState('');
    const [catalog, setCatalog] = useState<PharmacyDrug[]>([]);
    const [selectedItems, setSelectedItems] = useState<{ drug: PharmacyDrug; qty: string }[]>([]);

    const [loading, setLoading] = useState(false);
    const [fetching, setFetching] = useState(true);

    useEffect(() => {
        pharmacyApi.drugs().then(res => {
            if (res.success && res.data) setCatalog(res.data);
            setFetching(false);
        }).catch(() => setFetching(false));
    }, []);

    const addDrug = (drug: PharmacyDrug) => {
        if (selectedItems.find(i => i.drug.id === drug.id)) return;
        setSelectedItems([...selectedItems, { drug, qty: '10' }]);
    };

    const removeDrug = (id: number) => {
        setSelectedItems(selectedItems.filter(i => i.drug.id !== id));
    };

    const updateQty = (id: number, val: string) => {
        setSelectedItems(selectedItems.map(i => i.drug.id === id ? { ...i, qty: val } : i));
    };

    const handleSubmit = async () => {
        if (selectedItems.length === 0) {
            Alert.alert('Validation Error', 'Please add at least one drug to your requisition.');
            return;
        }

        const payloadItems = selectedItems.map(i => ({
            drug_id: i.drug.id,
            requested_quantity: parseInt(i.qty) || 0
        })).filter(i => i.requested_quantity > 0);

        if (payloadItems.length === 0) {
            Alert.alert('Validation Error', 'Quantities must be greater than zero.');
            return;
        }

        setLoading(true);
        try {
            const res = await pharmacyApi.createRequisition({ items: payloadItems, notes });
            if (res.success) {
                Alert.alert('Success', 'Requisition draft created. It can now be submitted for approval.');
                navigation.goBack();
            } else {
                throw new Error('Failed to create requisition');
            }
        } catch (err: any) {
            Alert.alert('Error', err.message || 'Could not create requisition. Please check your connection.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
            <KeyboardAvoidingView behavior={Platform.OS === 'ios' ? 'padding' : undefined} style={styles.flex1}>

                <View style={styles.header}>
                    <TouchableOpacity onPress={() => navigation.goBack()} style={styles.backBtn}>
                        <Ionicons name="close" size={28} color={themeColors.text} />
                    </TouchableOpacity>
                    <Text style={[styles.headerTitle, { color: themeColors.text }]}>New Requisition</Text>
                    <View style={{ width: 40 }} />
                </View>

                <ScrollView style={styles.flex1} contentContainerStyle={styles.scrollContent}>

                    <Text style={[styles.sectionTitle, { color: themeColors.textSecondary }]}>SELECT DRUGS</Text>
                    <View style={[styles.catalogWrap, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                        {fetching ? (
                            <ActivityIndicator size="small" color={themeColors.primary} style={{ margin: 20 }} />
                        ) : catalog.length === 0 ? (
                            <Text style={{ margin: 20, textAlign: 'center', color: themeColors.textMuted }}>No drugs found in catalog.</Text>
                        ) : (
                            <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={{ padding: 12, gap: 8 }}>
                                {catalog.map(drug => (
                                    <TouchableOpacity
                                        key={drug.id}
                                        style={[
                                            styles.catalogPill,
                                            { borderColor: themeColors.border, backgroundColor: themeColors.background },
                                            selectedItems.some(i => i.drug.id === drug.id) && { borderColor: themeColors.primary, backgroundColor: themeColors.primaryLight + '20' }
                                        ]}
                                        onPress={() => addDrug(drug)}
                                    >
                                        <Ionicons name="medical" size={14} color={selectedItems.some(i => i.drug.id === drug.id) ? themeColors.primary : themeColors.textMuted} />
                                        <Text style={[styles.pillText, { color: themeColors.text }]}>{drug.name}</Text>
                                    </TouchableOpacity>
                                ))}
                            </ScrollView>
                        )}
                    </View>

                    <Text style={[styles.sectionTitle, { color: themeColors.textSecondary, marginTop: 24 }]}>SELECTED ITEMS</Text>
                    {selectedItems.length === 0 ? (
                        <View style={[styles.emptyWrap, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                            <Text style={{ color: themeColors.textMuted, fontStyle: 'italic' }}>Tap drugs above to add them to your request.</Text>
                        </View>
                    ) : (
                        selectedItems.map((item, idx) => (
                            <View key={item.drug.id} style={[styles.itemRow, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
                                <View style={{ flex: 1 }}>
                                    <Text style={[styles.itemName, { color: themeColors.text }]}>{item.drug.name}</Text>
                                    <Text style={[styles.itemSub, { color: themeColors.textMuted }]}>{item.drug.category} • {item.drug.unit}</Text>
                                </View>

                                <View style={styles.qtyWrap}>
                                    <Text style={[styles.qtyLabel, { color: themeColors.textMuted }]}>Qty:</Text>
                                    <TextInput
                                        style={[styles.qtyInput, { color: themeColors.text, borderColor: themeColors.border, backgroundColor: themeColors.background }]}
                                        value={item.qty}
                                        onChangeText={(val) => updateQty(item.drug.id, val.replace(/[^0-9]/g, ''))}
                                        keyboardType="numeric"
                                        maxLength={5}
                                    />
                                    <TouchableOpacity onPress={() => removeDrug(item.drug.id)} style={{ padding: 4, marginLeft: 8 }}>
                                        <Ionicons name="trash-outline" size={20} color={themeColors.danger} />
                                    </TouchableOpacity>
                                </View>
                            </View>
                        ))
                    )}

                    <Text style={[styles.sectionTitle, { color: themeColors.textSecondary, marginTop: 24 }]}>ADDITIONAL NOTES</Text>
                    <TextInput
                        style={[styles.notesInput, { color: themeColors.text, borderColor: themeColors.border, backgroundColor: themeColors.surface }]}
                        placeholder="E.g., Monthly supply for Lagos clinic..."
                        placeholderTextColor={themeColors.textMuted}
                        value={notes}
                        onChangeText={setNotes}
                        multiline
                        textAlignVertical="top"
                    />

                </ScrollView>

                <View style={[styles.footer, { backgroundColor: themeColors.surface, borderTopColor: themeColors.border }]}>
                    <TouchableOpacity
                        style={[styles.primaryBtn, { backgroundColor: themeColors.primary, opacity: selectedItems.length === 0 ? 0.6 : 1 }]}
                        onPress={handleSubmit}
                        disabled={selectedItems.length === 0 || loading}
                    >
                        {loading ? <ActivityIndicator color="#fff" /> : <Text style={styles.primaryBtnText}>Create Requisition</Text>}
                    </TouchableOpacity>
                </View>

            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    flex1: { flex: 1 },
    scrollContent: { padding: spacing.xl, paddingBottom: 40 },

    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', paddingHorizontal: spacing.lg, paddingVertical: spacing.md },
    backBtn: { padding: 4 },
    headerTitle: { fontSize: 18, fontWeight: fontWeights.bold },

    sectionTitle: { fontSize: 12, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginBottom: spacing.sm, marginLeft: 4 },

    catalogWrap: { borderRadius: 16, borderWidth: 1, overflow: 'hidden' },
    catalogPill: { flexDirection: 'row', alignItems: 'center', paddingHorizontal: 12, paddingVertical: 8, borderRadius: 20, borderWidth: 1, gap: 6 },
    pillText: { fontSize: 13, fontWeight: fontWeights.medium },

    emptyWrap: { borderRadius: 16, borderWidth: 1, padding: spacing.lg, alignItems: 'center', borderStyle: 'dashed' },

    itemRow: { flexDirection: 'row', alignItems: 'center', borderRadius: 16, borderWidth: 1, padding: spacing.md, marginBottom: 8 },
    itemName: { fontSize: 15, fontWeight: fontWeights.bold, marginBottom: 2 },
    itemSub: { fontSize: 12 },
    qtyWrap: { flexDirection: 'row', alignItems: 'center' },
    qtyLabel: { fontSize: 12, marginRight: 6 },
    qtyInput: { width: 60, height: 36, borderWidth: 1, borderRadius: 8, textAlign: 'center', fontSize: 15, fontWeight: fontWeights.bold },

    notesInput: { borderRadius: 16, borderWidth: 1, padding: spacing.lg, minHeight: 100, fontSize: 15 },

    footer: { padding: spacing.xl, borderTopWidth: 1 },
    primaryBtn: { height: 50, borderRadius: 12, justifyContent: 'center', alignItems: 'center' },
    primaryBtnText: { color: '#fff', fontSize: 16, fontWeight: fontWeights.bold },
});
