import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { TransportHomeScreen } from '../screens/transport/TransportHomeScreen';
import { MovementOrdersScreen } from '../screens/transport/MovementOrdersScreen';
import { MovementOrderDetailScreen } from '../screens/transport/MovementOrderDetailScreen';
import { MyScheduleScreen } from '../screens/transport/MyScheduleScreen';
import { QuarterRequestsScreen } from '../screens/transport/QuarterRequestsScreen';
import { QuarterRequestSubmitScreen } from '../screens/transport/QuarterRequestSubmitScreen';
import { QuarterAllocationsScreen } from '../screens/transport/QuarterAllocationsScreen';
import { MyVehicleScreen } from '../screens/transport/MyVehicleScreen';
import { HealthScreen } from '../screens/health/HealthScreen';
import { MyReportsScreen } from '../screens/transport/MyReportsScreen';
import { colors, fontSizes } from '../theme';

export type TransportStackParamList = {
  TransportHome: undefined;
  MovementOrders: undefined;
  MovementOrderDetail: { id: number };
  MySchedule: undefined;
  QuarterRequests: undefined;
  QuarterRequestSubmit: undefined;
  QuarterAllocations: undefined;
  MyVehicle: undefined;
  Health: undefined;
  MyReports: undefined;
};

const Stack = createNativeStackNavigator<TransportStackParamList>();

export function TransportStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: colors.primary },
        headerTintColor: colors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen name="TransportHome" component={TransportHomeScreen} options={{ title: 'Transport & more' }} />
      <Stack.Screen name="MovementOrders" component={MovementOrdersScreen} options={{ title: 'Movement orders' }} />
      <Stack.Screen name="MovementOrderDetail" component={MovementOrderDetailScreen} options={{ title: 'Order detail' }} />
      <Stack.Screen name="MySchedule" component={MyScheduleScreen} options={{ title: 'My duty schedule' }} />
      <Stack.Screen name="QuarterRequests" component={QuarterRequestsScreen} options={{ title: 'Quarter requests' }} />
      <Stack.Screen name="QuarterRequestSubmit" component={QuarterRequestSubmitScreen} options={{ title: 'Request quarter' }} />
      <Stack.Screen name="QuarterAllocations" component={QuarterAllocationsScreen} options={{ title: 'My allocations' }} />
      <Stack.Screen name="MyVehicle" component={MyVehicleScreen} options={{ title: 'My vehicle' }} />
      <Stack.Screen name="Health" component={HealthScreen} options={{ title: 'Health & pharmacy' }} />
      <Stack.Screen name="MyReports" component={MyReportsScreen} options={{ title: 'Reports' }} />
    </Stack.Navigator>
  );
}
