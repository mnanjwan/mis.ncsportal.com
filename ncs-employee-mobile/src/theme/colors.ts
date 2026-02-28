import { useColorScheme } from 'react-native';

const tintColorLight = '#088a56'; // NCS Green
const tintColorDark = '#10b981';  // Lighter, more vibrant green for dark mode

export const colors = {
  light: {
    primary: tintColorLight,
    primaryDark: '#066c43',
    primaryLight: 'rgba(8, 138, 86, 0.15)',
    primaryMuted: 'rgba(8, 138, 86, 0.08)',
    danger: '#dc3545',
    dangerDark: '#c82333',
    dangerLight: 'rgba(220, 53, 69, 0.12)',
    success: '#088a56',
    successLight: '#f0fdf4',
    background: '#ffffff', // pure white for that clean fintech look
    surface: '#ffffff',
    surfaceSecondary: '#f9fafb', // slightly off-white for contrast
    surfaceTertiary: '#f3f4f6', // for pill buttons, circles
    border: '#e5e7eb',
    borderLight: '#f3f4f6',
    text: '#111827',
    textSecondary: '#4b5563',
    textMuted: '#9ca3af',
    textOnPrimary: '#ffffff',
    iconBackground: '#f8fafc',
    tabActive: tintColorLight,
    tabInactive: '#9ca3af',
    statusBarStyle: 'dark-content' as const,
  },
  dark: {
    primary: tintColorDark,
    primaryDark: '#059669',
    primaryLight: 'rgba(16, 185, 129, 0.15)',
    primaryMuted: 'rgba(16, 185, 129, 0.08)',
    danger: '#ef4444',
    dangerDark: '#b91c1c',
    dangerLight: 'rgba(239, 68, 68, 0.12)',
    success: '#10b981',
    successLight: 'rgba(16, 185, 129, 0.12)',
    background: '#0f172a', // Deep slate dark mode
    surface: '#1e293b', // slightly lighter for cards
    surfaceSecondary: '#334155',
    surfaceTertiary: '#475569',
    border: '#334155',
    borderLight: '#1e293b',
    text: '#f8fafc',
    textSecondary: '#cbd5e1',
    textMuted: '#94a3b8',
    textOnPrimary: '#0f172a',
    iconBackground: '#1e293b',
    tabActive: tintColorDark,
    tabInactive: '#64748b',
    statusBarStyle: 'light-content' as const,
  },
};

// Hook to get the current theme colors based on system preference
export function useThemeColor() {
  const theme = useColorScheme() ?? 'light';
  return colors[theme];
}

export type Colors = typeof colors.light;

// Still exporting default colors as light for backward compatibility in components not yet updated
export const defaultColors = colors.light;
