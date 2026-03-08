import React, { useState, useCallback, useEffect } from 'react';
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
  SafeAreaView,
  Platform,
  StatusBar,
  useColorScheme,
} from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import type { NativeStackNavigationProp } from '@react-navigation/native-stack';
import * as ImagePicker from 'expo-image-picker';
import * as LocalAuthentication from 'expo-local-authentication';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { logout, refreshUser } from '../../store/authSlice';
import { authStorage } from '../../store/authStorage';
import { officerApi } from '../../api/officerApi';
import { API_BASE_URL } from '../../utils/constants';
import type { ProfileStackParamList } from '../../navigation/ProfileStack';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Nav = NativeStackNavigationProp<ProfileStackParamList, 'Profile'>;

function InfoRow({ label, value, themeColors }: { label: string; value: string; themeColors: any }) {
  return (
    <View style={infoRowStyles.row}>
      <Text style={[infoRowStyles.label, { color: themeColors.textMuted }]}>{label}</Text>
      <Text style={[infoRowStyles.value, { color: themeColors.text }]} numberOfLines={1}>{value || '—'}</Text>
    </View>
  );
}
const infoRowStyles = StyleSheet.create({
  row: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 10 },
  label: { fontSize: 13, flex: 1 },
  value: { fontSize: 13, fontWeight: '500', flex: 1, textAlign: 'right' },
});

function MenuRow({ icon, label, subtitle, onPress, themeColors, danger = false }: any) {
  return (
    <TouchableOpacity
      style={[menuStyles.row, { borderBottomColor: themeColors.border }]}
      onPress={onPress}
      activeOpacity={0.7}
    >
      <View style={[menuStyles.iconWrap, { backgroundColor: danger ? '#fff1f2' : themeColors.surfaceTertiary }]}>
        <Ionicons name={icon} size={20} color={danger ? '#e11d48' : themeColors.primary} />
      </View>
      <View style={menuStyles.info}>
        <Text style={[menuStyles.label, { color: danger ? '#e11d48' : themeColors.text }]}>{label}</Text>
        {subtitle ? <Text style={[menuStyles.sub, { color: themeColors.textMuted }]}>{subtitle}</Text> : null}
      </View>
      {!danger && <Ionicons name="chevron-forward" size={18} color={themeColors.textMuted} />}
    </TouchableOpacity>
  );
}
const menuStyles = StyleSheet.create({
  row: { flexDirection: 'row', alignItems: 'center', paddingVertical: 14, borderBottomWidth: StyleSheet.hairlineWidth },
  iconWrap: { width: 38, height: 38, borderRadius: 19, justifyContent: 'center', alignItems: 'center', marginRight: 14 },
  info: { flex: 1 },
  label: { fontSize: 15, fontWeight: '500' },
  sub: { fontSize: 12, marginTop: 1 },
});

export function ProfileScreen() {
  const dispatch = useAppDispatch();
  const navigation = useNavigation<Nav>();
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const { user } = useAppSelector((s) => s.auth);

  const name = user?.officer?.name ?? user?.email ?? '—';
  const serviceNumber = user?.officer?.service_number ?? '—';
  const rank = user?.officer?.rank ?? '—';
  const command = user?.officer?.command?.name ?? '—';
  const phone = user?.officer?.phone_number ?? '—';
  const bankName = user?.officer?.bank_name ?? '—';
  const bankAcct = user?.officer?.bank_account_number ?? '—';
  const pfaName = user?.officer?.pfa_name ?? '—';
  const rsaNumber = user?.officer?.rsa_number ?? '—';

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

  // Refresh user data on focus
  useFocusEffect(
    useCallback(() => {
      dispatch(refreshUser());
    }, [dispatch])
  );

  const handleUpdatePhoto = async () => {
    const officerId = user?.officer?.id;
    if (officerId == null) return;

    Alert.alert('Update Photo', 'Choose a source', [
      {
        text: 'Camera', onPress: async () => {
          const { status } = await ImagePicker.requestCameraPermissionsAsync();
          if (status !== 'granted') { Alert.alert('Permission needed', 'Allow camera access to take a photo.'); return; }
          const result = await ImagePicker.launchCameraAsync({ allowsEditing: true, aspect: [1, 1], quality: 0.8 });
          if (result.canceled || !result.assets?.[0]?.uri) return;
          await uploadPhoto(officerId, result.assets[0].uri);
        }
      },
      {
        text: 'Gallery', onPress: async () => {
          const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
          if (status !== 'granted') { Alert.alert('Permission needed', 'Allow gallery access.'); return; }
          const result = await ImagePicker.launchImageLibraryAsync({ allowsEditing: true, aspect: [1, 1], quality: 0.8 });
          if (result.canceled || !result.assets?.[0]?.uri) return;
          await uploadPhoto(officerId, result.assets[0].uri);
        }
      },
      { text: 'Cancel', style: 'cancel' },
    ]);
  };

  const uploadPhoto = async (officerId: number, uri: string) => {
    const fileName = uri.split('/').pop() ?? 'profile.jpg';
    const formData = new FormData();
    formData.append('profile_picture', { uri, name: fileName, type: 'image/jpeg' } as unknown as Blob);
    setUploading(true);
    try {
      await officerApi.updateProfilePicture(officerId, formData);
      await dispatch(refreshUser()).unwrap();
    } catch {
      Alert.alert('Error', 'Failed to update profile picture. Please try again.');
    } finally {
      setUploading(false);
    }
  };

  const handleLogout = () => {
    Alert.alert('Sign out', 'Are you sure you want to sign out?', [
      { text: 'Cancel', style: 'cancel' },
      { text: 'Sign out', style: 'destructive', onPress: () => dispatch(logout()) },
    ]);
  };

  const initials = name.split(' ').map((w: string) => w[0]).join('').slice(0, 2).toUpperCase();

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

      <ScrollView contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>

        {/* ── Hero Header ── */}
        <View style={[styles.heroCard, { backgroundColor: themeColors.primary }]}>
          <TouchableOpacity style={styles.avatarWrap} onPress={handleUpdatePhoto} disabled={uploading}>
            {avatarUri ? (
              <Image source={{ uri: avatarUri }} style={styles.avatarImage} />
            ) : (
              <View style={[styles.avatarFallback, { backgroundColor: 'rgba(255,255,255,0.25)' }]}>
                <Text style={styles.avatarInitials}>{initials}</Text>
              </View>
            )}
            <View style={styles.cameraOverlay}>
              {uploading
                ? <ActivityIndicator color="#fff" size="small" />
                : <Ionicons name="camera" size={16} color="#fff" />
              }
            </View>
          </TouchableOpacity>

          <Text style={styles.heroName}>{rank !== '—' ? `${rank} ${name}` : name}</Text>
          <Text style={styles.heroSvc}>{serviceNumber} · {command}</Text>

          {/* Active badge */}
          <View style={styles.activeBadge}>
            <View style={styles.activeDot} />
            <Text style={styles.activeBadgeText}>Active</Text>
          </View>
        </View>

        {/* ── Service Quick Stats ── */}
        <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>SERVICE OVERVIEW</Text>
          <View style={styles.statsRow}>
            <View style={styles.statBox}>
              <Text style={[styles.statValue, { color: themeColors.primary }]}>{rank}</Text>
              <Text style={[styles.statKey, { color: themeColors.textMuted }]}>Rank</Text>
            </View>
            <View style={[styles.statDivider, { backgroundColor: themeColors.border }]} />
            <View style={styles.statBox}>
              <Text style={[styles.statValue, { color: themeColors.primary }]}>{command}</Text>
              <Text style={[styles.statKey, { color: themeColors.textMuted }]}>Command</Text>
            </View>
          </View>
        </View>

        {/* ── Contact Info ── */}
        <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          <View style={styles.cardHeader}>
            <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>CONTACT</Text>
            <TouchableOpacity
              onPress={() => { const id = user?.officer?.id; if (id) navigation.navigate('EditContact', { officerId: id }); }}
              hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
            >
              <Text style={[styles.editLink, { color: themeColors.primary }]}>Edit</Text>
            </TouchableOpacity>
          </View>
          <InfoRow label="Phone" value={phone} themeColors={themeColors} />
          <InfoRow label="Email" value={user?.email ?? '—'} themeColors={themeColors} />
        </View>

        {/* ── Banking Details ── */}
        <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          <View style={styles.cardHeader}>
            <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>BANKING & PENSION</Text>
            <TouchableOpacity
              onPress={() => { const id = user?.officer?.id; if (id) navigation.navigate('EditBanking', { officerId: id }); }}
              hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }}
            >
              <Text style={[styles.editLink, { color: themeColors.primary }]}>Edit</Text>
            </TouchableOpacity>
          </View>
          <InfoRow label="Bank" value={bankName} themeColors={themeColors} />
          <InfoRow label="Account No." value={bankAcct} themeColors={themeColors} />
          <InfoRow label="PFA" value={pfaName} themeColors={themeColors} />
          <InfoRow label="RSA Number" value={rsaNumber} themeColors={themeColors} />
        </View>

        {/* ── Actions Menu ── */}
        <View style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
          <Text style={[styles.cardLabel, { color: themeColors.textMuted }]}>SETTINGS</Text>

          {biometricAvailable && (
            <View style={[menuStyles.row, { borderBottomColor: themeColors.border }]}>
              <View style={[menuStyles.iconWrap, { backgroundColor: themeColors.surfaceTertiary }]}>
                <Ionicons name="finger-print" size={20} color={themeColors.primary} />
              </View>
              <View style={menuStyles.info}>
                <Text style={[menuStyles.label, { color: themeColors.text }]}>Biometric Login</Text>
                <Text style={[menuStyles.sub, { color: themeColors.textMuted }]}>Face ID / Fingerprint</Text>
              </View>
              <Switch
                value={biometricEnabled}
                onValueChange={async (v) => {
                  await authStorage.setBiometricEnabled(v);
                  setBiometricEnabled(v);
                }}
                trackColor={{ false: themeColors.border, true: themeColors.primaryLight }}
                thumbColor={biometricEnabled ? themeColors.primary : themeColors.textMuted}
              />
            </View>
          )}

          <MenuRow
            icon="notifications-outline"
            label="Notification Preferences"
            subtitle="Manage which alerts you receive"
            onPress={() => navigation.navigate('NotificationSettings' as any)}
            themeColors={themeColors}
          />

          <MenuRow
            icon="log-out-outline"
            label="Sign Out"
            subtitle={`Signed in as ${user?.email ?? '—'}`}
            onPress={handleLogout}
            themeColors={themeColors}
            danger
          />
        </View>

        <Text style={[styles.appVersion, { color: themeColors.textMuted }]}>NCS Employee · v1.0.0</Text>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  container: { padding: spacing.xl, paddingBottom: 60 },

  heroCard: {
    borderRadius: 20,
    alignItems: 'center',
    paddingVertical: spacing['2xl'],
    paddingHorizontal: spacing.xl,
    marginBottom: spacing.xl,
  },
  avatarWrap: { position: 'relative', marginBottom: spacing.md },
  avatarImage: { width: 88, height: 88, borderRadius: 44, borderWidth: 3, borderColor: 'rgba(255,255,255,0.5)' },
  avatarFallback: { width: 88, height: 88, borderRadius: 44, justifyContent: 'center', alignItems: 'center' },
  avatarInitials: { fontSize: 32, fontWeight: fontWeights.bold, color: '#ffffff' },
  cameraOverlay: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: 'rgba(0,0,0,0.5)',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#ffffff',
  },
  heroName: { fontSize: 20, fontWeight: fontWeights.bold, color: '#ffffff', marginBottom: 4 },
  heroSvc: { fontSize: 13, color: 'rgba(255,255,255,0.8)', marginBottom: spacing.md },
  activeBadge: { flexDirection: 'row', alignItems: 'center', backgroundColor: 'rgba(255,255,255,0.2)', paddingHorizontal: 12, paddingVertical: 4, borderRadius: 20, gap: 6 },
  activeDot: { width: 7, height: 7, borderRadius: 3.5, backgroundColor: '#4ade80' },
  activeBadgeText: { fontSize: 12, fontWeight: fontWeights.semibold, color: '#ffffff' },

  card: { borderRadius: 16, borderWidth: 1, padding: spacing.lg, marginBottom: spacing.lg },
  cardHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: spacing.sm },
  cardLabel: { fontSize: 11, fontWeight: fontWeights.bold, letterSpacing: 1.1, textTransform: 'uppercase', marginBottom: spacing.sm },
  editLink: { fontSize: 13, fontWeight: fontWeights.semibold },

  statsRow: { flexDirection: 'row', alignItems: 'center' },
  statBox: { flex: 1, alignItems: 'center', paddingVertical: spacing.sm },
  statValue: { fontSize: 15, fontWeight: fontWeights.bold, marginBottom: 2 },
  statKey: { fontSize: 11 },
  statDivider: { width: 1, height: 40 },

  appVersion: { textAlign: 'center', fontSize: 12, marginTop: spacing.xl },
});
