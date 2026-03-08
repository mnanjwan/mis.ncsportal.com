import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { TouchableOpacity } from 'react-native';
import { NotificationListScreen } from '../screens/notifications/NotificationListScreen';
import { NotificationSettingsScreen } from '../screens/notifications/NotificationSettingsScreen';
import { useThemeColor, fontSizes } from '../theme';
import { Ionicons } from '@expo/vector-icons';

export type NotificationStackParamList = {
  NotificationList: undefined;
  NotificationSettings: undefined;
};

const Stack = createNativeStackNavigator<NotificationStackParamList>();

export function NotificationStack() {
  const themeColors = useThemeColor();
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: themeColors.primary },
        headerTintColor: themeColors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen
        name="NotificationList"
        component={NotificationListScreen}
        options={({ navigation }) => ({
          title: 'Notifications',
          headerLeft: () => (
            <TouchableOpacity onPress={() => navigation.goBack()} style={{ marginRight: 16 }}>
              <Ionicons name="arrow-back" size={24} color={themeColors.textOnPrimary} />
            </TouchableOpacity>
          ),
          headerRight: () => (
            <TouchableOpacity onPress={() => navigation.navigate('NotificationSettings')} style={{ marginRight: 4 }}>
              <Ionicons name="settings-outline" size={22} color={themeColors.textOnPrimary} />
            </TouchableOpacity>
          ),
        })}
      />
      <Stack.Screen
        name="NotificationSettings"
        component={NotificationSettingsScreen}
        options={{ title: 'Preferences' }}
      />
    </Stack.Navigator>
  );
}
