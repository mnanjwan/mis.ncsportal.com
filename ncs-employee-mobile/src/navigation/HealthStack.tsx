import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { PharmacyDashboardScreen } from '../screens/health/PharmacyDashboardScreen';
import { CreateRequisitionScreen } from '../screens/health/CreateRequisitionScreen';
import { RequisitionDetailScreen } from '../screens/health/RequisitionDetailScreen';
import { useThemeColor, fontSizes } from '../theme';

export type HealthStackParamList = {
    PharmacyDashboard: undefined;
    CreateRequisition: undefined;
    RequisitionDetail: { id: number };
};

const Stack = createNativeStackNavigator<HealthStackParamList>();

export function HealthStack() {
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
                name="PharmacyDashboard"
                component={PharmacyDashboardScreen}
                options={{ headerShown: false }} // Custom header modeled
            />
            <Stack.Screen
                name="CreateRequisition"
                component={CreateRequisitionScreen}
                options={{ presentation: 'modal', headerShown: false }} // Custom modal header modeled
            />
            <Stack.Screen
                name="RequisitionDetail"
                component={RequisitionDetailScreen}
                options={{ headerShown: false }} // Custom header modeled
            />
        </Stack.Navigator>
    );
}
