import React, { useState, useCallback } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ActivityIndicator,
  RefreshControl,
  SafeAreaView,
  StatusBar,
  ScrollView,
  useColorScheme,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';
import { fleetApi } from '../../api/fleetApi';
import type { FleetVehicle } from '../../api/fleetApi';
import { useThemeColor, spacing, fontSizes, fontWeights } from '../../theme';
import { Ionicons } from '@expo/vector-icons';

export function MyVehicleScreen() {
  const themeColors = useThemeColor();
  const theme = useColorScheme() ?? 'light';
  const [vehicles, setVehicles] = useState<FleetVehicle[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const load = useCallback(async () => {
    setError(null);
    try {
      const res = await fleetApi.myVehicles();
      if (res.success && res.data) {
        setVehicles(res.data);
      }
    } catch {
      setError('Failed to load your assigned vehicles');
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      setLoading(true);
      load();
    }, [load])
  );

  if (loading && vehicles.length === 0) {
    return (
      <View style={[styles.centered, { backgroundColor: themeColors.background }]}>
        <ActivityIndicator size="large" color={themeColors.primary} />
      </View>
    );
  }

  return (
    <SafeAreaView style={[styles.safeArea, { backgroundColor: themeColors.background }]}>
      <StatusBar barStyle={theme === 'dark' ? 'light-content' : 'dark-content'} />

      <ScrollView
        contentContainerStyle={styles.container}
        refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} colors={[themeColors.primary]} tintColor={themeColors.primary} />}
      >
        {error ? <Text style={[styles.error, { color: themeColors.danger }]}>{error}</Text> : null}

        {vehicles.length === 0 ? (
          <View style={styles.emptyWrap}>
            <View style={[styles.emptyIconCircle, { backgroundColor: themeColors.surfaceTertiary }]}>
              <Ionicons name="car-outline" size={48} color={themeColors.textMuted} />
            </View>
            <Text style={[styles.emptyTitle, { color: themeColors.text }]}>No Assigned Vehicle</Text>
            <Text style={[styles.emptySub, { color: themeColors.textSecondary }]}>
              You currently do not have any official vehicle assigned to you. For vehicle requests, contact your T&L officer.
            </Text>
          </View>
        ) : (
          vehicles.map((v) => (
            <View key={v.id} style={[styles.card, { backgroundColor: themeColors.surface, borderColor: themeColors.border }]}>
              {/* Header Box */}
              <View style={[styles.cardHero, { backgroundColor: themeColors.primary }]}>
                <Ionicons name="car-sport" size={40} color="#fff" />
                <View style={styles.heroTextWrap}>
                  <Text style={styles.heroTitle}>{v.make} {v.model}</Text>
                  <Text style={styles.heroSub}>{v.year_of_manufacture || 'Unknown Year'} · {v.vehicle_type}</Text>
                </View>
                <View style={[styles.badge, { backgroundColor: 'rgba(255,255,255,0.2)' }]}>
                  <Text style={styles.badgeText}>ASSIGNED</Text>
                </View>
              </View>

              {/* Details */}
              <View style={styles.cardBody}>
                <View style={styles.detailRow}>
                  <Text style={[styles.detailLabel, { color: themeColors.textMuted }]}>Registration No.</Text>
                  <Text style={[styles.detailValue, { color: themeColors.text }]}>{v.reg_no}</Text>
                </View>
                <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                <View style={styles.detailRow}>
                  <Text style={[styles.detailLabel, { color: themeColors.textMuted }]}>Chassis Number</Text>
                  <Text style={[styles.detailValue, { color: themeColors.text }]} numberOfLines={1}>{v.chassis_number || '—'}</Text>
                </View>
                <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                <View style={styles.detailRow}>
                  <Text style={[styles.detailLabel, { color: themeColors.textMuted }]}>Engine Number</Text>
                  <Text style={[styles.detailValue, { color: themeColors.text }]} numberOfLines={1}>{v.engine_number || '—'}</Text>
                </View>
                <View style={[styles.divider, { backgroundColor: themeColors.border }]} />
                <View style={styles.detailRow}>
                  <Text style={[styles.detailLabel, { color: themeColors.textMuted }]}>Service Status</Text>
                  <View style={{ flexDirection: 'row', alignItems: 'center', gap: 4 }}>
                    <Ionicons
                      name={v.service_status === 'active' ? 'checkmark-circle' : 'build'}
                      size={14}
                      color={v.service_status === 'active' ? '#16a34a' : '#d97706'}
                    />
                    <Text style={[styles.detailValue, { color: v.service_status === 'active' ? '#16a34a' : '#d97706' }]}>
                      {v.service_status ? v.service_status.toUpperCase() : 'UNKNOWN'}
                    </Text>
                  </View>
                </View>
              </View>
            </View>
          ))
        )}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: { flex: 1 },
  centered: { flex: 1, justifyContent: 'center', alignItems: 'center' },
  container: { padding: spacing.xl, paddingBottom: 60 },
  error: { fontSize: 13, padding: spacing.base, textAlign: 'center', marginBottom: spacing.md },

  emptyWrap: { alignItems: 'center', paddingTop: 60, paddingHorizontal: spacing.lg },
  emptyIconCircle: { width: 100, height: 100, borderRadius: 50, justifyContent: 'center', alignItems: 'center', marginBottom: spacing.lg },
  emptyTitle: { fontSize: 20, fontWeight: fontWeights.bold, marginBottom: spacing.sm },
  emptySub: { fontSize: 14, textAlign: 'center', lineHeight: 20 },

  card: { borderRadius: 16, borderWidth: 1, overflow: 'hidden', marginBottom: spacing.xl },
  cardHero: { flexDirection: 'row', alignItems: 'center', padding: spacing.lg },
  heroTextWrap: { flex: 1, marginLeft: spacing.md },
  heroTitle: { fontSize: 18, fontWeight: fontWeights.bold, color: '#fff', marginBottom: 2 },
  heroSub: { fontSize: 13, color: 'rgba(255,255,255,0.8)' },
  badge: { paddingHorizontal: 8, paddingVertical: 4, borderRadius: 12 },
  badgeText: { fontSize: 10, fontWeight: fontWeights.bold, color: '#fff' },

  cardBody: { padding: spacing.lg },
  detailRow: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingVertical: 8 },
  detailLabel: { fontSize: 13, flex: 1 },
  detailValue: { fontSize: 14, fontWeight: fontWeights.semibold, flex: 1, textAlign: 'right' },
  divider: { height: StyleSheet.hairlineWidth, marginVertical: 4 },
});
