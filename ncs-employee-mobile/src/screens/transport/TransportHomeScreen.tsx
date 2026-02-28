import React from 'react';
import { View, Text, StyleSheet, ScrollView, TouchableOpacity } from 'react-native';
import { useNavigation } from '@react-navigation/native';
import { NativeStackNavigationProp } from '@react-navigation/native-stack';
import { TransportStackParamList } from '../../navigation/TransportStack';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

type Nav = NativeStackNavigationProp<TransportStackParamList, 'TransportHome'>;

const MENU_ITEMS: { key: keyof TransportStackParamList; title: string; subtitle: string; emoji: string }[] = [
  { key: 'MovementOrders', title: 'Movement orders', subtitle: 'Posting & transfer orders', emoji: '📋' },
  { key: 'MySchedule', title: 'My duty schedule', subtitle: 'Roster assignments', emoji: '📅' },
  { key: 'QuarterRequests', title: 'Quarter requests', subtitle: 'Accommodation requests', emoji: '🏠' },
  { key: 'QuarterAllocations', title: 'My allocations', subtitle: 'Quarter allocations', emoji: '🔑' },
  { key: 'MyVehicle', title: 'My vehicle', subtitle: 'Assigned vehicle (when applicable)', emoji: '🚗' },
  { key: 'Health', title: 'Health & pharmacy', subtitle: 'Prescriptions & appointments', emoji: '🏥' },
  { key: 'MyReports', title: 'Reports', subtitle: 'Personal summaries', emoji: '📊' },
];

export function TransportHomeScreen() {
  const navigation = useNavigation<Nav>();

  return (
    <ScrollView
      style={styles.scroll}
      contentContainerStyle={styles.container}
      showsVerticalScrollIndicator={false}
    >
      <Text style={styles.title}>Transport & more</Text>
      <Text style={styles.subtitle}>Movement, schedule, quarters, vehicle, reports</Text>
      {MENU_ITEMS.map((item) => (
        <TouchableOpacity
          key={item.key}
          style={styles.card}
          onPress={() => navigation.navigate(item.key)}
          activeOpacity={0.7}
        >
          <Text style={styles.emoji}>{item.emoji}</Text>
          <View style={styles.cardContent}>
            <Text style={styles.cardTitle}>{item.title}</Text>
            <Text style={styles.cardSubtitle}>{item.subtitle}</Text>
          </View>
          <Text style={styles.chevron}>›</Text>
        </TouchableOpacity>
      ))}
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: { flex: 1, backgroundColor: colors.background },
  container: { padding: spacing.xl, paddingBottom: spacing['3xl'] },
  title: { fontSize: fontSizes['2xl'], fontWeight: fontWeights.bold, color: colors.text, marginBottom: spacing.xs },
  subtitle: { fontSize: fontSizes.sm, color: colors.textSecondary, marginBottom: spacing.xl },
  card: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: colors.surface,
    borderRadius: 16,
    padding: spacing.lg,
    marginBottom: spacing.sm,
    borderWidth: 1,
    borderColor: colors.borderLight,
  },
  emoji: { fontSize: 28, marginRight: spacing.base },
  cardContent: { flex: 1 },
  cardTitle: { fontSize: fontSizes.base, fontWeight: fontWeights.semibold, color: colors.text },
  cardSubtitle: { fontSize: fontSizes.sm, color: colors.textSecondary, marginTop: 2 },
  chevron: { fontSize: fontSizes['2xl'], color: colors.textMuted },
});
