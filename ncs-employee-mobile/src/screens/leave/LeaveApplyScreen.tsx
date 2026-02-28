import React, { useEffect, useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ActivityIndicator, KeyboardAvoidingView, Platform, Alert, ScrollView, Modal, SafeAreaView } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { leaveApi } from '../../api/leaveApi';
import type { LeaveTypeItem } from '../../api/types';
import * as DocumentPicker from 'expo-document-picker';
import DateTimePicker from '@react-native-community/datetimepicker';
import { useAppSelector } from '../../hooks/redux';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Props = NativeStackScreenProps<RequestStackParamList, 'LeaveApply'>;

export function LeaveApplyScreen({ navigation, route }: Props) {
  const { user } = useAppSelector((s) => s.auth);
  const themeColors = useThemeColor();
  const officerId = user?.officer?.id;
  const [leaveTypes, setLeaveTypes] = useState<LeaveTypeItem[]>([]);
  const [leaveTypeId, setLeaveTypeId] = useState<number | null>(null);

  const [showTypeDropdown, setShowTypeDropdown] = useState(false);

  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [reason, setReason] = useState('');
  const [expectedDateOfDelivery, setExpectedDateOfDelivery] = useState('');
  const [medicalCertificate, setMedicalCertificate] = useState<DocumentPicker.DocumentPickerAsset | null>(null);

  const [searchQuery, setSearchQuery] = useState('');

  const [loading, setLoading] = useState(false);
  const [loadTypes, setLoadTypes] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const [showStartPicker, setShowStartPicker] = useState(false);
  const [showEndPicker, setShowEndPicker] = useState(false);
  const [showEddPicker, setShowEddPicker] = useState(false);

  React.useLayoutEffect(() => {
    if (route.params?.fromDashboard) {
      navigation.setOptions({
        headerLeft: () => (
          <TouchableOpacity onPress={() => navigation.navigate('Home' as any)} style={{ paddingRight: 20, paddingVertical: 8, marginLeft: Platform.OS === 'ios' ? -15 : 0 }}>
            <Ionicons name="arrow-back" size={24} color="#ffffff" />
          </TouchableOpacity>
        ),
      });
    }
  }, [navigation, route.params]);

  useEffect(() => {
    leaveApi.getLeaveTypes().then((res) => {
      if (res.success && res.data) setLeaveTypes(res.data);
      setLoadTypes(false);
    }).catch(() => setLoadTypes(false));
  }, []);

  const submit = async () => {
    if (!officerId) { setError('Officer not found'); return; }
    if (leaveTypeId == null) { setError('Please select a leave type'); return; }
    if (!startDate || !endDate) { setError('Start and end dates are required'); return; }

    setError(null);
    setLoading(true);
    try {
      let payload: any = {
        leave_type_id: leaveTypeId,
        start_date: startDate,
        end_date: endDate,
      };
      if (reason) payload.reason = reason;
      if (expectedDateOfDelivery) payload.expected_date_of_delivery = expectedDateOfDelivery;

      if (medicalCertificate) {
        const formData = new FormData();
        Object.entries(payload).forEach(([key, value]) => formData.append(key, value as string));

        const fileUri = Platform.OS === 'ios' ? medicalCertificate.uri.replace('file://', '') : medicalCertificate.uri;

        formData.append('medical_certificate', {
          uri: fileUri,
          name: medicalCertificate.name,
          type: medicalCertificate.mimeType || 'application/octet-stream',
        } as any);

        payload = formData;
      }

      const res = await leaveApi.apply(officerId, payload);
      if (res.success && res.data) {
        Alert.alert('Leave Requested', 'Your leave application has been submitted successfully.', [{ text: 'OK', onPress: () => navigation.goBack() }]);
      } else {
        setError(res.message ?? 'Submission failed');
      }
    } catch (e: unknown) {
      setError((e as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Network error occurred');
    } finally {
      setLoading(false);
    }
  };

  if (loadTypes) return <View style={[styles.centered, { backgroundColor: '#ffffff' }]}><ActivityIndicator size="large" color={themeColors.primary} /></View>;

  const showMaternityDate = leaveTypes.find(t => t.id === leaveTypeId)?.name.toLowerCase().includes('maternity');
  const selectedLeaveName = leaveTypes.find(t => t.id === leaveTypeId)?.name || 'Select a leave type';

  const filteredLeaveTypes = leaveTypes.filter(t => t.name.toLowerCase().includes(searchQuery.toLowerCase()));

  const pickDocument = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/jpeg', 'application/pdf'],
        copyToCacheDirectory: false,
      });

      if (!result.canceled && result.assets && result.assets.length > 0) {
        const asset = result.assets[0];
        if (asset.size && asset.size > 5 * 1024 * 1024) {
          setError('Document size must not exceed 5MB');
          return;
        }
        setMedicalCertificate(asset);
        setError(null);
      }
    } catch (err) {
      setError('Failed to pick document');
    }
  };

  const renderDatePicker = (value: string, setValue: (d: string) => void, show: boolean, setShow: (b: boolean) => void) => {
    if (!show) return null;
    const dateObj = value ? new Date(value) : new Date();

    const onChange = (event: any, selectedDate?: Date) => {
      if (Platform.OS === 'android') setShow(false);
      if (selectedDate && event.type !== 'dismissed') {
        const formatted = selectedDate.getFullYear() + '-' +
          String(selectedDate.getMonth() + 1).padStart(2, '0') + '-' +
          String(selectedDate.getDate()).padStart(2, '0');
        setValue(formatted);
      }
    };

    if (Platform.OS === 'ios') {
      return (
        <Modal visible={true} transparent animationType="fade">
          <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setShow(false)}>
            <View style={[styles.sheetContent, { padding: 0 }]} onStartShouldSetResponder={() => true}>
              <View style={{ flexDirection: 'row', justifyContent: 'flex-end', padding: 16, backgroundColor: '#f8faf9', borderTopLeftRadius: 24, borderTopRightRadius: 24 }}>
                <TouchableOpacity onPress={() => setShow(false)}>
                  <Text style={{ color: themeColors.primary, fontWeight: '700', fontSize: 16 }}>Done</Text>
                </TouchableOpacity>
              </View>
              <DateTimePicker
                value={dateObj}
                mode="date"
                display="spinner"
                onChange={onChange}
                style={{ height: 200, backgroundColor: '#fff' }}
              />
            </View>
          </TouchableOpacity>
        </Modal>
      );
    }

    return (
      <DateTimePicker
        value={dateObj}
        mode="date"
        display="default"
        onChange={onChange}
      />
    );
  };

  return (
    <KeyboardAvoidingView style={styles.container} behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView contentContainerStyle={styles.scrollContent} keyboardShouldPersistTaps="handled">

        {/* Header Info Banner */}
        <View style={[styles.infoBanner, { backgroundColor: '#f0fdf4', borderLeftColor: themeColors.primary }]}>
          <Ionicons name="information-circle" size={24} color={themeColors.primary} />
          <View style={styles.infoTextContainer}>
            <Text style={[styles.infoTitle, { color: '#166534' }]}>Leave Application</Text>
            <Text style={[styles.infoDesc, { color: '#166534' }]}>Select your leave type and specify the dates carefully. Approval is required before taking leave.</Text>
          </View>
        </View>

        <View style={styles.card}>
          <Text style={styles.label}>Leave Type</Text>
          <TouchableOpacity
            style={[styles.inputContainer, { justifyContent: 'space-between', paddingRight: spacing.md }]}
            onPress={() => !loading && setShowTypeDropdown(true)}
            disabled={loading}
          >
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
              <Ionicons name="documents-outline" size={18} color="#94a3b8" style={styles.inputIcon} />
              <Text style={{ fontSize: 14, color: leaveTypeId ? '#1e293b' : '#94a3b8' }}>
                {selectedLeaveName}
              </Text>
            </View>
            <Ionicons name="chevron-down" size={18} color="#94a3b8" />
          </TouchableOpacity>

          <Text style={styles.label}>Start Date</Text>
          <TouchableOpacity style={styles.inputContainer} onPress={() => !loading && setShowStartPicker(true)}>
            <Ionicons name="calendar-outline" size={18} color="#94a3b8" style={styles.inputIcon} />
            <Text style={[styles.inputText, !startDate && { color: '#94a3b8' }]}>{startDate || 'YYYY-MM-DD'}</Text>
          </TouchableOpacity>
          {renderDatePicker(startDate, setStartDate, showStartPicker, setShowStartPicker)}

          <Text style={styles.label}>End Date</Text>
          <TouchableOpacity style={styles.inputContainer} onPress={() => !loading && setShowEndPicker(true)}>
            <Ionicons name="calendar-outline" size={18} color="#94a3b8" style={styles.inputIcon} />
            <Text style={[styles.inputText, !endDate && { color: '#94a3b8' }]}>{endDate || 'YYYY-MM-DD'}</Text>
          </TouchableOpacity>
          {renderDatePicker(endDate, setEndDate, showEndPicker, setShowEndPicker)}

          {showMaternityDate && (
            <>
              <Text style={styles.label}>Expected Date of Delivery</Text>
              <TouchableOpacity style={styles.inputContainer} onPress={() => !loading && setShowEddPicker(true)}>
                <Ionicons name="calendar-outline" size={18} color="#94a3b8" style={styles.inputIcon} />
                <Text style={[styles.inputText, !expectedDateOfDelivery && { color: '#94a3b8' }]}>{expectedDateOfDelivery || 'YYYY-MM-DD'}</Text>
              </TouchableOpacity>
              {renderDatePicker(expectedDateOfDelivery, setExpectedDateOfDelivery, showEddPicker, setShowEddPicker)}
            </>
          )}

          <Text style={styles.label}>Reason (Optional)</Text>
          <View style={[styles.inputContainer, styles.textAreaContainer]}>
            <TextInput
              style={[styles.input, styles.textArea]}
              placeholder="Provide a reason for your leave..."
              placeholderTextColor="#94a3b8"
              value={reason}
              onChangeText={setReason}
              multiline
              textAlignVertical="top"
              editable={!loading}
            />
          </View>

          <Text style={styles.label}>Supporting Documents (Optional)</Text>
          <TouchableOpacity
            style={[styles.inputContainer, { justifyContent: 'space-between', paddingRight: spacing.md }]}
            onPress={pickDocument}
            disabled={loading}
          >
            <View style={{ flexDirection: 'row', alignItems: 'center', flex: 1, marginRight: spacing.sm }}>
              <Ionicons name="document-attach-outline" size={18} color={themeColors.primary} style={styles.inputIcon} />
              <Text style={{ fontSize: 14, color: medicalCertificate ? '#1e293b' : themeColors.primary, flexShrink: 1, fontWeight: medicalCertificate ? '400' : '500' }} numberOfLines={1}>
                {medicalCertificate ? medicalCertificate.name : 'Upload JPEG or PDF (Max 5MB)'}
              </Text>
            </View>
            {medicalCertificate ? (
              <TouchableOpacity onPress={() => setMedicalCertificate(null)}>
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

          <TouchableOpacity style={[styles.button, { backgroundColor: themeColors.primary, shadowColor: themeColors.primary }, loading && styles.buttonDisabled]} onPress={submit} disabled={loading}>
            {loading ? <ActivityIndicator color="#ffffff" /> : <Text style={styles.buttonText}>Submit Application</Text>}
          </TouchableOpacity>
        </View>
      </ScrollView>

      {/* Select Dropdown Modal */}
      <Modal visible={showTypeDropdown} transparent animationType="fade">
        <TouchableOpacity style={styles.modalBg} activeOpacity={1} onPress={() => setShowTypeDropdown(false)}>
          <SafeAreaView>
            <View style={styles.sheetContent}>
              <Text style={styles.sheetTitle}>Select Leave Type</Text>

              <View style={[styles.inputContainer, { marginBottom: spacing.md, height: 44, backgroundColor: '#f1f5f9' }]}>
                <Ionicons name="search" size={18} color="#94a3b8" style={styles.inputIcon} />
                <TextInput
                  style={{ flex: 1, fontSize: 14, color: '#1e293b' }}
                  placeholder="Search leave type..."
                  placeholderTextColor="#94a3b8"
                  value={searchQuery}
                  onChangeText={setSearchQuery}
                  autoCapitalize="none"
                />
              </View>

              <ScrollView style={{ maxHeight: 300 }} showsVerticalScrollIndicator={false}>
                {filteredLeaveTypes.map(t => (
                  <TouchableOpacity
                    key={t.id}
                    style={styles.sheetOpt}
                    onPress={() => {
                      setLeaveTypeId(t.id);
                      setShowTypeDropdown(false);
                      setSearchQuery('');
                    }}
                  >
                    <Text style={[styles.sheetOptText, leaveTypeId === t.id && { color: themeColors.primary, fontWeight: '700' }]}>{t.name}</Text>
                    {leaveTypeId === t.id && <Ionicons name="checkmark" size={18} color={themeColors.primary} />}
                  </TouchableOpacity>
                ))}
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
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center', backgroundColor: '#ffffff' },

  infoBanner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    padding: spacing.md,
    borderRadius: 10,
    borderLeftWidth: 4,
    marginBottom: spacing.lg,
  },
  infoTextContainer: { marginLeft: spacing.sm, flex: 1 },
  infoTitle: { fontSize: 14, fontWeight: '700', marginBottom: 2 },
  infoDesc: { fontSize: 12, lineHeight: 18 },

  card: { backgroundColor: '#ffffff', borderRadius: 12, padding: 0 },

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
  inputText: { flex: 1, fontSize: 14, color: '#1e293b' },

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
