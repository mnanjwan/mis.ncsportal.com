/**
 * Notification preferences (per-type toggles).
 * Stored locally; backend can add preference API later (Expo Notifications as per docs).
 */

import * as SecureStore from 'expo-secure-store';

const KEY = 'ncs_notification_preferences';

export type NotificationPreferences = {
  leave_notifications: boolean;
  pass_notifications: boolean;
  emolument_notifications: boolean;
  chat_notifications: boolean;
  fleet_notifications: boolean;
  pharmacy_notifications: boolean;
  system_notifications: boolean;
  quarters_notifications: boolean;
};

const defaults: NotificationPreferences = {
  leave_notifications: true,
  pass_notifications: true,
  emolument_notifications: true,
  chat_notifications: true,
  fleet_notifications: true,
  pharmacy_notifications: true,
  system_notifications: true,
  quarters_notifications: true,
};

export async function getNotificationPreferences(): Promise<NotificationPreferences> {
  try {
    const raw = await SecureStore.getItemAsync(KEY);
    if (!raw) return { ...defaults };
    const parsed = JSON.parse(raw) as Partial<NotificationPreferences>;
    return { ...defaults, ...parsed };
  } catch {
    return { ...defaults };
  }
}

export async function setNotificationPreferences(prefs: Partial<NotificationPreferences>): Promise<void> {
  const current = await getNotificationPreferences();
  const next = { ...current, ...prefs };
  await SecureStore.setItemAsync(KEY, JSON.stringify(next));
}
