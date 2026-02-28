import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { colors, spacing, fontSizes, fontWeights } from '../../theme';

export function MyReportsScreen() {
  return (
    <View style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.emoji}>📊</Text>
        <Text style={styles.title}>Reports</Text>
        <Text style={styles.subtitle}>
          Personal summaries and report generation will be available here in a future update.
        </Text>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background, padding: spacing.xl, justifyContent: 'center' },
  card: { backgroundColor: colors.surface, borderRadius: 16, padding: spacing.xl, borderWidth: 1, borderColor: colors.borderLight, alignItems: 'center' },
  emoji: { fontSize: 48, marginBottom: spacing.base },
  title: { fontSize: fontSizes.xl, fontWeight: fontWeights.bold, color: colors.text, marginBottom: spacing.sm },
  subtitle: { fontSize: fontSizes.sm, color: colors.textSecondary, textAlign: 'center' },
});
