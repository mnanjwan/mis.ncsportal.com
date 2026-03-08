import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { ChatRoomsScreen } from '../screens/chat/ChatRoomsScreen';
import { ChatRoomScreen } from '../screens/chat/ChatRoomScreen';
import { CreateGroupScreen } from '../screens/chat/CreateGroupScreen';
import { CreateDMScreen } from '../screens/chat/CreateDMScreen';
import { RoomDetailsScreen } from '../screens/chat/RoomDetailsScreen';
import { MemberSearchScreen } from '../screens/chat/MemberSearchScreen';
import { MessageInfoScreen } from '../screens/chat/MessageInfoScreen';
import { useThemeColor, fontSizes } from '../theme';

export type ChatStackParamList = {
  ChatRooms: undefined;
  ChatRoom: { roomId: number; roomName: string };
  RoomDetails: { roomId: number; roomName: string };
  MemberSearch: { roomId: number };
  CreateGroup: undefined;
  CreateDM: undefined;
  MessageInfo: { roomId: number; messageId: number };
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
      <Stack.Screen
        name="RoomDetails"
        component={RoomDetailsScreen}
        options={{ title: 'Group Info' }}
      />
      <Stack.Screen
        name="MemberSearch"
        component={MemberSearchScreen}
        options={{ title: 'Add Member' }}
      />
      <Stack.Screen name="CreateGroup" component={CreateGroupScreen} options={{ title: 'New Group' }} />
      <Stack.Screen name="CreateDM" component={CreateDMScreen} options={{ title: 'New Chat' }} />
      <Stack.Screen name="MessageInfo" component={MessageInfoScreen} options={{ title: 'Message Info' }} />
    </Stack.Navigator>
  );
}
