import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { MyRequestsScreen } from '../screens/requests/MyRequestsScreen';
import { PassDetailScreen } from '../screens/pass/PassDetailScreen';
import { PassApplyScreen } from '../screens/pass/PassApplyScreen';
import { LeaveDetailScreen } from '../screens/leave/LeaveDetailScreen';
import { LeaveApplyScreen } from '../screens/leave/LeaveApplyScreen';
import { EmolumentDetailScreen } from '../screens/emolument/EmolumentDetailScreen';
import { EmolumentRaiseScreen } from '../screens/emolument/EmolumentRaiseScreen';
import { colors, fontSizes } from '../theme';

export type RequestStackParamList = {
  MyRequests: undefined;
  PassDetail: { id: number };
  PassApply: { fromDashboard?: boolean } | undefined;
  LeaveDetail: { id: number };
  LeaveApply: { fromDashboard?: boolean } | undefined;
  EmolumentDetail: { id: number };
  EmolumentRaise: { fromDashboard?: boolean } | undefined;
};

const Stack = createNativeStackNavigator<RequestStackParamList>();

export function RequestStack() {
  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: colors.primary },
        headerTintColor: colors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen
        name="MyRequests"
        component={MyRequestsScreen}
        options={{ title: 'My Requests' }}
      />
      <Stack.Screen name="PassDetail" component={PassDetailScreen} options={{ title: 'Pass Detail' }} />
      <Stack.Screen name="PassApply" component={PassApplyScreen} options={{ title: 'Apply for Pass' }} />
      <Stack.Screen name="LeaveDetail" component={LeaveDetailScreen} options={{ title: 'Leave Detail' }} />
      <Stack.Screen name="LeaveApply" component={LeaveApplyScreen} options={{ title: 'Apply for Leave' }} />
      <Stack.Screen name="EmolumentDetail" component={EmolumentDetailScreen} options={{ title: 'Emolument Detail' }} />
      <Stack.Screen name="EmolumentRaise" component={EmolumentRaiseScreen} options={{ title: 'Raise Emolument' }} />
    </Stack.Navigator>
  );
}
