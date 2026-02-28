import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  TouchableOpacity,
  StyleSheet,
  ScrollView,
  Image,
  Alert,
  ActivityIndicator,
  Switch,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as ImagePicker from 'expo-image-picker';
import * as LocalAuthentication from 'expo-local-authentication';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { logout, refreshUser } from '../../store/authSlice';
import { authStorage } from '../../store/authStorage';
import { officerApi } from '../../api/officerApi';
import { API_BASE_URL } from '../../utils/constants';
import type { ProfileStackParamList } from '../../navigation/ProfileStack';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type Nav = NativeStackNavigationProp<ProfileStackParamList, 'Profile'>;

export function ProfileScreen() {
  const dispatch = useAppDispatch();
  const navigation = useNavigation<Nav>();
  const { user } = useAppSelector((s) => s.auth);
  const name = user?.officer?.name ?? user?.email ?? '—';
  const serviceNumber = user?.officer?.service_number ?? '—';
  const rank = user?.officer?.rank ?? '—';
  const command = user?.officer?.command?.name ?? '—';
  const phone = user?.officer?.phone_number ?? '—';
  const profilePictureUrl = user?.officer?.profile_picture_url;
  const avatarUri = profilePictureUrl
    ? (profilePictureUrl.startsWith('http') ? profilePictureUrl : `${API_BASE_URL.replace(/\/api\/v1\/?$/, '')}/storage/${profilePictureUrl}`)
    : null;
  const [uploading, setUploading] = useState(false);
  const [biometricEnabled, setBiometricEnabled] = useState(false);
  const [biometricAvailable, setBiometricAvailable] = useState(false);

  useEffect(() => {
    (async () => {
      const [enabled, hasHardware, isEnrolled] = await Promise.all([
        authStorage.getBiometricEnabled(),
        LocalAuthentication.hasHardwareAsync(),
        LocalAuthentication.isEnrolledAsync(),
      ]);
      setBiometricEnabled(enabled);
      setBiometricAvailable(hasHardware && isEnrolled);
    })();
  }, []);

  const handleEditContact = () => {
    const officerId = user?.officer?.id;
    if (officerId != null) navigation.navigate('EditContact', { officerId });
  };

  const handleUpdatePhoto = async () => {
    const officerId = user?.officer?.id;
    if (officerId == null) return;
    const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
    if (status !== 'granted') {
      Alert.alert('Permission needed', 'Allow access to photos to update your profile picture.');
      return;
    }
    const result = await ImagePicker.launchImageLibraryAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Images,
      allowsEditing: true,
      aspect: [1, 1],
      quality: 0.8,
    });
    if (result.canceled || !result.assets?.[0]?.uri) return;
    const uri = result.assets[0].uri;
    const fileName = uri.split('/').pop() ?? 'profile.jpg';
    const formData = new FormData();
    formData.append('profile_picture', { uri, name: fileName, type: 'image/jpeg' } as unknown as Blob);
    setUploading(true);
    try {
      await officerApi.updateProfilePicture(officerId, formData);
      await dispatch(refreshUser()).unwrap();
    } catch (e) {
      Alert.alert('Error', 'Failed to update profile picture. Please try again.');
    } finally {
      setUploading(false);
    }
  };

  return (
    <ScrollView
      style={styles.scroll}
      contentContainerStyle={styles.container}
      showsVerticalScrollIndicator={false}
    >
      <View style={styles.avatarWrap}>
        {avatarUri ? (
          <Image source={{ uri: avatarUri }} style={styles.avatarImage} />
        ) : (
          <View style={styles.avatar}>
            <Text style={styles.avatarText}>{name.charAt(0).toUpperCase()}</Text>
          </View>
        )}
        <TouchableOpacity
          style={styles.updatePhotoBtn}
          onPress={handleUpdatePhoto}
          disabled={uploading}
        >
          {uploading ? (
            <ActivityIndicator color={colors.textOnPrimary} size="small" />
          ) : (
            <Text style={styles.updatePhotoText}>Update photo</Text>
          )}
        </TouchableOpacity>
      </View>
      <Text style={styles.name}>{name}</Text>
      <Text style={styles.serviceNumber}>{serviceNumber}</Text>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Contact</Text>
        <View style={styles.row}>
          <Text style={styles.label}>Phone</Text>
          <Text style={styles.value}>{phone}</Text>
        </View>
        <TouchableOpacity style={styles.editContactBtn} onPress={handleEditContact}>
          <Text style={styles.editContactText}>Edit contact</Text>
        </TouchableOpacity>
      </View>

      <View style={styles.section}>
        <Text style={styles.sectionTitle}>Service details</Text>
        <View style={styles.row}>
          <Text style={styles.label}>Rank</Text>
          <Text style={styles.value}>{rank}</Text>
        </View>
        <View style={styles.row}>
          <Text style={styles.label}>Command</Text>
          <Text style={styles.value}>{command}</Text>
        </View>
      </View>

      {biometricAvailable && (
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Security</Text>
          <View style={styles.row}>
            <Text style={styles.label}>Use Face ID / Fingerprint for next login</Text>
            <Switch
              value={biometricEnabled}
              onValueChange={async (v) => {
                await authStorage.setBiometricEnabled(v);
                setBiometricEnabled(v);
              }}
              trackColor={{ false: colors.border, true: colors.primaryLight }}
              thumbColor={biometricEnabled ? colors.primary : colors.textMuted}
            />
          </View>
        </View>
      )}

      <TouchableOpacity
        style={styles.logoutButton}
        onPress={() => dispatch(logout())}
        activeOpacity={0.85}
      >
        <Text style={styles.logoutText}>Log out</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1, backgroundColor: colors.background },
  container: {
    padding: spacing.xl,
    paddingBottom: spacing['3xl'],
    alignItems: 'center',
  },
  avatarWrap: { marginBottom: spacing.base, alignItems: 'center' },
  avatar: {
    width: 80,
    height: 80,
    borderRadius: 40,
    backgroundColor: colors.primary,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarImage: { width: 80, height: 80, borderRadius: 40 },
  updatePhotoBtn: {
    marginTop: spacing.sm,
    paddingVertical: spacing.xs,
    paddingHorizontal: spacing.base,
  },
  updatePhotoText: { fontSize: fontSizes.sm, color: colors.primary, fontWeight: fontWeights.semibold },
  avatarText: {
    fontSize: fontSizes['3xl'],
    fontWeight: fontWeights.bold,
    color: colors.textOnPrimary,
  },
  name: {
    fontSize: fontSizes.xl,
    fontWeight: fontWeights.semibold,
    color: colors.text,
    marginBottom: spacing.xs,
  },
  serviceNumber: {
    fontSize: fontSizes.sm,
    color: colors.textSecondary,
    marginBottom: spacing.xl,
  },
  section: {
    width: '100%',
    backgroundColor: colors.surface,
    borderRadius: 16,
    padding: spacing.xl,
    borderWidth: 1,
    borderColor: colors.borderLight,
    marginBottom: spacing.xl,
  },
  sectionTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.textSecondary,
    marginBottom: spacing.base,
  },
  row: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: spacing.md,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: colors.borderLight,
  },
  label: { fontSize: fontSizes.sm, color: colors.textMuted },
  value: { fontSize: fontSizes.sm, fontWeight: fontWeights.medium, color: colors.text },
  editContactBtn: { marginTop: spacing.sm },
  editContactText: { fontSize: fontSizes.sm, color: colors.primary, fontWeight: fontWeights.semibold },
  logoutButton: {
    width: '100%',
    paddingVertical: spacing.base,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: colors.danger,
    borderRadius: 12,
    backgroundColor: colors.dangerLight,
  },
  logoutText: { color: colors.danger, fontSize: fontSizes.base, fontWeight: fontWeights.semibold },
});
