import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { ProfileScreen } from '../screens/profile/ProfileScreen';
import { EditContactScreen } from '../screens/profile/EditContactScreen';
import { colors, fontSizes } from '../theme';

export type ProfileStackParamList = {
  Profile: undefined;
  EditContact: { officerId: number };
};

const Stack = createNativeStackNavigator<ProfileStackParamList>();

export function ProfileStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: colors.primary },
        headerTintColor: colors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen name="Profile" component={ProfileScreen} options={{ title: 'Profile' }} />
      <Stack.Screen name="EditContact" component={EditContactScreen} options={{ title: 'Edit Contact' }} />
    </Stack.Navigator>
  );
}
