import React, { useState } from 'react';
import { View, Text, TextInput, TouchableOpacity, StyleSheet, ActivityIndicator, KeyboardAvoidingView, Platform, Alert, ScrollView, Modal } from 'react-native';
import { NativeStackScreenProps } from '@react-navigation/native-stack';
import { RequestStackParamList } from '../../navigation/RequestStack';
import { passApi } from '../../api/passApi';
import * as DocumentPicker from 'expo-document-picker';
import DateTimePicker from '@react-native-community/datetimepicker';
import { useAppSelector } from '../../hooks/redux';
import { useThemeColor, spacing } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Props = NativeStackScreenProps<RequestStackParamList, 'PassApply'>;

export function PassApplyScreen({ navigation, route }: Props) {
  const { user } = useAppSelector((s) => s.auth);
  const themeColors = useThemeColor();
  const officerId = user?.officer?.id;

  const [startDate, setStartDate] = useState('');
  const [endDate, setEndDate] = useState('');
  const [reason, setReason] = useState('');
  const [supportingDocuments, setSupportingDocuments] = useState<DocumentPicker.DocumentPickerAsset[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const [showStartPicker, setShowStartPicker] = useState(false);
  const [showEndPicker, setShowEndPicker] = useState(false);

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

  const validateDates = () => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    const start = new Date(startDate);
    const end = new Date(endDate);

    if (isNaN(start.getTime()) || isNaN(end.getTime()) || startDate.length !== 10 || endDate.length !== 10) {
      setError('Please complete the dates');
      return false;
    }

    if (start < today) {
      setError('Start date cannot be in the past');
      return false;
    }

    if (end < start) {
      setError('End date must be after start date');
      return false;
    }

    return true;
  };

  const submit = async () => {
    setError(null);
    if (!officerId) { setError('Officer profile not found'); return; }
    if (!startDate || !endDate) { setError('Start and end dates are required'); return; }

    if (!validateDates()) return;

    setLoading(true);
    try {
      let payload: any = { start_date: startDate, end_date: endDate };
      if (reason) payload.reason = reason;

      if (supportingDocuments.length > 0) {
        const formData = new FormData();
        Object.entries(payload).forEach(([key, value]) => formData.append(key, value as string));

        supportingDocuments.forEach((doc) => {
          const fileUri = Platform.OS === 'ios' ? doc.uri.replace('file://', '') : doc.uri;
          formData.append('supporting_documents[]', {
            uri: fileUri,
            name: doc.name,
            type: doc.mimeType || 'application/octet-stream',
          } as any);
        });

        payload = formData;
      }

      const res = await passApi.apply(officerId, payload);
      if (res.success && res.data) {
        Alert.alert(
          'Pass Requested',
          'Your pass application has been submitted successfully for approval.',
          [{ text: 'OK', onPress: () => navigation.goBack() }]
        );
      } else {
        setError(res.message ?? 'Submission failed');
      }
    } catch (e: unknown) {
      setError((e as { response?: { data?: { message?: string } } })?.response?.data?.message ?? 'Network error occurred');
    } finally {
      setLoading(false);
    }
  };

  const pickDocuments = async () => {
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/jpeg', 'application/pdf'],
        copyToCacheDirectory: false,
        multiple: true,
      });

      if (!result.canceled && result.assets) {
        const validAssets = result.assets.filter(a => !a.size || a.size <= 5 * 1024 * 1024);
        if (validAssets.length < result.assets.length) {
          Alert.alert('Size Limit', 'Some files were skipped because they exceed the 5MB limit.');
        }
        setSupportingDocuments(prev => [...prev, ...validAssets]);
        setError(null);
      }
    } catch (err) {
      setError('Failed to pick documents');
    }
  };

  const removeDocument = (index: number) => {
    setSupportingDocuments(prev => prev.filter((_, i) => i !== index));
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

        {/* Pass Policy Banner */}
        <View style={[styles.infoBanner, { backgroundColor: '#f0fdf4', borderLeftColor: themeColors.primary }]}>
          <Ionicons name="information-circle" size={24} color={themeColors.primary} />
          <View style={styles.infoTextContainer}>
            <Text style={[styles.infoTitle, { color: '#166534' }]}>Pass Policy Guidelines</Text>
            <Text style={[styles.infoDesc, { color: '#166534' }]}>A Pass is short-term absence permission. The maximum duration is based on your grade level (working days only; Saturdays and Sundays are not counted). Submit to see your limit or any validation message.</Text>
          </View>
        </View>

        <View style={styles.card}>

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

          <Text style={styles.label}>Reason (Optional)</Text>
          <View style={[styles.inputContainer, styles.textAreaContainer]}>
            <TextInput
              style={[styles.input, styles.textArea]}
              placeholder="Provide a brief reason for your pass..."
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
            style={[styles.inputContainer, { justifyContent: 'center' }]}
            onPress={pickDocuments}
            disabled={loading}
          >
            <Ionicons name="cloud-upload-outline" size={18} color={themeColors.primary} style={styles.inputIcon} />
            <Text style={{ fontSize: 14, color: themeColors.primary, fontWeight: '500' }}>
              Upload JPEG or PDF (Max 5MB)
            </Text>
          </TouchableOpacity>

          {supportingDocuments.length > 0 && (
            <View style={styles.documentList}>
              {supportingDocuments.map((doc, index) => (
                <View key={index} style={styles.documentItem}>
                  <Ionicons name="document-text-outline" size={20} color="#64748b" />
                  <Text style={styles.documentName} numberOfLines={1}>{doc.name}</Text>
                  <TouchableOpacity onPress={() => removeDocument(index)} disabled={loading}>
                    <Ionicons name="close-circle" size={20} color="#ef4444" />
                  </TouchableOpacity>
                </View>
              ))}
            </View>
          )}

          {error ? (
            <View style={styles.errorBox}>
              <Ionicons name="warning" size={16} color="#ef4444" />
              <Text style={styles.errorText}>{error}</Text>
            </View>
          ) : null}

          <TouchableOpacity
            style={[styles.button, { backgroundColor: themeColors.primary, shadowColor: themeColors.primary }, loading && styles.buttonDisabled]}
            onPress={submit}
            disabled={loading}
          >
            {loading ? <ActivityIndicator color="#ffffff" /> : <Text style={styles.buttonText}>Submit Pass Request</Text>}
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#ffffff' },
  scrollContent: { padding: spacing.lg, paddingBottom: spacing['3xl'] },

  infoBanner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    padding: spacing.md,
    borderRadius: 10,
    borderLeftWidth: 4,
    marginBottom: spacing.lg,
  },
  infoTextContainer: {
    marginLeft: spacing.sm,
    flex: 1,
  },
  infoTitle: {
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 2,
  },
  infoDesc: {
    fontSize: 12,
    lineHeight: 18,
  },

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

  documentList: { marginTop: spacing.sm, gap: spacing.xs },
  documentItem: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f1f5f9',
    padding: spacing.sm,
    borderRadius: 6,
  },
  documentName: { flex: 1, fontSize: 13, color: '#334155', marginHorizontal: spacing.sm },

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
  sheetContent: { backgroundColor: '#fff', borderTopLeftRadius: 24, borderTopRightRadius: 24 },
});
