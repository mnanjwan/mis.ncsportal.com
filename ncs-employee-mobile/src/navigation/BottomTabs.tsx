import React, { useEffect, useState } from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { DashboardScreen } from '../screens/home/DashboardScreen';
import { RequestStack } from './RequestStack';
import { ChatStack } from './ChatStack';
import { ProfileStack } from './ProfileStack';
import { useThemeColor, fontSizes } from '../theme';
import { useAppSelector } from '../hooks/redux';
import { chatApi } from '../api/chatApi';
import { useNavigationState } from '@react-navigation/native';

const Tab = createBottomTabNavigator();

function TabIcon({ name, focused, themeColors }: { name: any; focused: boolean; themeColors: any }) {
  return (
    <Ionicons
      name={focused ? name : `${name}-outline`}
      size={26}
      color={focused ? themeColors.tabActive : themeColors.tabInactive}
    />
  );
}

function ChatTabIcon({ focused, themeColors }: { focused: boolean; themeColors: any }) {
  const [unread, setUnread] = useState(0);

  const fetchUnread = async () => {
    try {
      const res = await chatApi.rooms();
      if (res.success && res.data) {
        const total = res.data.reduce((s, r) => s + (r.unread_count ?? 0), 0);
        setUnread(total);
      }
    } catch { }
  };

  useEffect(() => {
    fetchUnread();
    // Refresh every 10s as a fallback
    const iv = setInterval(fetchUnread, 10000);
    return () => clearInterval(iv);
  }, []);

  // Also refresh when the icon component is rendered/focused
  useEffect(() => {
    if (focused) fetchUnread();
  }, [focused]);

  return (
    <View>
      <Ionicons
        name={focused ? 'chatbubbles' : 'chatbubbles-outline'}
        size={26}
        color={focused ? themeColors.tabActive : themeColors.tabInactive}
      />
      {unread > 0 && (
        <View style={styles.badge}>
          <Text style={styles.badgeText}>{unread > 99 ? '99+' : unread}</Text>
        </View>
      )}
    </View>
  );
}

export function BottomTabs() {
  const themeColors = useThemeColor();
  const { user } = useAppSelector((s) => s.auth);
  const userRoles = user?.roles || [];

  return (
    <Tab.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: themeColors.primary },
        headerTintColor: themeColors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
        tabBarActiveTintColor: themeColors.tabActive,
        tabBarInactiveTintColor: themeColors.tabInactive,
        tabBarStyle: {
          backgroundColor: themeColors.surface,
          borderTopColor: themeColors.border,
          elevation: 0,
        },
        tabBarLabelStyle: { fontSize: 11, marginBottom: 4, fontWeight: 'bold' },
      }}
    >
      <Tab.Screen
        name="Home"
        component={DashboardScreen}
        options={{
          headerShown: false,
          tabBarIcon: ({ focused }) => <TabIcon name="home" focused={focused} themeColors={themeColors} />,
        }}
      />
      <Tab.Screen
        name="MyRequests"
        component={RequestStack}
        options={{
          title: 'Requests',
          headerShown: false,
          // @ts-ignore
          unmountOnBlur: true,
          tabBarIcon: ({ focused }) => <TabIcon name="document-text" focused={focused} themeColors={themeColors} />,
        }}
      />
      <Tab.Screen
        name="Chat"
        component={ChatStack}
        options={{
          title: 'Chat',
          headerShown: false,
          tabBarIcon: ({ focused }) => <ChatTabIcon focused={focused} themeColors={themeColors} />,
        }}
      />
      <Tab.Screen
        name="Profile"
        component={ProfileStack}
        options={{
          title: 'Profile',
          headerShown: false,
          tabBarIcon: ({ focused }) => <TabIcon name="person" focused={focused} themeColors={themeColors} />,
        }}
      />
    </Tab.Navigator>
  );
}

const styles = StyleSheet.create({
  badge: {
    position: 'absolute',
    top: -4,
    right: -8,
    backgroundColor: '#25d366',
    borderRadius: 10,
    minWidth: 18,
    height: 18,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 4,
  },
  badgeText: { color: '#fff', fontSize: 10, fontWeight: 'bold' },
});
