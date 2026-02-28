import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

export function MyVehicleScreen() {
  return (
    <View style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.emoji}>🚗</Text>
        <Text style={styles.title}>My vehicle</Text>
        <Text style={styles.subtitle}>
          No vehicle currently assigned. When a vehicle is allocated to you, details will appear here.
        </Text>
        <Text style={styles.note}>For vehicle requests, contact your T&L officer.</Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background, padding: spacing.xl, justifyContent: 'center' },
  card: { backgroundColor: colors.surface, borderRadius: 16, padding: spacing.xl, borderWidth: 1, borderColor: colors.borderLight, alignItems: 'center' },
  emoji: { fontSize: 48, marginBottom: spacing.base },
  title: { fontSize: fontSizes.xl, fontWeight: fontWeights.bold, color: colors.text, marginBottom: spacing.sm },
  subtitle: { fontSize: fontSizes.sm, color: colors.textSecondary, textAlign: 'center', marginBottom: spacing.base },
  note: { fontSize: fontSizes.xs, color: colors.textMuted },
});
