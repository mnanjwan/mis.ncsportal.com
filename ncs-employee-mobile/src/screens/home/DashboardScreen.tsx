import React, { useEffect, useRef, useCallback } from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, SafeAreaView, Platform, StatusBar, Animated, useColorScheme } from 'react-native';
import { useNavigation, useFocusEffect } from '@react-navigation/native';
import { useAppSelector } from '../../hooks/redux';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';
import { passApi } from '../../api/passApi';
import { leaveApi } from '../../api/leaveApi';
import { emolumentApi } from '../../api/emolumentApi';
import { notificationApi } from '../../api/notificationApi';
import { fleetApi } from '../../api/fleetApi';

// Define the type for a Feature
type FeatureItem = {
  id: string;
  title: string;
  icon: string;
  roles?: string[]; // If undefined or empty, accessible to everyone
};

// Strictly aligned to mobile-app-docs features with role-based access
const FEATURES: FeatureItem[] = [
  { id: 'chat', title: 'Chat', icon: 'chatbubbles' },
  { id: 'transport', title: 'Fleet', icon: 'car', roles: ['CD', 'CC T&L', 'O/C T&L', 'T&L Officer', 'Staff Officer T&L', 'Staff Officer', 'Transport Store/Receiver'] },
  { id: 'health', title: 'Health', icon: 'medkit', roles: ['OC Pharmacy', 'Command Pharmacist', 'Central Medical Store'] },
  { id: 'reports', title: 'Reports', icon: 'document-text', roles: ['Area Controller', 'Staff Officer', 'HRD', 'DC Admin'] },
  // Optional: A unified Approvals module that only appears for approver roles
  {
    id: 'approvals',
    title: 'Approvals',
    icon: 'checkmark-circle',
    roles: ['Staff Officer', 'DC Admin', 'Assessor', 'Validator', 'Auditor']
  },
];

export function DashboardScreen() {
  const navigation = useNavigation<any>();
  const { user } = useAppSelector((s) => s.auth);
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';

  const name = user?.officer?.name ?? user?.email ?? 'Officer';
  const firstName = name.split(' ')[0];
  const displayBadge = user?.officer?.rank || user?.roles?.[0] || 'Officer';

  const [recentRequests, setRecentRequests] = React.useState<any[]>([]);
  const [loadingRequests, setLoadingRequests] = React.useState(true);
  const [unreadCount, setUnreadCount] = React.useState(0);
  const [myVehicles, setMyVehicles] = React.useState<any[]>([]);
  const [fleetInbox, setFleetInbox] = React.useState<any[]>([]);

  const fetchUnreadCount = async () => {
    try {
      const res = await notificationApi.unreadCount();
      if (res.success && res.data) {
        setUnreadCount(res.data.count);
      }
    } catch { }
  };

  const fetchRecentRequests = async () => {
    setLoadingRequests(true);
    try {
      const [passRes, leaveRes, emolRes] = await Promise.all([
        passApi.list().catch(() => null),
        leaveApi.list().catch(() => null),
        emolumentApi.myEmoluments().catch(() => null)
      ]);

      let allRequests: any[] = [];

      if (passRes?.success && passRes.data) {
        allRequests = [...allRequests, ...passRes.data.map((r: any) => ({
          id: `pass-${r.id}`, type: 'pass', title: 'Pass Application',
          date: new Date(r.created_at || r.submitted_at || Date.now()),
          status: r.status, icon: 'card-outline'
        }))];
      }

      if (leaveRes?.success && leaveRes.data) {
        allRequests = [...allRequests, ...leaveRes.data.map((r: any) => ({
          id: `leave-${r.id}`, type: 'leave', title: r.leave_type?.name || 'Leave Application',
          date: new Date(r.created_at || r.submitted_at || Date.now()),
          status: r.status, icon: 'calendar-outline'
        }))];
      }

      if (emolRes?.success && emolRes.data) {
        allRequests = [...allRequests, ...emolRes.data.map((r: any) => ({
          id: `emol-${r.id}`, type: 'emolument', title: 'Emolument',
          date: new Date(r.submitted_at || r.created_at || Date.now()),
          status: r.status, icon: 'cash-outline'
        }))];
      }

      allRequests.sort((a, b) => b.date.getTime() - a.date.getTime());
      setRecentRequests(allRequests.slice(0, 5));
    } catch {
      // Ignore errors for widget
    } finally {
      setLoadingRequests(false);
    }
  };

  const fetchFleetWidgets = async () => {
    try {
      const vRes = await fleetApi.myVehicles();
      if (vRes.success && vRes.data) {
        setMyVehicles(vRes.data);
      }
      // If user has approver roles, fetch requests inbox
      if (userRoles.some(r => ['CGC', 'DCG FATS', 'ACG TS', 'CC T&L', 'Staff Officer T&L'].includes(r))) {
        const rRes = await fleetApi.requests();
        if (rRes.success && rRes.data) {
          setFleetInbox(rRes.data.inbox);
        }
      }
    } catch { }
  };

  useFocusEffect(
    useCallback(() => {
      fetchRecentRequests();
      fetchUnreadCount();
    }, [])
  );

  const formatDate = (date: Date) => {
    return date.toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });
  };

  // Animation for notification dot
  const pulseAnim = useRef(new Animated.Value(1)).current;

  useEffect(() => {
    Animated.loop(
      Animated.sequence([
        Animated.timing(pulseAnim, {
          toValue: 0.4,
          duration: 800,
          useNativeDriver: true,
        }),
        Animated.timing(pulseAnim, {
          toValue: 1,
          duration: 800,
          useNativeDriver: true,
        }),
      ])
    ).start();
  }, [pulseAnim]);

  // The user.roles array from Redux state indicates their system permissions
  const userRoles = user?.roles || [];

  // Filter features: include if feature has no specific roles, OR if the user holds at least one of the required roles
  const visibleFeatures = FEATURES.filter(f => {
    if (!f.roles || f.roles.length === 0) return true;
    return f.roles.some(role => userRoles.includes(role));
  });

  const handleFeaturePress = (featureId: string) => {
    switch (featureId) {
      case 'pass':
        navigation.navigate('MyRequests', { screen: 'PassApply' });
        break;
      case 'leave':
        navigation.navigate('MyRequests', { screen: 'LeaveApply' });
        break;
      case 'emolument':
        navigation.navigate('MyRequests', { screen: 'EmolumentRaise' });
        break;
      case 'chat':
        navigation.navigate('Chat');
        break;
      case 'transport':
        navigation.navigate('Transport');
        break;
      case 'health':
        navigation.navigate('Transport', { screen: 'Health' });
        break;
      case 'reports':
        navigation.navigate('Transport', { screen: 'ReportsMenu' });
        break;
      case 'approvals':
        navigation.navigate('Approvals');
        break;
    }
  };


  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} backgroundColor={themeColors.background} translucent={false} />

      <ScrollView
        style={[styles.scroll, { backgroundColor: themeColors.background }]}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.maxWidthContainer}>

          {/* Top Navigation Row */}
          <View style={styles.headerRow}>
            <TouchableOpacity style={[styles.avatarCircle, { backgroundColor: themeColors.surfaceTertiary }]} activeOpacity={0.7}>
              <Text style={[styles.avatarText, { color: themeColors.text }]}>{firstName.charAt(0).toUpperCase()}</Text>
            </TouchableOpacity>

            <View style={styles.headerRightActions}>
              <TouchableOpacity
                style={[styles.iconButton, { backgroundColor: themeColors.surfaceTertiary }]}
                activeOpacity={0.7}
                onPress={() => navigation.navigate('Notifications')}
              >
                <Ionicons name="notifications-outline" size={24} color={themeColors.text} />
                {unreadCount > 0 && (
                  <Animated.View style={[styles.notificationDot, { opacity: pulseAnim, borderColor: themeColors.background }]}>
                    <Text style={styles.notificationText}>{unreadCount > 99 ? '99+' : unreadCount}</Text>
                  </Animated.View>
                )}
              </TouchableOpacity>
              <TouchableOpacity style={styles.primaryPillBtn} activeOpacity={0.8}>
                <Ionicons name="flash" size={14} color="#ffffff" style={styles.flashIcon} />
                <Text style={styles.primaryPillText}>{displayBadge}</Text>
              </TouchableOpacity>
            </View>
          </View>

          {/* Balance / Greeting Area */}
          <View style={styles.greetingSection}>
            <TouchableOpacity style={[styles.badgeSelector, { backgroundColor: themeColors.surfaceSecondary, borderColor: themeColors.border }]} activeOpacity={0.7}>
              <Ionicons name="shield-checkmark" size={14} color={themeColors.primary} />
              <Text style={[styles.badgeSelectorText, { color: themeColors.textSecondary }]}>OFFICIAL PORTAL</Text>
              <Ionicons name="chevron-down" size={14} color={themeColors.textSecondary} />
            </TouchableOpacity>

            <View style={styles.nameRow}>
              <Text style={[styles.greetingName, { color: themeColors.text }]}>Hi, {firstName}</Text>
            </View>
          </View>

          {/* Quick Circular Actions Grid (Role Filtered) */}
          <View style={styles.actionGrid}>
            {visibleFeatures.map((feature) => (
              <View key={feature.id} style={styles.actionItemWrapper}>
                <TouchableOpacity
                  style={[styles.circleButton, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
                  activeOpacity={0.7}
                  onPress={() => handleFeaturePress(feature.id)}
                >
                  <Ionicons name={feature.icon as any} size={28} color={themeColors.primary} />
                </TouchableOpacity>
                <Text style={[styles.actionLabel, { color: themeColors.textSecondary }]}>{feature.title}</Text>
              </View>
            ))}
          </View>

          {/* Role-Based Fleet Widgets */}
          {myVehicles.length > 0 && (
            <TouchableOpacity
              style={[styles.widgetCard, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
              activeOpacity={0.8}
              onPress={() => navigation.navigate('Transport', { screen: 'MyVehicle' })}
            >
              <View style={[styles.widgetIconBox, { backgroundColor: '#fef3c7' }]}>
                <Ionicons name="car" size={20} color="#d97706" />
              </View>
              <View style={styles.widgetContent}>
                <Text style={[styles.widgetTitle, { color: themeColors.text }]}>My Vehicle</Text>
                <Text style={[styles.widgetSub, { color: themeColors.textSecondary }]}>
                  {myVehicles[0].make} • {myVehicles[0].reg_no || 'No Plate'}
                </Text>
              </View>
              <Ionicons name="chevron-forward" size={20} color={themeColors.textMuted} />
            </TouchableOpacity>
          )}

          {fleetInbox.length > 0 && (
            <TouchableOpacity
              style={[styles.widgetCard, { backgroundColor: themeColors.surface, borderColor: themeColors.primary }]}
              activeOpacity={0.8}
              onPress={() => navigation.navigate('Transport', { screen: 'FleetDashboard' })}
            >
              <View style={[styles.widgetIconBox, { backgroundColor: '#fee2e2' }]}>
                <Ionicons name="alert-circle" size={20} color="#dc2626" />
              </View>
              <View style={styles.widgetContent}>
                <Text style={[styles.widgetTitle, { color: themeColors.text }]}>Pending Fleet Approvals</Text>
                <Text style={[styles.widgetSub, { color: themeColors.textSecondary }]}>
                  You have {fleetInbox.length} requests waiting for your action
                </Text>
              </View>
              <View style={[styles.widgetBadge, { backgroundColor: '#dc2626' }]}>
                <Text style={styles.widgetBadgeText}>{fleetInbox.length}</Text>
              </View>
            </TouchableOpacity>
          )}

          {/* Recent Requests List */}
          <View style={styles.recentSection}>
            <View style={styles.sectionHeaderRow}>
              <Text style={[styles.sectionLabel, { color: themeColors.textMuted }]}>RECENT REQUESTS</Text>
              <TouchableOpacity hitSlop={{ top: 10, bottom: 10, left: 10, right: 10 }} onPress={() => navigation.navigate('MyRequests')}>
                <Text style={[styles.seeAllText, { color: themeColors.primary }]}>See all</Text>
              </TouchableOpacity>
            </View>

            <View style={[styles.listContainer, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
              {loadingRequests ? (
                <View style={{ padding: spacing.lg, alignItems: 'center' }}>
                  <Text style={{ color: themeColors.textMuted }}>Loading...</Text>
                </View>
              ) : recentRequests.length > 0 ? (
                recentRequests.map((req, index) => (
                  <React.Fragment key={req.id}>
                    <ListItem
                      icon={req.icon}
                      title={req.title}
                      date={formatDate(req.date)}
                      status={req.status}
                      statusColor={req.status.toUpperCase() === 'APPROVED' ? '#16a34a' : req.status.toUpperCase() === 'REJECTED' ? '#ef4444' : themeColors.textMuted}
                      themeColors={themeColors}
                    />
                    {index < recentRequests.length - 1 && (
                      <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                    )}
                  </React.Fragment>
                ))
              ) : (
                <View style={{ padding: spacing.lg, alignItems: 'center' }}>
                  <Text style={{ color: themeColors.textMuted }}>No recent requests found</Text>
                </View>
              )}
            </View>
          </View>

          {/* Strong bottom buffer ensuring no bottom nav overlap */}
          <View style={styles.bottomBuffer} />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

function ListItem({ icon, title, date, status, statusColor, themeColors }: any) {
  return (
    <View style={styles.listItem}>
      <View style={[styles.listIconBox, { backgroundColor: themeColors.iconBackground }]}>
        <Ionicons name={icon} size={18} color={themeColors.primary} />
      </View>
      <View style={styles.listMiddle}>
        <Text style={[styles.listTitle, { color: themeColors.text }]}>{title}</Text>
        <Text style={[styles.listDate, { color: themeColors.textMuted }]}>{date}</Text>
      </View>
      <View style={styles.listRight}>
        <Text style={[styles.listAmount, { color: statusColor }]}>
          {status}
        </Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
  },
  scroll: {
    flex: 1,
  },
  scrollContent: {
    paddingHorizontal: spacing.xl,
    paddingTop: Platform.OS === 'android' ? spacing.xl : spacing.md,
    // Add extra padding to strictly respect the bottom tab safe zones
    paddingBottom: Platform.OS === 'ios' ? 40 : 20,
  },
  maxWidthContainer: {
    width: '100%',
    maxWidth: 640 // Keeps the content centered and proportional on flip phones/tablets
  },
  headerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing['xl'],
  },
  avatarCircle: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
  },
  avatarText: {
    fontSize: fontSizes.lg,
    fontWeight: fontWeights.semibold,
  },
  headerRightActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing.md,
  },
  iconButton: {
    width: 44,
    height: 44,
    borderRadius: 22,
    justifyContent: 'center',
    alignItems: 'center',
    position: 'relative',
  },
  notificationDot: {
    position: 'absolute',
    top: 6,
    right: 6,
    width: 16,
    height: 16,
    borderRadius: 8,
    backgroundColor: '#ef4444',
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1.5,
  },
  notificationText: {
    color: '#ffffff',
    fontSize: 9,
    fontWeight: 'bold',
  },
  primaryPillBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#088a56', // Always green, regardless of theme
    paddingHorizontal: spacing.lg,
    height: 36,
    borderRadius: 18,
  },
  flashIcon: {
    marginRight: 6,
  },
  primaryPillText: {
    color: '#ffffff',
    fontSize: fontSizes.xs,
    fontWeight: fontWeights.semibold,
  },
  greetingSection: {
    alignItems: 'center',
    marginBottom: spacing['2xl'],
  },
  badgeSelector: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: spacing.md,
    paddingVertical: 8,
    borderRadius: 20,
    borderWidth: 1,
    marginBottom: spacing.md,
    gap: 6,
  },
  badgeSelectorText: {
    fontSize: 12,
    fontWeight: fontWeights.semibold,
    letterSpacing: 0.5,
  },
  nameRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  greetingName: {
    fontSize: 34,
    fontWeight: fontWeights.bold,
    letterSpacing: -1,
  },
  actionGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'center',
    gap: 20,
    marginBottom: spacing['3xl'],
  },
  actionItemWrapper: {
    alignItems: 'center',
    width: 80,
  },
  circleButton: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 1,
    marginBottom: spacing.sm,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.04,
    shadowRadius: 8,
    elevation: 2,
  },
  actionLabel: {
    fontSize: 13,
    fontWeight: fontWeights.bold,
    textAlign: 'center',
  },
  recentSection: {
    marginTop: spacing.md,
  },
  sectionHeaderRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: spacing.lg,
    paddingHorizontal: spacing.xs,
  },
  sectionLabel: {
    fontSize: 12,
    fontWeight: fontWeights.bold,
    letterSpacing: 1.2,
    textTransform: 'uppercase',
  },
  seeAllText: {
    fontSize: 14,
    fontWeight: fontWeights.semibold,
  },
  listContainer: {
    borderRadius: 20,
    borderWidth: 1,
    paddingVertical: spacing.sm,
  },
  listItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 10,
    paddingHorizontal: spacing.lg,
  },
  listIconBox: {
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  listMiddle: {
    flex: 1,
  },
  listTitle: {
    fontSize: 13,
    fontWeight: fontWeights.medium,
    marginBottom: 2,
  },
  listDate: {
    fontSize: 11,
  },
  listRight: {
    alignItems: 'flex-end',
    justifyContent: 'center',
  },
  listAmount: {
    fontSize: 12,
    fontWeight: fontWeights.semibold,
  },
  divider: {
    height: 1,
    marginLeft: 76,
    marginRight: spacing.lg,
  },
  widgetCard: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: spacing.md,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: spacing.md,
  },
  widgetIconBox: {
    width: 40,
    height: 40,
    borderRadius: 20,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  widgetContent: {
    flex: 1,
  },
  widgetTitle: {
    fontSize: 15,
    fontWeight: fontWeights.semibold,
    marginBottom: 2,
  },
  widgetSub: {
    fontSize: 13,
  },
  widgetBadge: {
    paddingHorizontal: 8,
    paddingVertical: 4,
    borderRadius: 12,
    marginLeft: spacing.sm,
  },
  widgetBadgeText: {
    color: '#fff',
    fontSize: 12,
    fontWeight: 'bold',
  },
  bottomBuffer: {
    height: 30, // Firm spacing above safe zones
  }
});
