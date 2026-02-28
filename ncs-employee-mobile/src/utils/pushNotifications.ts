/**
 * Expo push token and permission handling.
 * Register token with backend after login via authApi.registerPushToken().
 */

import * as Device from 'expo-device';
import { Platform } from 'react-native';

let Notifications: any = null;
if (Device.isDevice) {
  try {
    // Disabled for Expo Go testing. Uncomment when using a Development Build.
    // Notifications = require('expo-notifications');
  } catch (e) {
    // Ignore require error
  }
}

// Show notifications when app is in foreground (optional: set to false to only show in tray)
// Skip configuring in Expo Go to avoid SDK 53+ warnings/errors
if (Device.isDevice && Notifications) {
  try {
    Notifications.setNotificationHandler({
      handleNotification: async () => ({
        shouldShowAlert: true,
        shouldPlaySound: true,
        shouldSetBadge: true,
      }),
    });
  } catch (error) {
    console.log('Failed to set notification handler:', error);
  }
}

export async function getExpoPushToken(): Promise<string | null> {
  // Return null if not on a physical device or running in Expo Go
  if (Platform.OS !== 'ios' && Platform.OS !== 'android') return null;
  if (!Device.isDevice || !Notifications) {
    console.log('Push notifications are not supported in simulators or Expo Go (SDK 53+).');
    return null;
  }

  try {
    const { status: existing } = await Notifications.getPermissionsAsync();
    let final = existing;
    if (existing !== 'granted') {
      const { status } = await Notifications.requestPermissionsAsync();
      final = status;
    }
    if (final !== 'granted') return null;
    const tokenData = await Notifications.getExpoPushTokenAsync();
    const token = tokenData?.data;
    return token && typeof token === 'string' && token.startsWith('ExponentPushToken[') ? token : null;
  } catch (error) {
    console.log('Failed to get Expo push token:', error);
    return null;
  }
}
