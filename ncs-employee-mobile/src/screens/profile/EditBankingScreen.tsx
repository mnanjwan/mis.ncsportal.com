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
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { officerApi } from '../../api/officerApi';
import { refreshUser } from '../../store/authSlice';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type RouteParams = { EditBanking: { officerId: number } };

function FormField({ label, value, onChangeText, placeholder, keyboardType, themeColors }: any) {
    return (
        <View style={{ marginBottom: spacing.lg }}>
            <Text style={[fieldStyles.label, { color: themeColors.textSecondary }]}>{label}</Text>
            <TextInput
                style={[fieldStyles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text }]}
                value={value}
                onChangeText={onChangeText}
                placeholder={placeholder}
                placeholderTextColor={themeColors.textMuted}
                keyboardType={keyboardType ?? 'default'}
                autoCapitalize="none"
            />
        </View>
    );
}
const fieldStyles = StyleSheet.create({
    label: { fontSize: 13, fontWeight: fontWeights.semibold, marginBottom: 6 },
    input: { borderWidth: 1, borderRadius: 12, paddingHorizontal: spacing.md, paddingVertical: 12, fontSize: 15 },
});

export function EditBankingScreen() {
    const navigation = useNavigation();
    const route = useRoute<RouteProp<RouteParams, 'EditBanking'>>();
    const officerId = route.params?.officerId;
    const dispatch = useAppDispatch();
    const themeColors = useThemeColor();
    const { user } = useAppSelector((s) => s.auth);

    const [bankName, setBankName] = useState(user?.officer?.bank_name ?? '');
    const [bankAcct, setBankAcct] = useState(user?.officer?.bank_account_number ?? '');
    const [sortCode, setSortCode] = useState(user?.officer?.sort_code ?? '');
    const [pfaName, setPfaName] = useState(user?.officer?.pfa_name ?? '');
    const [rsaNumber, setRsaNumber] = useState(user?.officer?.rsa_number ?? '');
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const handleSave = async () => {
        const id = officerId ?? user?.officer?.id;
        if (id == null) return;

        if (!bankName.trim() || !bankAcct.trim() || !pfaName.trim() || !rsaNumber.trim()) {
            Alert.alert('Required', 'Bank name, account number, PFA name, and RSA number are required.');
            return;
        }

        setError(null);
        setSaving(true);
        try {
            await officerApi.update(id, {
                bank_name: bankName.trim(),
                bank_account_number: bankAcct.trim(),
                sort_code: sortCode.trim() || undefined,
                pfa_name: pfaName.trim(),
                rsa_number: rsaNumber.trim(),
            });
            await dispatch(refreshUser()).unwrap();
            Alert.alert('Saved', 'Banking details updated. These will be pre-filled in future Emolument applications.', [
                { text: 'OK', onPress: () => navigation.goBack() },
            ]);
        } catch (e: unknown) {
            const msg = e && typeof e === 'object' && 'message' in e
                ? String((e as { message: string }).message)
                : 'Failed to save banking details';
            setError(msg);
        } finally {
            setSaving(false);
        }
    };

    return (
        <KeyboardAvoidingView
            style={[styles.flex, { backgroundColor: themeColors.background }]}
            behavior={Platform.OS === 'ios' ? 'padding' : undefined}
            keyboardVerticalOffset={100}
        >
            <ScrollView
                style={styles.flex}
                contentContainerStyle={styles.container}
                showsVerticalScrollIndicator={false}
            >
                {/* Security note */}
                <View style={[styles.noteCard, { backgroundColor: '#fffbeb', borderColor: '#fcd34d' }]}>
                    <Ionicons name="lock-closed" size={16} color="#d97706" />
                    <Text style={styles.noteText}>
                        This information is encrypted and used to pre-fill your Emolument document forms.
                    </Text>
                </View>

                <Text style={[styles.sectionLabel, { color: themeColors.textMuted }]}>BANK DETAILS</Text>
                <FormField label="Bank Name" value={bankName} onChangeText={setBankName} placeholder="e.g. First Bank" themeColors={themeColors} />
                <FormField label="Account Number" value={bankAcct} onChangeText={setBankAcct} placeholder="10-digit account number" keyboardType="numeric" themeColors={themeColors} />
                <FormField label="Sort Code (optional)" value={sortCode} onChangeText={setSortCode} placeholder="e.g. 011" themeColors={themeColors} />

                <Text style={[styles.sectionLabel, { color: themeColors.textMuted, marginTop: spacing.sm }]}>PENSION DETAILS</Text>
                <FormField label="PFA Name" value={pfaName} onChangeText={setPfaName} placeholder="e.g. ARM Pension" themeColors={themeColors} />
                <FormField label="RSA Number" value={rsaNumber} onChangeText={setRsaNumber} placeholder="e.g. PEN100012345678" themeColors={themeColors} />

                {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

                <TouchableOpacity
                    style={[styles.saveBtn, { backgroundColor: themeColors.primary }, saving && styles.saveBtnDisabled]}
                    onPress={handleSave}
                    disabled={saving}
                >
                    {saving
                        ? <ActivityIndicator color="#ffffff" />
                        : <Text style={styles.saveBtnText}>Save Banking Details</Text>
                    }
                </TouchableOpacity>
            </ScrollView>
        </KeyboardAvoidingView>
    );
}

const styles = StyleSheet.create({
    flex: { flex: 1 },
    container: { padding: spacing.xl, paddingBottom: 60 },
    noteCard: { flexDirection: 'row', alignItems: 'flex-start', gap: 8, borderRadius: 12, borderWidth: 1, padding: spacing.md, marginBottom: spacing.xl },
    noteText: { flex: 1, fontSize: 13, color: '#92400e', lineHeight: 18 },
    sectionLabel: { fontSize: 11, fontWeight: fontWeights.bold, letterSpacing: 1.1, marginBottom: spacing.md },
    error: { fontSize: 13, marginBottom: spacing.lg },
    saveBtn: { borderRadius: 12, height: 50, justifyContent: 'center', alignItems: 'center', marginTop: spacing.sm },
    saveBtnDisabled: { opacity: 0.6 },
    saveBtnText: { color: '#ffffff', fontSize: 16, fontWeight: fontWeights.bold },
});
