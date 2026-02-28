# Feature 05: Push Notifications

> **Source studied:** `NotificationService.php` (3,229 lines, 84 methods, 92 notification types), `Notification.php` model, `SendNotificationEmailJob.php`

---

## 1. Feature Overview

Every notification that fires on the web application **must also fire as a push notification on the mobile app**. The existing `NotificationService::notify()` method is the single chokepoint — adding FCM dispatch here guarantees that all 92 notification types automatically reach mobile devices with **zero individual changes**.

---

## 2. Architecture

### Current Flow (Web)
```
Event → NotificationService method → notify() → [DB record + Email Job]
```

### New Flow (Web + Mobile)
```
Event → NotificationService method → notify() → [DB record + Email Job + FCM Push Job]
```

### Single Modification Point

The only backend change required:

```php
// In NotificationService::notify()
public function notify(User $user, string $type, string $title, string $message, ...): Notification
{
    // 1. Create DB notification (existing)
    $notification = Notification::create([...]);

    // 2. Send email (existing)
    if ($sendEmail && $user->email) {
        SendNotificationEmailJob::dispatch($user, $notification);
    }

    // 3. NEW: Send mobile push notification
    if ($user->fcm_tokens()->where('is_active', true)->exists()) {
        SendMobilePushNotificationJob::dispatch($user, $notification);
    }

    return $notification;
}
```

---

## 3. FCM Token Management

### New Database Table: `fcm_tokens`

```
┌──────────────┬──────────┬──────────────────────────────────────┐
│ Column       │ Type     │ Notes                                │
├──────────────┼──────────┼──────────────────────────────────────┤
│ id           │ bigint PK│                                      │
│ user_id      │ bigint FK│ → users.id                           │
│ token        │ text     │ FCM device token                     │
│ device_id    │ string   │ Unique device identifier             │
│ device_name  │ string   │ e.g. "iPhone 15 Pro"                 │
│ platform     │ string   │ 'ios' or 'android'                   │
│ is_active    │ boolean  │ Token still valid?                   │
│ last_used_at │ timestamp│ Last time token was used             │
│ created_at   │ timestamp│                                      │
│ updated_at   │ timestamp│                                      │
└──────────────┴──────────┴──────────────────────────────────────┘
```

### API Endpoints

```
POST   /api/v1/fcm/register          → Register new FCM token
DELETE /api/v1/fcm/unregister        → Remove FCM token (on logout)
GET    /api/v1/notifications          → List in-app notifications (paginated)
POST   /api/v1/notifications/{id}/read → Mark notification as read
POST   /api/v1/notifications/read-all  → Mark all as read
GET    /api/v1/notifications/unread-count → Get unread count
```

### Token Registration

```json
// POST /api/v1/fcm/register
{
  "token": "fMCh7k...",
  "device_id": "A1B2C3D4-E5F6-...",
  "device_name": "iPhone 15 Pro",
  "platform": "ios"
}
```

---

## 4. Push Notification Payload Format

### Standard Payload
```json
{
  "notification": {
    "title": "Leave Application Approved",
    "body": "Your leave from 01/03/2026 to 15/03/2026 has been approved."
  },
  "data": {
    "notification_id": 1234,
    "notification_type": "leave_application_approved",
    "entity_type": "leave_application",
    "entity_id": 88,
    "deep_link": "ncsapp://leave/88"
  }
}
```

### Deep-Link Routing (18 Entity Types)

| entity_type | Deep Link | Mobile Screen |
|-------------|-----------|---------------|
| `leave_application` | `ncsapp://leave/{id}` | LeaveDetailScreen |
| `pass_application` | `ncsapp://pass/{id}` | PassDetailScreen |
| `emolument` | `ncsapp://emolument/{id}` | EmolumentDetailScreen |
| `chat_message` | `ncsapp://chat/{room_id}` | ChatConversationScreen |
| `fleet_request` | `ncsapp://transport/{id}` | FleetRequestDetailScreen |
| `pharmacy_requisition` | `ncsapp://pharmacy/{id}` | PharmacyRequisitionDetailScreen |
| `officer` | `ncsapp://profile/{id}` | ProfileScreen |
| `manning_request` | `ncsapp://requests/{id}` | RequestDetailScreen |
| `quarter_allocation` | `ncsapp://quarters/{id}` | QuarterDetailScreen |
| `posting` | `ncsapp://postings/{id}` | PostingDetailScreen |
| `query` | `ncsapp://queries/{id}` | QueryDetailScreen |
| `course` | `ncsapp://courses/{id}` | CourseDetailScreen |
| `duty_roster` | `ncsapp://duty-roster/{id}` | DutyRosterScreen |
| `internal_staff_order` | `ncsapp://iso/{id}` | ISODetailScreen |
| `fleet_vehicle` | `ncsapp://fleet/{id}` | FleetVehicleScreen |
| `pharmacy_stock` | `ncsapp://pharmacy-stock/{id}` | PharmacyStockScreen |
| `recruit` | `ncsapp://recruit/{id}` | RecruitDetailScreen |
| `user` | `ncsapp://settings` | SettingsScreen |

---

## 5. Complete Notification Catalog (92 Types)

All 92 notification types from `NotificationService.php` are categorized by module in the main README. Each fires through `notify()` → automatic FCM push.

**Modules:** Leave (5), Pass (3), Emolument (8), Manning (7), Quarters (9), Posting (5), Officer Status (8), Query (7), Course (3), Duty Roster (7), Internal Staff Orders (3), Fleet (8), Pharmacy (6), Recruit (7), System/Role (6)

---

## 6. React Native Implementation

### Component Structure
```
src/features/notifications/
├── screens/
│   ├── NotificationListScreen.tsx    → All notifications inbox
│   └── NotificationSettingsScreen.tsx → Toggle notification preferences
├── components/
│   ├── NotificationCard.tsx          → Individual notification item
│   ├── NotificationBadge.tsx         → Unread count on tab bar
│   └── NotificationTypeIcon.tsx      → Icon per notification type
├── hooks/
│   ├── useNotifications.ts
│   ├── usePushNotifications.ts       → FCM setup + permissions
│   └── useDeepLinking.ts            → Handle deep-link routing
├── services/
│   ├── pushNotificationService.ts    → Expo Notifications setup
│   └── deepLinkService.ts           → URL scheme handler
├── api/
│   └── notificationApi.ts
└── types/
    └── notification.ts
```

### Expo Push Setup
```typescript
import * as Notifications from 'expo-notifications';
import * as Device from 'expo-device';
import Constants from 'expo-constants';

export async function registerForPushNotifications(): Promise<string | null> {
  if (!Device.isDevice) return null;

  const { status: existingStatus } = await Notifications.getPermissionsAsync();
  let finalStatus = existingStatus;

  if (existingStatus !== 'granted') {
    const { status } = await Notifications.requestPermissionsAsync();
    finalStatus = status;
  }

  if (finalStatus !== 'granted') return null;

  // Get FCM token (via Expo)
  const token = await Notifications.getExpoPushTokenAsync({
    projectId: Constants.expirationId?.extra?.eas?.projectId,
  });

  return token.data;
}

// Handle notification tap → deep link
Notifications.addNotificationResponseReceivedListener((response) => {
  const data = response.notification.request.content.data;
  if (data.deep_link) {
    Linking.openURL(data.deep_link);
  }
});
```

### Notification Preferences (per type toggle)
```typescript
export interface NotificationPreferences {
  leave_notifications: boolean;
  pass_notifications: boolean;
  emolument_notifications: boolean;
  chat_notifications: boolean;
  fleet_notifications: boolean;
  pharmacy_notifications: boolean;
  system_notifications: boolean;
}
```

---

## 7. Testing Checklist

- [ ] FCM token registration on login
- [ ] FCM token removal on logout
- [ ] Push notification delivery for each major module
- [ ] Deep-link routing from notification tap
- [ ] Notification list displays all types
- [ ] Mark single as read
- [ ] Mark all as read
- [ ] Unread badge count updates
- [ ] Background notification handling
- [ ] Notification grouping by type (iOS)
- [ ] Notification preferences toggle
