import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { ProfileScreen } from '../screens/profile/ProfileScreen';
import { EditContactScreen } from '../screens/profile/EditContactScreen';
import { EditBankingScreen } from '../screens/profile/EditBankingScreen';
import { useThemeColor, fontSizes } from '../theme';

export type ProfileStackParamList = {
  Profile: undefined;
  EditContact: { officerId: number };
  EditBanking: { officerId: number };
};

const Stack = createNativeStackNavigator<ProfileStackParamList>();

export function ProfileStack() {
  const themeColors = useThemeColor();
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: themeColors.primary },
        headerTintColor: themeColors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen name="Profile" component={ProfileScreen} options={{ title: 'My Profile' }} />
      <Stack.Screen name="EditContact" component={EditContactScreen} options={{ title: 'Edit Contact Info' }} />
      <Stack.Screen name="EditBanking" component={EditBankingScreen} options={{ title: 'Banking & Pension' }} />
    </Stack.Navigator>
  );
}
