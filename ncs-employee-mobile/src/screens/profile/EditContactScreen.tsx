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
} from 'react-native';
import { useNavigation, useRoute, RouteProp } from '@react-navigation/native';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { officerApi } from '../../api/officerApi';
import { refreshUser } from '../../store/authSlice';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';

type RouteParams = { EditContact: { officerId: number } };

function FormField({ label, value, onChangeText, placeholder, keyboardType, multiline, themeColors }: any) {
  return (
    <View style={{ marginBottom: spacing.lg }}>
      <Text style={[fieldStyles.label, { color: themeColors.textSecondary }]}>{label}</Text>
      <TextInput
        style={[fieldStyles.input, { backgroundColor: themeColors.surface, borderColor: themeColors.border, color: themeColors.text }, multiline && { minHeight: 80, textAlignVertical: 'top' }]}
        value={value}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={themeColors.textMuted}
        keyboardType={keyboardType ?? 'default'}
        multiline={multiline}
        autoCapitalize="none"
      />
    </View>
  );
}
const fieldStyles = StyleSheet.create({
  label: { fontSize: 13, fontWeight: fontWeights.semibold, marginBottom: 6 },
  input: { borderWidth: 1, borderRadius: 12, paddingHorizontal: spacing.md, paddingVertical: 12, fontSize: 15 },
});

export function EditContactScreen() {
  const navigation = useNavigation();
  const route = useRoute<RouteProp<RouteParams, 'EditContact'>>();
  const officerId = route.params?.officerId;
  const dispatch = useAppDispatch();
  const themeColors = useThemeColor();
  const { user } = useAppSelector((s) => s.auth);

  const [phone, setPhone] = useState(user?.officer?.phone_number ?? '');
  const [email, setEmail] = useState(user?.officer?.personal_email ?? user?.email ?? '');
  const [address, setAddress] = useState(user?.officer?.residential_address ?? '');
  const [homeAddress, setHomeAddress] = useState(user?.officer?.permanent_home_address ?? '');
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleSave = async () => {
    const id = officerId ?? user?.officer?.id;
    if (id == null) return;
    setError(null);
    setSaving(true);
    try {
      await officerApi.update(id, {
        phone_number: phone.trim() || undefined,
        personal_email: email.trim() || undefined,
        residential_address: address.trim() || undefined,
        permanent_home_address: homeAddress.trim() || undefined,
      });
      await dispatch(refreshUser()).unwrap();
      navigation.goBack();
    } catch (e: unknown) {
      const msg = e && typeof e === 'object' && 'message' in e
        ? String((e as { message: string }).message)
        : 'Failed to save changes';
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
        <Text style={[styles.sectionNote, { color: themeColors.textMuted }]}>
          Update your contact information. These details are used across the app for correspondence and notifications.
        </Text>

        <FormField label="Phone Number" value={phone} onChangeText={setPhone} placeholder="e.g. 08012345678" keyboardType="phone-pad" themeColors={themeColors} />
        <FormField label="Personal Email" value={email} onChangeText={setEmail} placeholder="e.g. smith@email.com" keyboardType="email-address" themeColors={themeColors} />
        <FormField label="Residential Address" value={address} onChangeText={setAddress} placeholder="Current residential address" multiline themeColors={themeColors} />
        <FormField label="Permanent Home Address" value={homeAddress} onChangeText={setHomeAddress} placeholder="Permanent / home address" multiline themeColors={themeColors} />

        {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

        <TouchableOpacity
          style={[styles.saveBtn, { backgroundColor: themeColors.primary }, saving && styles.saveBtnDisabled]}
          onPress={handleSave}
          disabled={saving}
        >
          {saving
            ? <ActivityIndicator color="#ffffff" />
            : <Text style={styles.saveBtnText}>Save Changes</Text>
          }
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  container: { padding: spacing.xl, paddingBottom: 60 },
  sectionNote: { fontSize: 13, lineHeight: 19, marginBottom: spacing.xl },
  error: { fontSize: 13, marginBottom: spacing.lg },
  saveBtn: { borderRadius: 12, height: 50, justifyContent: 'center', alignItems: 'center' },
  saveBtnDisabled: { opacity: 0.6 },
  saveBtnText: { color: '#ffffff', fontSize: 16, fontWeight: fontWeights.bold },
});
