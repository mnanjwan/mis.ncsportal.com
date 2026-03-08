import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity, SafeAreaView, StatusBar, useColorScheme } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { useAppSelector } from '../../hooks/redux';
import { TransportStackParamList } from '../../navigation/TransportStack';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

type Nav = NativeStackNavigationProp<TransportStackParamList, 'TransportHome'>;

type MenuItem = { key: keyof TransportStackParamList; title: string; subtitle: string; icon: string; bg: string; color: string; restricted?: boolean };

const MENU_ITEMS: MenuItem[] = [
  // Fleet Manager (T&L Officers)
  { key: 'FleetDashboard' as any, title: 'Fleet & Transport Dashboard', subtitle: 'Manage command vehicles and requests', icon: 'car-sport', bg: '#fef3c7', color: '#d97706', restricted: true },

  // Regular Officers
  { key: 'MyVehicle', title: 'My Official Vehicle', subtitle: 'View your assigned vehicle details', icon: 'car', bg: '#eff6ff', color: '#3b82f6' },
  { key: 'MovementOrders', title: 'Movement Orders', subtitle: 'Postings & transfer movement orders', icon: 'document-text', bg: '#ecfdf5', color: '#10b981' },
  { key: 'MySchedule', title: 'Duty Schedule', subtitle: 'View roster assignments', icon: 'calendar', bg: '#fdf4ff', color: '#a855f7' },
  { key: 'QuarterRequests', title: 'Quarter Requests', subtitle: 'Request accommodation', icon: 'home', bg: '#fff7ed', color: '#ea580c' },
  { key: 'QuarterAllocations', title: 'My Quarters', subtitle: 'Currently allocated accommodation', icon: 'key', bg: '#f0f9ff', color: '#0ea5e9' },
  { key: 'Health', title: 'Health & Pharmacy', subtitle: 'Prescriptions & medical appointments', icon: 'medkit', bg: '#fef2f2', color: '#e11d48' },
  { key: 'ReportsMenu', title: 'Reports & Analytics', subtitle: 'Personal summaries', icon: 'bar-chart', bg: '#f8fafc', color: '#64748b' },
];

export function TransportHomeScreen() {
  const navigation = useNavigation<Nav>();
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const { user } = useAppSelector((s) => s.auth);

  // Check if user has Fleet Management privileges (T&L Officer or Admin)
  const isFleetManager = user?.roles?.some(role => ['CC T&L', 'O/C T&L', 'T&L Officer', 'Staff Officer T&L'].includes(role));

  // Filter menu items based on roles
  const availableItems = MENU_ITEMS.filter(item => {
    if (item.restricted && !isFleetManager) return false;
    return true;
  });

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />
      <ScrollView style={styles.scroll} contentContainerStyle={styles.container} showsVerticalScrollIndicator={false}>

        <Text style={[styles.title, { color: themeColors.text }]}>Transport & Facilities</Text>
        <Text style={[styles.subtitle, { color: themeColors.textSecondary }]}>
          Manage fleet vehicles, movement orders, quarters, and health services.
        </Text>

        {availableItems.map((item) => (
          <TouchableOpacity
            key={item.key}
            style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}
            onPress={() => navigation.navigate(item.key as any)}
            activeOpacity={0.7}
          >
            <View style={[styles.iconWrap, { backgroundColor: item.bg }]}>
              <Ionicons name={item.icon as any} size={24} color={item.color} />
            </View>
            <View style={styles.cardContent}>
              <Text style={[styles.cardTitle, { color: themeColors.text }]}>{item.title}</Text>
              <Text style={[styles.cardSubtitle, { color: themeColors.textSecondary }]} numberOfLines={1}>
                {item.subtitle}
              </Text>
            </View>
            <Ionicons name="chevron-forward" size={20} color={themeColors.textMuted} />
          </TouchableOpacity>
        ))}

      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  scroll: { flex: 1 },
  container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },

  title: { fontSize: 24, fontWeight: fontWeights.bold, marginBottom: 4 },
  subtitle: { fontSize: 13, lineHeight: 18, marginBottom: spacing.xl },

  card: {
    flexDirection: 'row',
    alignItems: 'center',
    borderRadius: 16,
    padding: spacing.md,
    marginBottom: spacing.md,
    borderWidth: 1,
  },
  iconWrap: {
    width: 48,
    height: 48,
    borderRadius: 24,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: spacing.md,
  },
  cardContent: { flex: 1, paddingRight: spacing.sm },
  cardTitle: { fontSize: 16, fontWeight: fontWeights.semibold, marginBottom: 2 },
  cardSubtitle: { fontSize: 12 },
});
