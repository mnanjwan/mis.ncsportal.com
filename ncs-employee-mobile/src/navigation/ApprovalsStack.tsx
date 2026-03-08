import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { ApprovalsDashboardScreen } from '../screens/approvals/ApprovalsDashboardScreen';
import { useThemeColor, fontSizes } from '../theme';
import { TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export type ApprovalsStackParamList = {
    ApprovalsDashboard: undefined;
};

const Stack = createNativeStackNavigator<ApprovalsStackParamList>();

export function ApprovalsStack() {
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
                name="ApprovalsDashboard"
                component={ApprovalsDashboardScreen}
                options={({ navigation }) => ({
                    title: 'Unified Inbox',
                    headerLeft: () => (
                        <TouchableOpacity onPress={() => navigation.goBack()} style={{ marginRight: 16 }}>
                            <Ionicons name="arrow-back" size={24} color={themeColors.textOnPrimary} />
                        </TouchableOpacity>
                    ),
                })}
            />
        </Stack.Navigator>
    );
}
