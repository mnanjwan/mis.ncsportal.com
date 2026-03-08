import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { TransportHomeScreen } from '../screens/transport/TransportHomeScreen';
import { FleetDashboardScreen } from '../screens/transport/FleetDashboardScreen';
import { CreateFleetRequestScreen } from '../screens/transport/CreateFleetRequestScreen';
import { FleetRequestDetailScreen } from '../screens/transport/FleetRequestDetailScreen';
import { MovementOrdersScreen } from '../screens/transport/MovementOrdersScreen';
import { MovementOrderDetailScreen } from '../screens/transport/MovementOrderDetailScreen';
import { MyScheduleScreen } from '../screens/transport/MyScheduleScreen';
import { QuarterRequestsScreen } from '../screens/transport/QuarterRequestsScreen';
import { QuarterRequestSubmitScreen } from '../screens/transport/QuarterRequestSubmitScreen';
import { QuarterAllocationsScreen } from '../screens/transport/QuarterAllocationsScreen';
import { MyVehicleScreen } from '../screens/transport/MyVehicleScreen';
import { HealthStack } from './HealthStack';
import { ReportsMenuScreen } from '../screens/reports/ReportsMenuScreen';
import { ReportDetailScreen } from '../screens/reports/ReportDetailScreen';
import { useThemeColor, fontSizes } from '../theme';
import { TouchableOpacity } from 'react-native';
import { Ionicons } from '@expo/vector-icons';

export type TransportStackParamList = {
  TransportHome: undefined;
  FleetDashboard: undefined;
  CreateFleetRequest: undefined;
  FleetRequestDetail: { id: number };
  MovementOrders: undefined;
  MovementOrderDetail: { id: number };
  MySchedule: undefined;
  QuarterRequests: undefined;
  QuarterRequestSubmit: undefined;
  QuarterAllocations: undefined;
  MyVehicle: undefined;
  Health: undefined; // Routing to nested stack
  ReportsMenu: undefined;
  ReportDetail: { type: string; title: string; icon: string; color: string };
};

const Stack = createNativeStackNavigator<TransportStackParamList>();

export function TransportStack() {
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
        name="TransportHome"
        component={TransportHomeScreen}
        options={({ navigation }) => ({
          title: 'Transport & Facilities',
          headerLeft: () => (
            <TouchableOpacity onPress={() => navigation.goBack()} style={{ marginRight: 16 }}>
              <Ionicons name="arrow-back" size={24} color={themeColors.textOnPrimary} />
            </TouchableOpacity>
          ),
        })}
      />
      <Stack.Screen name="FleetDashboard" component={FleetDashboardScreen} options={{ title: 'Fleet Dashboard' }} />
      <Stack.Screen name="CreateFleetRequest" component={CreateFleetRequestScreen} options={{ title: 'Request Vehicle' }} />
      <Stack.Screen name="FleetRequestDetail" component={FleetRequestDetailScreen} options={{ title: 'Request Timeline' }} />
      <Stack.Screen name="MovementOrders" component={MovementOrdersScreen} options={{ title: 'Movement orders' }} />
      <Stack.Screen name="MovementOrderDetail" component={MovementOrderDetailScreen} options={{ title: 'Order detail' }} />
      <Stack.Screen name="MySchedule" component={MyScheduleScreen} options={{ title: 'My duty schedule' }} />
      <Stack.Screen name="QuarterRequests" component={QuarterRequestsScreen} options={{ title: 'Quarter requests' }} />
      <Stack.Screen name="QuarterRequestSubmit" component={QuarterRequestSubmitScreen} options={{ title: 'Request quarter' }} />
      <Stack.Screen name="QuarterAllocations" component={QuarterAllocationsScreen} options={{ title: 'My allocations' }} />
      <Stack.Screen name="MyVehicle" component={MyVehicleScreen} options={{ title: 'My vehicle' }} />
      <Stack.Screen name="Health" component={HealthStack} options={{ headerShown: false }} />
      <Stack.Screen name="ReportsMenu" component={ReportsMenuScreen} options={{ title: 'Reports' }} />
      <Stack.Screen
        name="ReportDetail"
        component={ReportDetailScreen}
        options={{ title: 'Report View', headerShown: false }}
      />
    </Stack.Navigator>
  );
}
