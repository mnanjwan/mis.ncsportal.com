import React from 'react';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { Ionicons } from '@expo/vector-icons';
import { DashboardScreen } from '../screens/home/DashboardScreen';
import { RequestStack } from './RequestStack';
import { ChatStack } from './ChatStack';
import { TransportStack } from './TransportStack';
import { ProfileStack } from './ProfileStack';
import { useThemeColor, fontSizes } from '../theme';
import { useAppSelector } from '../hooks/redux';

const Tab = createBottomTabNavigator();

function TabIcon({ name, focused, themeColors }: { name: any; focused: boolean; themeColors: any }) {
  return (
    <Ionicons
      name={focused ? name : `${name}-outline`}
      size={26} // Increased size for a bolder look
      color={focused ? themeColors.tabActive : themeColors.tabInactive}
    />
  );
}

export function BottomTabs() {
  const themeColors = useThemeColor();
  const { user } = useAppSelector((s) => s.auth);

  const userRoles = user?.roles || [];

  // Transport roles, mirroring DashboardScreen rules
  const hasTransportRole = userRoles.some(r => ['CD', 'Transport Admin', 'Staff Officer'].includes(r));

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
          elevation: 0, // removes shadow/overlay on android
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
          tabBarIcon: ({ focused }) => <TabIcon name="chatbubbles" focused={focused} themeColors={themeColors} />,
        }}
      />
      {hasTransportRole && (
        <Tab.Screen
          name="Transport"
          component={TransportStack}
          options={{
            title: 'Fleet',
            headerShown: false,
            tabBarIcon: ({ focused }) => <TabIcon name="car" focused={focused} themeColors={themeColors} />,
          }}
        />
      )}
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
