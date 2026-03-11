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
    Modal
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import * as DocumentPicker from 'expo-document-picker';
import { useAppSelector } from '../../hooks/redux';
import { fleetApi } from '../../api/fleetApi';
import type { FleetRequestType, FleetVehicle } from '../../api/fleetApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type RequestOption = { type: FleetRequestType; label: string; icon: string; desc: string };

const REQUEST_TYPES: RequestOption[] = [
    { type: 'FLEET_NEW_VEHICLE', label: 'New Vehicle', icon: 'car-sport', desc: 'Request an entirely new vehicle' },
    { type: 'FLEET_RE_ALLOCATION', label: 'Re-Allocation', icon: 'swap-horizontal', desc: 'Transfer a vehicle' },
    { type: 'FLEET_OPE', label: 'OPE Request', icon: 'cash', desc: 'Out of Pocket Expenses' },
    { type: 'FLEET_REPAIR', label: 'Repair', icon: 'build', desc: 'Maintenance or repair' },
    { type: 'FLEET_USE', label: 'Use Vehicle', icon: 'key', desc: 'Request use of a vehicle' },
    { type: 'FLEET_REQUISITION', label: 'Requisition', icon: 'construct', desc: 'Maintenance Requisition' },
];

const VEHICLE_TYPES = [
    { label: 'Sedan (Saloon)', value: 'SEDAN' },
    { label: 'SUV', value: 'SUV' },
    { label: 'Hatchback', value: 'HATCHBACK' },
    { label: 'Coupe', value: 'COUPE' },
    { label: 'Convertible', value: 'CONVERTIBLE' },
    { label: 'Pickup Truck', value: 'PICKUP' },
    { label: 'Van', value: 'VAN' },
    { label: 'Minivan (MPV)', value: 'MINIVAN' },
    { label: 'Bus', value: 'BUS' },
    { label: 'Truck (Lorry)', value: 'TRUCK' },
    { label: 'Wagon (Estate)', value: 'WAGON' },
    { label: 'Crossover (CUV)', value: 'CROSSOVER' },
    { label: 'Jeep', value: 'JEEP' },
    { label: 'Limousine', value: 'LIMOUSINE' },
    { label: 'Motorcycle', value: 'MOTORCYCLE' },
    { label: 'Scooter', value: 'SCOOTER' },
    { label: 'Electric Vehicle (EV)', value: 'EV' },
    { label: 'Hybrid Vehicle', value: 'HYBRID' },
];

export function CreateFleetRequestScreen() {
    const navigation = useNavigation();
    const themeColors = useThemeColor();
    const theme = useColorScheme() ?? 'light';

    const [requestType, setRequestType] = useState<FleetRequestType>('FLEET_NEW_VEHICLE');

    // Form fields
    const [vehicleType, setVehicleType] = useState('Sedan');
    const [vehicleTypeExpanded, setVehicleTypeExpanded] = useState(false);
    const [vehicleTypeSearchQuery, setVehicleTypeSearchQuery] = useState('');

    const [make, setMake] = useState('');
    const [model, setModel] = useState('');
    const [year, setYear] = useState('');
    const [quantity, setQuantity] = useState('1');
    const [amount, setAmount] = useState('');
    const [notes, setNotes] = useState('');
    const [document, setDocument] = useState<DocumentPicker.DocumentPickerAsset | null>(null);

    // Vehicle Select
    const [vehicles, setVehicles] = useState<FleetVehicle[]>([]);
    const [fleetVehicleId, setFleetVehicleId] = useState<number | null>(null);
    const [vehicleSelectExpanded, setVehicleSelectExpanded] = useState(false);
    const [vehicleSearchQuery, setVehicleSearchQuery] = useState('');

    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        const loadVehicles = async () => {
            try {
                const res = await fleetApi.commandVehicles();
                if (res.success && res.data) {
                    setVehicles(res.data);
                }
            } catch (e) {
                console.log('Failed to fetch command vehicles', e);
            }
        };
        loadVehicles();
    }, []);

    const pickDocument = async () => {
        try {
            const result = await DocumentPicker.getDocumentAsync({
                type: ['application/pdf', 'image/jpeg', 'image/png'],
            });
            if (!result.canceled && result.assets && result.assets.length > 0) {
                const asset = result.assets[0];
                if (asset.size && asset.size > 5 * 1024 * 1024) {
                    setError('Document size must not exceed 5MB');
                    return;
                }
                setDocument(asset);
                setError(null);
            }
        } catch (err) {
            Alert.alert('Error', 'Failed to pick document');
        }
    };

    const handleSubmit = async () => {
        if (!requestType) {
            Alert.alert('Validation Error', 'Please select a request type.');
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
                requested_quantity: Number(quantity) || undefined,
                amount: Number(amount) || undefined,
                fleet_vehicle_id: fleetVehicleId || undefined,
                notes: notes,
                document: document ? {
                    uri: document.uri,
                    name: document.name,
                    type: document.mimeType || 'application/pdf',
                } : undefined
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

    const showSpecs = requestType === 'FLEET_NEW_VEHICLE';
    const showVehicleSelect = ['FLEET_RE_ALLOCATION', 'FLEET_REPAIR', 'FLEET_USE', 'FLEET_OPE', 'FLEET_REQUISITION'].includes(requestType);
    const showAmount = ['FLEET_OPE', 'FLEET_REQUISITION'].includes(requestType);

    const filteredVehicles = vehicles.filter(v =>
        (v.make + ' ' + v.model + ' ' + v.reg_no).toLowerCase().includes(vehicleSearchQuery.toLowerCase())
    );

    const filteredVehicleTypes = VEHICLE_TYPES.filter(vt =>
        vt.label.toLowerCase().includes(vehicleTypeSearchQuery.toLowerCase())
    );

    return (
        <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
            <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled" showsVerticalScrollIndicator={false}>

                <Text style={styles.sectionLabel}>1. REQUEST TYPE</Text>
                <View style={styles.typeGrid}>
                    {REQUEST_TYPES.map((rt) => {
                        const selected = requestType === rt.type;
                        return (
                            <TouchableOpacity
                                key={rt.type}
                                style={[
                                    styles.typeCard,
                                    selected && { borderColor: themeColors.primary, backgroundColor: themeColors.primaryLight + '10' }
                                ]}
                                onPress={() => setRequestType(rt.type)}
                                activeOpacity={0.7}
                            >
                                <Ionicons name={rt.icon as any} size={20} color={selected ? themeColors.primary : '#94a3b8'} />
                                <Text style={[styles.typeLabel, { color: selected ? themeColors.primary : '#1e293b' }]} numberOfLines={1}>
                                    {rt.label}
                                </Text>
                                <Text style={styles.typeDesc} numberOfLines={2}>
                                    {rt.desc}
                                </Text>
                            </TouchableOpacity>
                        );
                    })}
                </View>

                {(showSpecs || showVehicleSelect || showAmount) && (
                    <Text style={[styles.sectionLabel, { marginTop: spacing.md }]}>2. VEHICLE DETAILS</Text>
                )}

                <View style={styles.card}>
                    {showSpecs && (
                        <>
                            <Text style={styles.label}>Vehicle Type</Text>
                            <TouchableOpacity
                                style={[styles.inputContainer, { justifyContent: 'space-between', paddingRight: spacing.md }]}
                                onPress={() => !submitting && setVehicleTypeExpanded(true)}
                                disabled={submitting}
                            >
                                <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                                    <Ionicons name="car-outline" size={18} color="#94a3b8" style={styles.inputIcon} />
                                    <Text style={{ fontSize: 14, color: vehicleType ? '#1e293b' : '#94a3b8' }}>
                                        {VEHICLE_TYPES.find(v => v.value === vehicleType)?.label || 'Select Type'}
                                    </Text>
                                </View>
                                <Ionicons name="chevron-down" size={18} color="#94a3b8" />
                            </TouchableOpacity>

                            <View style={styles.rowBox}>
                                <View style={styles.halfCol}>
                                    <Text style={styles.label}>Quantity</Text>
                                    <View style={styles.inputContainer}>
                                        <TextInput style={styles.input} value={quantity} onChangeText={setQuantity} placeholder="1" keyboardType="numeric" placeholderTextColor="#94a3b8" editable={!submitting} />
                                    </View>
                                </View>
                                <View style={styles.halfCol}>
                                    <Text style={styles.label}>Year (Optional)</Text>
                                    <View style={styles.inputContainer}>
                                        <TextInput style={styles.input} value={year} onChangeText={setYear} placeholder="e.g. 2024" keyboardType="numeric" placeholderTextColor="#94a3b8" editable={!submitting} />
                                    </View>
                                </View>
                            </View>

                            <View style={styles.rowBox}>
                                <View style={styles.halfCol}>
                                    <Text style={styles.label}>Make (Optional)</Text>
                                    <View style={styles.inputContainer}>
                                        <TextInput style={styles.input} value={make} onChangeText={setMake} placeholder="e.g. Toyota" placeholderTextColor="#94a3b8" editable={!submitting} />
                                    </View>
                                </View>
                                <View style={styles.halfCol}>
                                    <Text style={styles.label}>Model (Optional)</Text>
                                    <View style={styles.inputContainer}>
                                        <TextInput style={styles.input} value={model} onChangeText={setModel} placeholder="e.g. Hilux" placeholderTextColor="#94a3b8" editable={!submitting} />
                                    </View>
                                </View>
                            </View>
                        </>
                    )}

                    {showVehicleSelect && (
                        <>
                            <Text style={styles.label}>Select Vehicle</Text>
                            <TouchableOpacity
                                style={[styles.inputContainer, { justifyContent: 'space-between', paddingRight: spacing.md }]}
                                onPress={() => !submitting && setVehicleSelectExpanded(true)}
                                disabled={submitting}
                            >
                                <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1, marginRight: spacing.sm }}>
                                    <Ionicons name="car-outline" size={18} color="#94a3b8" style={styles.inputIcon} />
                                    <Text style={{ fontSize: 14, color: fleetVehicleId ? '#1e293b' : '#94a3b8' }} numberOfLines={1}>
                                        {fleetVehicleId ? vehicles.find(v => v.id === fleetVehicleId)?.reg_no || 'Selected' : 'Select vehicle...'}
                                    </Text>
                                </View>
                                <Ionicons name="chevron-down" size={18} color="#94a3b8" />
                            </TouchableOpacity>
                        </>
                    )}

                    {showAmount && (
                        <>
                            <Text style={styles.label}>Estimated Amount (₦)</Text>
                            <View style={styles.inputContainer}>
                                <TextInput style={styles.input} value={amount} onChangeText={setAmount} placeholder="e.g. 150000" keyboardType="numeric" placeholderTextColor="#94a3b8" editable={!submitting} />
                            </View>
                        </>
                    )}
                </View>

                <Text style={[styles.sectionLabel, { marginTop: spacing.md }]}>3. ADDITIONAL DETAILS</Text>

                <View style={styles.card}>
                    <Text style={styles.label}>Notes / Description</Text>
                    <View style={[styles.inputContainer, styles.textAreaContainer]}>
                        <TextInput style={[styles.input, styles.textArea]} value={notes} onChangeText={setNotes} placeholder="Reason for this request..." multiline textAlignVertical="top" placeholderTextColor="#94a3b8" editable={!submitting} />
                    </View>

                    <Text style={styles.label}>Supporting Documents (Optional)</Text>
                    <TouchableOpacity
                        style={[styles.inputContainer, { justifyContent: 'space-between', paddingRight: spacing.md }]}
                        onPress={pickDocument}
                        disabled={submitting}
                    >
                        <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1, marginRight: spacing.sm }}>
                            <Ionicons name="document-attach-outline" size={18} color={themeColors.primary} style={styles.inputIcon} />
                            <Text style={{ fontSize: 14, color: document ? '#1e293b' : themeColors.primary, flexShrink: 1, fontWeight: document ? '400' : '500' }} numberOfLines={1}>
                                {document ? document.name : 'Upload PDF or Image (Max 5MB)'}
                            </Text>
                        </View>
                        {document ? (
                            <TouchableOpacity onPress={() => setDocument(null)}>
                                <Ionicons name="close-circle" size={18} color="#ef4444" />
                            </TouchableOpacity>
                        ) : (
                            <Ionicons name="cloud-upload-outline" size={18} color={themeColors.primary} />
                        )}
                    </TouchableOpacity>

                    {error ? (
                        <View style={styles.errorBox}>
                            <Ionicons name="warning" size={16} color="#ef4444" />
                            <Text style={styles.errorText}>{error}</Text>
                        </View>
                    ) : null}

                    <TouchableOpacity style={[styles.button, { backgroundColor: themeColors.primary }, submitting && styles.buttonDisabled]} onPress={handleSubmit} disabled={submitting}>
                        {submitting ? <ActivityIndicator color="#ffffff" /> : <Text style={styles.buttonText}>Submit Request</Text>}
                    </TouchableOpacity>
                </View>

            </ScrollView>

            {/* Vehicle Type Modal */}
            <Modal visible={vehicleTypeExpanded} transparent animationType="fade">
                <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setVehicleTypeExpanded(false)}>
                    <SafeAreaView>
                        <View style={styles.sheetContent}>
                            <Text style={styles.sheetTitle}>Select Vehicle Type</Text>

                            <View style={[styles.inputContainer, { marginBottom: spacing.md, height: 44, backgroundColor: '#f1f5f9' }]}>
                                <Ionicons name="search" size={18} color="#94a3b8" style={styles.inputIcon} />
                                <TextInput
                                    style={{ flex: 1, fontSize: 14, color: '#1e293b' }}
                                    placeholder="Search vehicle type..."
                                    placeholderTextColor="#94a3b8"
                                    value={vehicleTypeSearchQuery}
                                    onChangeText={setVehicleTypeSearchQuery}
                                    autoCapitalize="none"
                                />
                            </View>

                            <ScrollView style={{ maxHeight: 300 }} showsVerticalScrollIndicator={false}>
                                {filteredVehicleTypes.map(vt => (
                                    <TouchableOpacity
                                        key={vt.value}
                                        style={styles.sheetOpt}
                                        onPress={() => {
                                            setVehicleType(vt.value);
                                            setVehicleTypeExpanded(false);
                                            setVehicleTypeSearchQuery('');
                                        }}
                                    >
                                        <Text style={[styles.sheetOptText, vehicleType === vt.value && { color: themeColors.primary, fontWeight: '700' }]}>{vt.label}</Text>
                                        {vehicleType === vt.value && <Ionicons name="checkmark" size={18} color={themeColors.primary} />}
                                    </TouchableOpacity>
                                ))}
                                {filteredVehicleTypes.length === 0 && (
                                    <Text style={{ padding: 12, color: '#94a3b8', textAlign: 'center' }}>No vehicle types found.</Text>
                                )}
                            </ScrollView>
                        </View>
                    </SafeAreaView>
                </TouchableOpacity>
            </Modal>

            {/* Specific Vehicle Select Modal */}
            <Modal visible={vehicleSelectExpanded} transparent animationType="fade">
                <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setVehicleSelectExpanded(false)}>
                    <SafeAreaView>
                        <View style={styles.sheetContent}>
                            <Text style={styles.sheetTitle}>Select Vehicle</Text>

                            <View style={[styles.inputContainer, { marginBottom: spacing.md, height: 44, backgroundColor: '#f1f5f9' }]}>
                                <Ionicons name="search" size={18} color="#94a3b8" style={styles.inputIcon} />
                                <TextInput
                                    style={{ flex: 1, fontSize: 14, color: '#1e293b' }}
                                    placeholder="Search by make, model, reg no..."
                                    placeholderTextColor="#94a3b8"
                                    value={vehicleSearchQuery}
                                    onChangeText={setVehicleSearchQuery}
                                    autoCapitalize="none"
                                />
                            </View>

                            <ScrollView style={{ maxHeight: 300 }} showsVerticalScrollIndicator={false}>
                                {filteredVehicles.map(v => (
                                    <TouchableOpacity
                                        key={v.id}
                                        style={styles.sheetOpt}
                                        onPress={() => {
                                            setFleetVehicleId(v.id);
                                            setVehicleSelectExpanded(false);
                                            setVehicleSearchQuery('');
                                        }}
                                    >
                                        <Text style={[styles.sheetOptText, fleetVehicleId === v.id && { color: themeColors.primary, fontWeight: '700' }]}>
                                            {v.make} {v.model} ({v.reg_no})
                                        </Text>
                                        {fleetVehicleId === v.id && <Ionicons name="checkmark" size={18} color={themeColors.primary} />}
                                    </TouchableOpacity>
                                ))}
                                {vehicles.length === 0 && (
                                    <Text style={{ padding: 12, color: '#94a3b8', textAlign: 'center' }}>No vehicles found in your command.</Text>
                                )}
                            </ScrollView>
                        </View>
                    </SafeAreaView>
                </TouchableOpacity>
            </Modal>

        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#ffffff' },
    scrollContent: { padding: spacing.lg, paddingBottom: spacing['3xl'] },

    sectionLabel: { fontSize: 12, fontWeight: '700', color: '#94a3b8', letterSpacing: 1.1, marginBottom: spacing.sm, marginLeft: 4 },

    typeGrid: { flexDirection: 'row', flexWrap: 'wrap', gap: 10, marginBottom: spacing.md },
    typeCard: { width: '48%', borderRadius: 12, padding: 12, borderWidth: 1, borderColor: '#e2e8f0', backgroundColor: '#f8faf9', minHeight: 90, justifyContent: 'center' },
    typeLabel: { fontSize: 12, fontWeight: '700', marginTop: 6, marginBottom: 2 },
    typeDesc: { fontSize: 10, lineHeight: 14, color: '#64748b' },

    card: { backgroundColor: '#ffffff', borderRadius: 12, padding: 0, marginBottom: spacing.md },
    rowBox: { flexDirection: 'row', gap: 12 },
    halfCol: { flex: 1 },

    label: { fontSize: 12, fontWeight: '600', color: '#1e293b', marginBottom: 4, marginTop: spacing.md },

    inputContainer: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#f8faf9',
        borderWidth: 1,
        borderColor: '#e2e8f0',
        borderRadius: 8,
        paddingHorizontal: spacing.md,
        height: 44,
    },
    inputIcon: { marginRight: spacing.sm },
    input: { flex: 1, fontSize: 14, height: '100%', color: '#1e293b' },

    textAreaContainer: { height: 80, paddingVertical: spacing.sm, alignItems: 'flex-start' },
    textArea: { minHeight: 60, height: '100%' },

    errorBox: {
        flexDirection: 'row',
        alignItems: 'center',
        backgroundColor: '#fef2f2',
        padding: spacing.sm,
        borderRadius: 8,
        marginTop: spacing.md,
        gap: spacing.sm,
    },
    errorText: { color: '#ef4444', fontSize: 13, fontWeight: '500', flex: 1 },

    button: {
        borderRadius: 10,
        height: 44,
        justifyContent: 'center',
        alignItems: 'center',
        marginTop: spacing.lg,
        elevation: 2,
        shadowOffset: { width: 0, height: 3 },
        shadowOpacity: 0.2,
        shadowRadius: 6
    },
    buttonDisabled: { opacity: 0.7, elevation: 0 },
    buttonText: { color: '#ffffff', fontSize: 15, fontWeight: '700' },

    modalBg: { flex: 1, backgroundColor: 'rgba(0,0,0,0.4)', justifyContent: 'flex-end' },
    sheetContent: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24, padding: spacing.xl },
    sheetTitle: { fontSize: 18, fontWeight: '700', color: '#1e293b', marginBottom: spacing.lg, textAlign: 'center' },
    sheetOpt: { flexDirection: 'row', justifyContent: 'space-between', paddingVertical: spacing.md, borderBottomWidth: 1, borderBottomColor: '#f1f5f9' },
    sheetOptText: { fontSize: 15, color: '#334155', fontWeight: '500' },
});
