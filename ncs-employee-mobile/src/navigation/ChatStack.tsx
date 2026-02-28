import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { ChatRoomsScreen } from '../screens/chat/ChatRoomsScreen';
import { ChatRoomScreen } from '../screens/chat/ChatRoomScreen';
import { CreateGroupScreen } from '../screens/chat/CreateGroupScreen';
import { useThemeColor, fontSizes } from '../theme';

export type ChatStackParamList = {
  ChatRooms: undefined;
  ChatRoom: { roomId: number; roomName: string };
  CreateGroup: undefined;
};

const Stack = createNativeStackNavigator<ChatStackParamList>();

export function ChatStack() {
  const themeColors = useThemeColor();

  return (
    <Stack.Navigator
      screenOptions={{
        headerStyle: { backgroundColor: themeColors.primary },
        headerTintColor: themeColors.textOnPrimary,
        headerTitleStyle: { fontSize: fontSizes.lg, fontWeight: '600' },
      }}
    >
      <Stack.Screen name="ChatRooms" component={ChatRoomsScreen} options={{ title: 'Messages' }} />
      <Stack.Screen
        name="ChatRoom"
        component={ChatRoomScreen}
        options={({ route }) => ({ title: route.params.roomName })}
      />
      <Stack.Screen name="CreateGroup" component={CreateGroupScreen} options={{ title: 'New Group' }} />
    </Stack.Navigator>
  );
}
