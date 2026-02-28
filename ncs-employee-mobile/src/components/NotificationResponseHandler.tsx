import { useEffect } from 'react';
import type { NavigationContainerRef } from '@react-navigation/native';
import { store } from '../store/store';
import * as Device from 'expo-device';

let Notifications: any = null;
if (Device.isDevice) {
  try {
    // Disabled for Expo Go testing. Uncomment when using a Development Build.
    // Notifications = require('expo-notifications');
  } catch (e) {
    // Ignore require error
  }
}

type NavRef = React.RefObject<NavigationContainerRef<Record<string, object | undefined>> | null>;

/**
 * When user taps a push notification, navigate to the relevant screen using entity_type and entity_id.
 */
export function useNotificationResponseHandler(navigationRef: NavRef) {
  useEffect(() => {
    if (!navigationRef.current || !Notifications) return;
    const sub = Notifications.addNotificationResponseReceivedListener((response: any) => {
      const data = response.notification.request.content.data as Record<string, unknown>;
      const entityType = data?.entity_type as string | undefined;
      const entityId = data?.entity_id as number | undefined;
      if (!entityType || entityId == null) return;
      const token = store.getState().auth.token;
      if (!token) return;
      const nav = navigationRef.current;
      if (!nav) return;
      try {
        switch (entityType) {
          case 'pass_application':
            nav.navigate('Main', { screen: 'MyRequests', params: { screen: 'PassDetail', params: { id: entityId } } });
            break;
          case 'leave_application':
            nav.navigate('Main', { screen: 'MyRequests', params: { screen: 'LeaveDetail', params: { id: entityId } } });
            break;
          case 'emolument':
            nav.navigate('Main', { screen: 'MyRequests', params: { screen: 'EmolumentDetail', params: { id: entityId } } });
            break;
          case 'quarter':
          case 'quarter_request':
          case 'quarter_allocation':
            nav.navigate('Main', { screen: 'Transport', params: { screen: 'QuarterRequests', params: undefined } });
            break;
          case 'fleet_request':
          case 'fleet_vehicle':
          case 'movement_order':
            nav.navigate('Main', { screen: 'Transport', params: { screen: 'MovementOrders', params: undefined } });
            break;
          case 'pharmacy_procurement':
          case 'pharmacy_requisition':
          case 'pharmacy_stock':
            nav.navigate('Main', { screen: 'Transport', params: { screen: 'Health', params: undefined } });
            break;
          case 'chat_message':
          case 'chat_room':
            nav.navigate('Main', { screen: 'Chat', params: { screen: 'ChatRoom', params: { roomId: entityId, roomName: 'Chat' } } });
            break;
          case 'officer':
            nav.navigate('Main', { screen: 'Profile' });
            break;
          case 'manning_request':
            nav.navigate('Main', { screen: 'MyRequests' });
            break;
          case 'duty_roster':
            nav.navigate('Main', { screen: 'Transport', params: { screen: 'MySchedule', params: undefined } });
            break;
          default:
            nav.navigate('Main', { screen: 'Notifications' });
        }
      } catch {
        nav.navigate('Main', { screen: 'Notifications' });
      }
    });
    return () => sub.remove();
  }, [navigationRef]);
}
