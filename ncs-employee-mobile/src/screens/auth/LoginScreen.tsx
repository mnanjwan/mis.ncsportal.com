import React, { useState } from 'react';
import {
  View,
  Text,
  TextInput,
  TouchableOpacity,
  StyleSheet,
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  SafeAreaView,
  StatusBar,
  Dimensions,
  Image,
} from 'react-native';
import { useAppDispatch, useAppSelector } from '../../hooks/redux';
import { login, clearError } from '../../store/authSlice';
import { defaultColors as colors, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import { LinearGradient } from 'expo-linear-gradient';
import * as LocalAuthentication from 'expo-local-authentication';
import { authStorage } from '../../store/authStorage';

const { width } = Dimensions.get('window');

export function LoginScreen() {
  const dispatch = useAppDispatch();
  const { isLoading, error } = useAppSelector((s) => s.auth);
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [isPasswordVisible, setIsPasswordVisible] = useState(false);
  const [showTooltip, setShowTooltip] = useState(false);
  const [rememberMe, setRememberMe] = useState(true);
  const [hasBiometric, setHasBiometric] = useState(false);
  const [savedCreds, setSavedCreds] = useState<{ identifier?: string; password?: string } | null>(null);

  React.useEffect(() => {
    const checkBiometric = async () => {
      const hasHardware = await LocalAuthentication.hasHardwareAsync();
      const isEnrolled = await LocalAuthentication.isEnrolledAsync();
      setHasBiometric(hasHardware && isEnrolled);

      const credsRaw = await authStorage.getCredentials();
      if (credsRaw) {
        try {
          const parsed = JSON.parse(credsRaw);
          setSavedCreds(parsed);
        } catch { } // ignore
      }
    };
    checkBiometric();
  }, []);

  const handleBiometricLogin = async () => {
    if (!savedCreds?.identifier || !savedCreds?.password) return;
    try {
      const result = await LocalAuthentication.authenticateAsync({
        promptMessage: 'Sign in to NCS Employee',
        fallbackLabel: 'Use passode',
      });
      if (result.success) {
        dispatch(clearError());
        dispatch(login({
          identifier: savedCreds.identifier,
          password: savedCreds.password,
          rememberMe: true, // keep them saved
        }));
      }
    } catch {
      // ignore or show alert
    }
  };

  const handleLogin = () => {
    dispatch(clearError());
    if (!identifier.trim() || !password.trim()) return;
    dispatch(
      login({
        identifier: identifier.trim(),
        password: password.trim(),
        rememberMe,
      })
    );
  };

  return (
    <View style={styles.mainContainer}>
      <StatusBar barStyle="light-content" backgroundColor="#1a1a1a" />

      {/* Background Gradient matching web linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%) */}
      <LinearGradient
        colors={['#1a1a1a', '#2d2d2d', '#1a1a1a']}
        start={{ x: 0, y: 0 }}
        end={{ x: 1, y: 1 }}
        style={styles.backgroundGradient}
      />

      {/* Decorative Floating Shapes */}
      <View style={[styles.shape, styles.shape1]} />
      <View style={[styles.shape, styles.shape2]} />
      <View style={[styles.shape, styles.shape3]} />

      <SafeAreaView style={styles.safeArea}>
        <KeyboardAvoidingView
          style={styles.keyboardView}
          behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        >
          <View style={styles.container}>
            {/* Glassmorphism Card */}
            <View style={styles.glassCard}>
              {/* Logo Header */}
              <View style={styles.headerSection}>
                <View style={[styles.logoCircle, { backgroundColor: 'transparent', borderWidth: 0, shadowOpacity: 0 }]}>
                  <Image source={require('../../../assets/ncslogo.png')} style={{ width: 80, height: 80, resizeMode: 'contain' }} />
                </View>

                <View style={styles.titleRow}>
                  <Text style={styles.title}>Welcome Back</Text>
                  <TouchableOpacity
                    onPress={() => setShowTooltip(!showTooltip)}
                    style={styles.tooltipButton}
                    activeOpacity={0.7}
                  >
                    <Ionicons name="information-circle" size={24} color="#f59e0b" />
                  </TouchableOpacity>
                </View>

                {showTooltip && (
                  <View style={styles.tooltipContent}>
                    <Text style={styles.tooltipTitle}>Security Reminder</Text>
                    <Text style={styles.tooltipText}>• Never share your password.</Text>
                    <Text style={styles.tooltipText}>• Beware of phishing attempts.</Text>
                    <Text style={styles.tooltipText}>• Use trusted networks.</Text>
                  </View>
                )}

                <Text style={styles.subtitle}>Sign in to the NCS Employee Portal</Text>
              </View>

              {/* Error Alert */}
              {error ? (
                <View style={styles.errorAlert}>
                  <Ionicons name="information-circle" size={20} color={colors.danger} />
                  <Text style={styles.errorAlertText}>{error}</Text>
                </View>
              ) : null}

              {/* Form Section */}
              <View style={styles.formSection}>
                {/* Username */}
                <View style={styles.inputGroup}>
                  <Text style={styles.label}>Service Number or Email</Text>
                  <View style={styles.inputContainer}>
                    <Ionicons name="person" size={18} color="#9ca3af" style={styles.inputIcon} />
                    <TextInput
                      style={styles.input}
                      placeholder="e.g. NCS12345 or officer@ncs.gov.ng"
                      placeholderTextColor="#9ca3af"
                      value={identifier}
                      onChangeText={setIdentifier}
                      autoCapitalize="none"
                      keyboardType="email-address"
                      autoCorrect={false}
                      editable={!isLoading}
                    />
                  </View>
                </View>

                {/* Password */}
                <View style={styles.inputGroup}>
                  <View style={styles.labelRow}>
                    <Text style={styles.label}>Password</Text>
                    <TouchableOpacity activeOpacity={0.7}>
                      <Text style={styles.forgotText}>Forgot password?</Text>
                    </TouchableOpacity>
                  </View>
                  <View style={styles.inputContainer}>
                    <Ionicons name="lock-closed" size={18} color="#9ca3af" style={styles.inputIcon} />
                    <TextInput
                      style={styles.input}
                      placeholder="••••••••"
                      placeholderTextColor="#9ca3af"
                      value={password}
                      onChangeText={setPassword}
                      secureTextEntry={!isPasswordVisible}
                      editable={!isLoading}
                    />
                    <TouchableOpacity
                      onPress={() => setIsPasswordVisible(!isPasswordVisible)}
                      style={styles.eyeBtn}
                    >
                      <Ionicons
                        name={isPasswordVisible ? 'eye-off' : 'eye'}
                        size={20}
                        color="#9ca3af"
                      />
                    </TouchableOpacity>
                  </View>
                </View>

                {/* Keep me signed in */}
                <TouchableOpacity
                  style={styles.checkboxRow}
                  onPress={() => setRememberMe(!rememberMe)}
                  activeOpacity={0.8}
                >
                  <View style={[styles.checkbox, !rememberMe && { backgroundColor: 'transparent', borderWidth: 1.5, borderColor: '#d1d5db' }]}>
                    {rememberMe && <Ionicons name="checkmark" size={14} color={colors.surface} />}
                  </View>
                  <Text style={styles.checkboxLabel}>Keep me signed in</Text>
                </TouchableOpacity>

                {/* Submit Button */}
                <TouchableOpacity
                  style={[styles.button, isLoading && styles.buttonDisabled]}
                  onPress={handleLogin}
                  disabled={isLoading}
                  activeOpacity={0.8}
                >
                  {isLoading ? (
                    <ActivityIndicator color={colors.textOnPrimary} />
                  ) : (
                    <>
                      <Text style={styles.buttonText}>Sign In</Text>
                      <Ionicons name="arrow-forward" size={20} color={colors.surface} />
                    </>
                  )}
                </TouchableOpacity>

                {/* Biometric Sign In Option */}
                {hasBiometric && savedCreds?.identifier && (
                  <TouchableOpacity
                    style={styles.biometricBtn}
                    onPress={handleBiometricLogin}
                    disabled={isLoading}
                    activeOpacity={0.7}
                  >
                    <Ionicons name="finger-print" size={24} color={colors.primary} />
                    <Text style={styles.biometricBtnText}>Sign in with Biometrics</Text>
                  </TouchableOpacity>
                )}
              </View>

              {/* Secure Portal Badge */}
              <View style={styles.secureBadgeWrapper}>
                <View style={styles.secureBadge}>
                  <Ionicons name="shield-checkmark" size={16} color={colors.primary} />
                  <Text style={styles.secureBadgeText}>Secure Official Portal</Text>
                </View>
              </View>
            </View>

            {/* Bottom Copyright */}
            <View style={styles.footer}>
              <Text style={styles.copyrightText}>© 2025 Nigeria Customs Service. All rights reserved.</Text>
              <Text style={styles.creditText}>Designed by NCS ICT - MOD</Text>
            </View>
          </View>
        </KeyboardAvoidingView>
      </SafeAreaView>
    </View>
  );
}

const styles = StyleSheet.create({
  mainContainer: {
    flex: 1,
    backgroundColor: '#1a1a1a',
  },
  backgroundGradient: {
    position: 'absolute',
    left: 0,
    right: 0,
    top: 0,
    bottom: 0,
  },
  shape: {
    position: 'absolute',
    borderWidth: 2,
    borderColor: 'rgba(8, 138, 86, 0.3)',
    borderRadius: 999,
  },
  shape1: {
    width: 200,
    height: 200,
    top: '10%',
    left: '-10%',
  },
  shape2: {
    width: 150,
    height: 150,
    bottom: '20%',
    right: '-15%',
  },
  shape3: {
    width: 100,
    height: 100,
    top: '60%',
    left: '20%',
  },
  safeArea: {
    flex: 1,
  },
  keyboardView: {
    flex: 1,
  },
  container: {
    flex: 1,
    justifyContent: 'center',
    paddingHorizontal: spacing.xl,
  },
  glassCard: {
    backgroundColor: 'rgba(255, 255, 255, 0.95)',
    borderRadius: 8,
    padding: spacing['2xl'],
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 25 },
    shadowOpacity: 0.25,
    shadowRadius: 50,
    elevation: 10,
    borderWidth: 1,
    borderColor: 'rgba(255, 255, 255, 0.3)',
    width: width > 400 ? 400 : '100%',
    alignSelf: 'center',
  },
  headerSection: {
    alignItems: 'center',
    marginBottom: spacing['2xl'],
    position: 'relative',
  },
  logoCircle: {
    width: 80,
    height: 80,
    backgroundColor: colors.surface,
    borderRadius: 8,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: spacing.xl,
    borderWidth: 1,
    borderColor: colors.borderLight,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 4,
    elevation: 2,
  },
  titleRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
  },
  title: {
    fontSize: 28,
    fontWeight: fontWeights.bold,
    color: '#111827',
    letterSpacing: -0.5,
  },
  tooltipButton: {
    marginLeft: spacing.sm,
    padding: spacing.xs,
  },
  tooltipContent: {
    position: 'absolute',
    top: '100%',
    backgroundColor: '#fffbeb',
    borderWidth: 2,
    borderColor: '#fcd34d',
    borderRadius: 8,
    padding: spacing.md,
    width: '100%',
    zIndex: 50,
    marginTop: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 10,
    elevation: 5,
  },
  tooltipTitle: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: '#78350f',
    marginBottom: spacing.xs,
  },
  tooltipText: {
    fontSize: fontSizes.xs,
    color: '#92400e',
    marginBottom: 2,
  },
  subtitle: {
    fontSize: fontSizes.sm,
    color: '#4b5563',
    fontWeight: fontWeights.medium,
    marginTop: spacing.sm,
  },
  errorAlert: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#fef2f2',
    borderWidth: 1,
    borderColor: '#fee2e2',
    borderRadius: 4,
    padding: spacing.md,
    marginBottom: spacing.xl,
  },
  errorAlertText: {
    marginLeft: spacing.sm,
    fontSize: fontSizes.sm,
    color: '#dc2626',
    fontWeight: fontWeights.medium,
    flex: 1,
  },
  formSection: {
    width: '100%',
  },
  inputGroup: {
    marginBottom: spacing.xl,
  },
  labelRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.sm,
  },
  label: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: '#374151',
    marginBottom: spacing.sm,
  },
  forgotText: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.primary,
  },
  inputContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f9fafb',
    borderWidth: 1,
    borderColor: '#e5e7eb',
    borderRadius: 4,
    height: 52,
    paddingHorizontal: spacing.md,
  },
  inputIcon: {
    marginRight: spacing.sm,
  },
  input: {
    flex: 1,
    height: '100%',
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#111827',
  },
  eyeBtn: {
    padding: spacing.xs,
  },
  checkboxRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: spacing.xl,
  },
  checkbox: {
    width: 20,
    height: 20,
    backgroundColor: colors.primary,
    borderRadius: 4,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.sm,
  },
  checkboxLabel: {
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.medium,
    color: '#4b5563',
  },
  button: {
    backgroundColor: colors.primary,
    borderRadius: 4,
    height: 52,
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
  },
  buttonDisabled: {
    opacity: 0.75,
  },
  buttonText: {
    color: colors.surface,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.bold,
    marginRight: spacing.sm,
  },
  biometricBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: spacing.lg,
    padding: spacing.sm,
    backgroundColor: '#ecfdf5',
    borderRadius: 8,
    borderWidth: 1,
    borderColor: '#d1fae5',
  },
  biometricBtnText: {
    marginLeft: spacing.sm,
    fontSize: fontSizes.sm,
    fontWeight: fontWeights.semibold,
    color: colors.primaryDark,
  },
  secureBadgeWrapper: {
    alignItems: 'center',
    marginTop: spacing['3xl'],
    paddingTop: spacing.xl,
    borderTopWidth: 1,
    borderTopColor: '#f3f4f6',
  },
  secureBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f9fafb',
    borderWidth: 1,
    borderColor: '#f3f4f6',
    borderRadius: 4,
    paddingVertical: spacing.sm,
    paddingHorizontal: spacing.base,
  },
  secureBadgeText: {
    fontSize: 12,
    fontWeight: fontWeights.medium,
    color: '#4b5563',
    marginLeft: spacing.sm,
  },
  footer: {
    alignItems: 'center',
    marginTop: spacing['2xl'],
    paddingHorizontal: spacing.base,
  },
  copyrightText: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.8)',
    fontWeight: fontWeights.medium,
    marginBottom: spacing.xs,
    textAlign: 'center',
  },
  creditText: {
    fontSize: 12,
    color: 'rgba(255, 255, 255, 0.6)',
    fontWeight: fontWeights.medium,
  },
});
