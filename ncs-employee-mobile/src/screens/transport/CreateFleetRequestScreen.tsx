import React, { useState } from 'react';
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
import { useAppSelector } from '../../hooks/redux';
import { fleetApi } from '../../api/fleetApi';
import type { FleetRequestType } from '../../api/fleetApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type RequestOption = { type: FleetRequestType; label: string; icon: string; desc: string };

const REQUEST_TYPES: RequestOption[] = [
    { type: 'new_vehicle', label: 'New Vehicle', icon: 'car-sport', desc: 'Request an entirely new vehicle for the command' },
    { type: 'reallocation', label: 'Re-Allocation', icon: 'swap-horizontal', desc: 'Transfer a vehicle between commands' },
    { type: 'requisition', label: 'Requisition', icon: 'key', desc: 'Request an existing vehicle from the fleet pool' },
    { type: 'repair', label: 'Repair', icon: 'build', desc: 'Request maintenance or repair for a vehicle' },
];

export function CreateFleetRequestScreen() {
    const navigation = useNavigation();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';
    const { user } = useAppSelector((s) => s.auth);

    const [requestType, setRequestType] = useState<FleetRequestType>('new_vehicle');
    const [vehicleType, setVehicleType] = useState('Sedan');
    const [make, setMake] = useState('Toyota');
    const [model, setModel] = useState('Corolla');
    const [year, setYear] = useState('2023');
    const [quantity, setQuantity] = useState('1');
    const [amount, setAmount] = useState('');
    const [notes, setNotes] = useState('');
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSubmit = async () => {
        if (!make.trim() || !model.trim() || !quantity.trim() || isNaN(Number(quantity))) {
            Alert.alert('Validation Error', 'Please check the make, model, and quantity fields.');
            return;
        }

        setSubmitting(true);
        setError(null);

        try {
            await fleetApi.createRequest({
                request_type: requestType,
                requested_vehicle_type: vehicleType,
                requested_make: make,
                requested_model: model,
                requested_year: Number(year) || undefined,
                requested_quantity: Number(quantity),
                amount: Number(amount) || undefined,
                notes: notes || undefined,
            });

            Alert.alert('Success', 'Fleet request submitted successfully.', [
                { text: 'OK', onPress: () => navigation.goBack() }
            ]);
        } catch (err: any) {
            setError(err?.message || 'Failed to submit fleet request.');
        } finally {
            setSubmitting(false);
        }
    };

    const renderTypeSelector = () => (
        <View style={styles.typeGrid}>
            {REQUEST_TYPES.map((rt) => {
                const selected = requestType === rt.type;
                return (
                    <TouchableOpacity
                        key={rt.type}
                        style={[
                            styles.typeCard,
                            { backgroundColor: themeColors.surface, borderColor: selected ? themeColors.primary : themeColors.border },
                            selected && { backgroundColor: themeColors.primaryLight + '20' }
                        ]}
                        onPress={() => setRequestType(rt.type)}
                        activeOpacity={0.7}
                    >
                        <Ionicons name={rt.icon as any} size={24} color={selected ? themeColors.primary : themeColors.textMuted} />
                        <Text style={[styles.typeLabel, { color: selected ? themeColors.primary : themeColors.text }]} numberOfLines={1}>
                            {rt.label}
                        </Text>
                        <Text style={[styles.typeDesc, { color: themeColors.textMuted }]} numberOfLines={2}>
                            {rt.desc}
                        </Text>
                    </TouchableOpacity>
                );
            })}
        </View>
    );

    return (
        <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
            <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
            <KeyboardAvoidingView style={styles.flex} behavior={Platform.OS === 'ios' ? 'padding' : undefined} keyboardVerticalOffset={80}>
                <ScrollView style={styles.flex} contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>

                    <Text style={[styles.sectionLabel, { color: themeColors.textMuted }]}>1. REQUEST TYPE</Text>
                    {renderTypeSelector()}

                    <Text style={[styles.sectionLabel, { color: themeColors.textMuted, marginTop: spacing.lg }]}>2. VEHICLE DETAILS</Text>

                    <View style={styles.rowBox}>
                        <View style={styles.halfCol}>
                            <Text style={[styles.label, { color: themeColors.textSecondary }]}>Make</Text>
                            <TextInput style={[styles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text }]} value={make} onChangeText={setMake} placeholder="e.g. Toyota" placeholderTextColor={themeColors.textMuted} />
                        </View>
                        <View style={styles.halfCol}>
                            <Text style={[styles.label, { color: themeColors.textSecondary }]}>Model</Text>
                            <TextInput style={[styles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text }]} value={model} onChangeText={setModel} placeholder="e.g. Hilux" placeholderTextColor={themeColors.textMuted} />
                        </View>
                    </View>

                    <View style={styles.rowBox}>
                        <View style={styles.halfCol}>
                            <Text style={[styles.label, { color: themeColors.textSecondary }]}>Type</Text>
                            <TextInput style={[styles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text }]} value={vehicleType} onChangeText={setVehicleType} placeholder="e.g. Pickup" placeholderTextColor={themeColors.textMuted} />
                        </View>
                        <View style={[styles.halfCol, { flex: 0.5 }]}>
                            <Text style={[styles.label, { color: themeColors.textSecondary }]}>Qty</Text>
                            <TextInput style={[styles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text }]} value={quantity} onChangeText={setQuantity} placeholder="1" keyboardType="numeric" placeholderTextColor={themeColors.textMuted} />
                        </View>
                    </View>

                    <Text style={[styles.label, { color: themeColors.textSecondary }]}>Estimated Amount (₦) - Optional</Text>
                    <TextInput style={[styles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text, marginBottom: spacing.lg }]} value={amount} onChangeText={setAmount} placeholder="e.g. 15000000" keyboardType="numeric" placeholderTextColor={themeColors.textMuted} />

                    <Text style={[styles.label, { color: themeColors.textSecondary }]}>Justification / Notes</Text>
                    <TextInput style={[styles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text, minHeight: 100, textAlignVertical: 'top' }]} value={notes} onChangeText={setNotes} placeholder="Reason for this request..." multiline placeholderTextColor={themeColors.textMuted} />

                    {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

                    <TouchableOpacity
                        style={[styles.submitBtn, { backgroundColor: themeColors.primary }, submitting && { opacity: 0.7 }]}
                        onPress={handleSubmit}
                        disabled={submitting}
                    >
                        {submitting ? <ActivityIndicator color="#fff" /> : <Text style={styles.submitBtnText}>Submit Request</Text>}
                    </TouchableOpacity>

                </ScrollView>
            </KeyboardAvoidingView>
        </SafeAreaView>
    );
}

const styles = StyleSheet.create({
    safeArea: { flex: 1 },
    flex: { flex: 1 },
    container: { padding: spacing.xl, paddingBottom: 80 },

    sectionLabel: { fontSize: 13, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginBottom: spacing.md },

    typeGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 12, marginBottom: spacing.sm },
    typeCard: { width: '48%', borderRadius: 12, padding: 12, borderWidth: 1, height: 110 },
    typeLabel: { fontSize: 13, fontWeight: fontWeights.bold, marginTop: 8, marginBottom: 4 },
    typeDesc: { fontSize: 10, lineHeight: 14 },

    rowBox: { flexDirection: 'row', gap: 12, marginBottom: spacing.md },
    halfCol: { flex: 1 },
    label: { fontSize: 13, fontWeight: fontWeights.medium, marginBottom: 6 },
    input: { borderWidth: 1, borderRadius: 12, paddingHorizontal: 16, paddingVertical: 14, fontSize: 15 },

    error: { fontSize: 13, paddingVertical: spacing.md, textAlign: 'center' },

    submitBtn: { borderRadius: 12, height: 54, justifyContent: 'center', alignItems: 'center', marginTop: spacing.md },
    submitBtnText: { color: '#ffffff', fontSize: 16, fontWeight: fontWeights.bold },
});
