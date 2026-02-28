import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { TouchableOpacity, Text } from 'react-native';
import { NotificationListScreen } from '../screens/notifications/NotificationListScreen';
import { NotificationSettingsScreen } from '../screens/notifications/NotificationSettingsScreen';
import { colors, fontSizes } from '../theme';

export type NotificationStackParamList = {
  NotificationList: undefined;
  NotificationSettings: undefined;
};

const Stack = createNativeStackNavigator<NotificationStackParamList>();

export function NotificationStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: colors.primary },
        headerTintColor: colors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen
        name="NotificationList"
        component={NotificationListScreen}
        options={({ navigation }) => ({
          title: 'Notifications',
          headerRight: () => (
            <TouchableOpacity onPress={() => navigation.navigate('NotificationSettings')} style={{ marginRight: 12 }}>
              <Text style={{ color: colors.textOnPrimary, fontSize: fontSizes.sm, fontWeight: '600' }}>Preferences</Text>
            </TouchableOpacity>
          ),
        })}
      />
      <Stack.Screen
        name="NotificationSettings"
        component={NotificationSettingsScreen}
        options={{ title: 'Notification preferences' }}
      />
    </Stack.Navigator>
  );
}
