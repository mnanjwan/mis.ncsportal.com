# Feature 04: Chat System

> **Source studied:** `ChatController.php` (API, 770+ lines), `ChatRoom.php` model, `ChatMessage.php` model, `ChatRoomMember.php` model, web chat routes, `NotificationService.php`

---

## 1. Feature Overview

The NCS Employee app has **four distinct chat capabilities** that are unified into a single feature, now following a **WhatsApp-premium UI standard**:

| Sub-Feature | Description | Auto-Join Logic |
|-------------|-------------|-----------------|
| **Command Chat Room** | Every command has a chat room. Officers auto-join their command's room | Officer's `present_station` → auto-add to that command's room |
| **Management Chat Room** | AC (Assistant Controller) rank and above | Officer's `substantive_rank` ≥ AC → auto-add to management room |
| **Staff Officer Admin** | Staff Officers can manage chat rooms, add/remove members | Role-based: `Staff Officer` role |
| **Group Creation** | WhatsApp-style groups for document sharing | Any officer can create, invite members |

**WhatsApp-Style UI Enhancements:**
- **Bold unread indicators** and green badge counts (`#25d366`).
- **Real-time synchronization** via **WebSockets (Laravel Reverb)**.
- **Read tracking** using `last_read_at` on a per-member basis.
- **Double-tick status** (`✓✓`) for sent messages.

---

## 2. Room Types & Membership Logic

The system supports five distinct types of chat rooms, each with varying levels of automation and administrative control:

| Room Type | Purpose | Automation | Logic / Trigger |
| :--- | :--- | :--- | :--- |
| **Direct Message** | Private 1-on-1 chats | Manual | Created when a user starts a chat with another officer via Global Search. |
| **Command Room** | Official station-wide chat | **Auto-Join** | Linked to `present_station`. Officers are added instantly upon Documentation or Posting via Web Portal. |
| **Unit Room** | Department/Unit specific chat | **Auto-Join** | Linked to the `unit` field in the officer profile. Syncs on profile update. |
| **Management** | Senior leadership chat | **Auto-Join** | Restricted to **AC (Assistant Controller)** rank and above. Filtered via `PromotionService`. |
| **Custom Group** | Ad-hoc collaboration groups | Manual | Created by any officer. The creator acts as the admin and can add/remove members. |

---

## 3. Synchronization Mechanism (`OfficerObserver`)
To ensure the mobile app complements the web portal perfectly, we use a **Global Observer**:
*   **Web Trigger**: Any change to an officer's rank, station, or unit in the Web Portal (Postings, Documentation, Promotions) immediately triggers the `OfficerObserver`.
*   **Instant Sync**: The observer calls `ChatService::syncOfficerRooms()`, which recalculates memberships and adds the officer to the correct rooms before they even open the mobile app.
*   **Notifications Flow**:
    *   **User**: Receives an **instant Push Notification** and an **Email** when added to a new official room.
    *   **Admins**: Command-level **Staff Officers** receive a notification when a new member is added to their official rooms (Command/Unit/Management).
*   **Resilience**: If an officer was previously removed but their status still matches the room criteria, they are re-activated and notified automatically.

---

## 4. Technical Infrastructure
*   **ChatService**: Centralized logic for calculating memberships based on rank, station, and unit.
*   **OfficerObserver**: Global hook that triggers `ChatService` whenever an officer's profile is updated via the web portal (Postings, Promotions, Documentation).
*   **Data Models**: `ChatRoom`, `ChatRoomMember`(pivot with active status and read tracking), `ChatMessage` (with attachment, reaction, and threading support).
*   **Real-Time Engine**: Laravel Reverb (WebSocket Server) + Laravel Echo (Mobile Client).
*   **API Layer**: RESTful endpoints using Laravel Sanctum for secure mobile communication.

---

## 5. Data Models

### `chat_rooms` Table

```
┌──────────────┬──────────┬──────────────────────────────────────────┐
│ Column       │ Type     │ Notes                                    │
├──────────────┼──────────┼──────────────────────────────────────────┤
│ id           │ bigint PK│                                          │
│ command_id   │ bigint FK│ → commands.id (nullable for groups)      │
│ room_type    │ string   │ 'command' / 'management' / 'group'       │
│ name         │ string   │ Room display name                        │
│ description  │ text     │ Room description                         │
│ is_active    │ boolean  │ Is room active?                          │
│ created_at   │ timestamp│                                          │
│ updated_at   │ timestamp│                                          │
└──────────────┴──────────┴──────────────────────────────────────────┘
```

### `chat_room_members` Table

```
┌──────────────┬──────────┬──────────────────────────────────────────┐
│ Column       │ Type     │ Notes                                    │
├──────────────┼──────────┼──────────────────────────────────────────┤
│ id           │ bigint PK│                                          │
│ chat_room_id │ bigint FK│ → chat_rooms.id                          │
│ officer_id   │ bigint FK│ → officers.id                            │
│ added_by     │ bigint FK│ → users.id (who added this member)       │
│ is_active    │ boolean  │ Active membership                        │
│ last_read_at │ datetime │ When the user last read the room         │
│ joined_at    │ datetime │ When joined                              │
│ left_at      │ datetime │ When left (nullable)                     │
│ created_at   │ timestamp│                                          │
│ updated_at   │ timestamp│                                          │
└──────────────┴──────────┴──────────────────────────────────────────┘
```

### `chat_messages` Table

```
┌──────────────┬──────────┬──────────────────────────────────────────┐
│ Column       │ Type     │ Notes                                    │
├──────────────┼──────────┼──────────────────────────────────────────┤
│ id           │ bigint PK│                                          │
│ chat_room_id │ bigint FK│ → chat_rooms.id                          │
│ sender_id    │ bigint FK│ → officers.id (sender)                   │
│ parent_id    │ bigint FK│ → chat_messages.id (Self-relation/Reply) │
│ message_text │ text     │ Message content                          │
│ attachment_url│ string  │ File attachment URL (nullable)           │
│ is_broadcast │ boolean  │ Is this a broadcast/announcement?        │
│ is_pinned    │ boolean  │ Is this message pinned to the top?       │
│ is_deleted   │ boolean  │ Soft-delete flag                         │
│ deleted_at   │ timestamp│ When the message was deleted             │
│ created_at   │ timestamp│                                          │
│ updated_at   │ timestamp│                                          │
└──────────────┴──────────┴──────────────────────────────────────────┘
```

### `chat_message_reactions` Table (NEW)

```
┌──────────────┬──────────┬──────────────────────────────────────────┐
│ Column       │ Type     │ Notes                                    │
├──────────────┼──────────┼──────────────────────────────────────────┤
│ id           │ bigint PK│                                          │
│ chat_message_id│ bigint FK│ → chat_messages.id                     │
│ officer_id   │ bigint FK│ → officers.id                            │
│ reaction_type│ string   │ Emoji string (e.g., '❤️')                 │
└──────────────┴──────────┴──────────────────────────────────────────┘
```

---

## 6. Room Types & Auto-Join Logic

### Command Chat Room (`room_type = 'command'`)

```
Officer is assigned to Lagos Command (present_station = 5)
  → On login, check: Is officer a member of Lagos Command chat room?
  → If not, auto-add as member
  → If officer is transferred (present_station changes), remove from old room, add to new
```

**Implementation:**
```typescript
// On app login / profile refresh
async function syncCommandChatRoom(officer: Officer) {
  const commandId = officer.present_station;
  const commandRoom = await api.getChatRoomByCommand(commandId, 'command');
  
  if (commandRoom && !await api.isMember(commandRoom.id, officer.id)) {
    await api.joinRoom(commandRoom.id);
  }
}
```

### Management Chat Room (`room_type = 'management'`)

```
Officer rank = "AC" or above (DC, ACG, DCG, CGC)
  → Auto-join the management chat room
  → If officer rank is below AC, they should NOT see this room
```

**Rank hierarchy for eligibility check:**
```
CGC > DCG > ACG > DC > AC > CI > DSC > ASC I > ASC II > Insp > AI
                                ↑
                      Management threshold
```

### Group Chat (`room_type = 'group'`)

- **Any officer** can create a group
- Creator selects members from a searchable officer list
- Groups can be used for document sharing (attachments)
- No auto-join — manual invitation only

---

## 7. API Endpoints

### Core & Advanced Endpoints

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| **GET** | `/api/v1/chat/rooms` | List user's chat rooms (including unread_count) |
| **POST** | `/api/v1/chat/rooms` | Create chat room/group |
| **GET** | `/api/v1/chat/rooms/{id}/messages` | Get messages (auto-marks room as read) |
| **POST** | `/api/v1/chat/rooms/{id}/messages` | Send text message (supports `parent_id` for replies) |
| **POST** | `/api/v1/chat/rooms/{id}/messages/attachment` | Upload and send file/image |
| **POST** | `/api/v1/chat/rooms/{id}/messages/{mid}/react` | Toggle emoji reaction |
| **POST** | `/api/v1/chat/rooms/{id}/messages/{mid}/pin` | Toggle pinned status (Admin/Staff only) |
| **POST** | `/api/v1/chat/rooms/{id}/messages/{mid}/broadcast` | Toggle broadcast status (Staff only) |
| **GET** | `/api/v1/chat/rooms/{id}/messages/{mid}/info` | Get read receipts/info |
| **DELETE** | `/api/v1/chat/rooms/{id}/messages/{mid}` | Delete message for everyone |
| **POST** | `/api/v1/chat/rooms/{id}/mark-read` | Explicitly mark room as read |
| **GET** | `/api/v1/chat/officers/search` | Custom system-wide officer search |

---

## 8. Real-Time Messaging

The application has transitioned from polling to **True Real-Time Messaging** using **WebSockets**:
*   **Backend Engine**: **Laravel Reverb**, a high-performance socket server.
*   **Broadcaster**: Reverb implements the Pusher protocol.
*   **Mobile Client**: **Laravel Echo** + `pusher-js` listeners for `ChatMessageSent`, `ChatMessageDeleted`, `ChatMessageReacted`, etc.
*   **Reliability**: A fallback polling mechanism (6s interval) remains in place for cases where the WebSocket connection cannot be established.

---

## 9. Administrative Context & Permissions

To ensure the chat system properly complements the Web Portal, specific administrative rules apply:

*   **Staff Officer Privileges**: 
    - Can remove members from **Command Rooms** if they belong to the same station.
    - Can manage members (Add/Remove) in all **Custom Groups** and **Unit Rooms**.
    - Can **Broadcast** messages to mark them as official announcements.
    - Can **Pin** messages to the top of the room.
*   **Auto-Join Logic**:
    - **Command Room**: Linked to `present_station`. Updates instantly on Web Documentation.
    - **Unit Room**: Linked to `unit` field. Updates on Web Update.
    - **Management Room**: Reserved for AC rank and above. Filtered via `PromotionService`.
*   **Membership ID Convention**:
    - APIs for membership (`members`, `addMembers`, `removeMember`) consistently use **`officer_id`** from the `officers` table, NOT the `user_id`.
*   **Troubleshooting Removal**:
    - If removal fails with "Not active", verify that the `is_active` flag in `chat_room_members` is actually `true`.
    - Permission checks rely on the `added_by` field (for group creators) or the `Staff Officer` role (for global management).

---

## 10. Mobile Screens

The mobile implementation following the premium WhatsApp theme:

### Screen 6.1: Chat Room List (`ChatRoomsScreen.tsx`)
- **WhatsApp Style**: Bold names for unread, green badges, hairline dividers.
- **Unread Banner**: Summarizes total unread across all rooms.
- **Tab Badge**: Dynamic unread count on the "Chat" bottom tab.

### Screen 6.2: Chat Conversation (`ChatRoomScreen.tsx`)
- **Bubble UI**: Right-aligned own messages with double-tick delivery indicators.
- **WebSocket Sync**: New messages, reactions, and deletions appear instantly.
- **Auto-Read**: Count clears immediately when room is opened.
- **Interaction**: Long-press for the premium options list (Reply, Info, React, Pin, Delete).

---

## 11. React Native Implementation

### Component Structure
```
src/screens/chat/
├── ChatRoomsScreen.tsx       → List of all rooms (WhatsApp style)
├── ChatRoomScreen.tsx        → Conversation UI + WebSocket listeners
├── CreateGroupScreen.tsx     → New group creation
├── CreateDMScreen.tsx        → New 1-on-1 search & chat
├── MessageInfoScreen.tsx     → Detailed read receipts list
```

### API Interface (`chatApi.ts`)
```typescript
export interface ChatRoomItem {
  id: number;
  name: string;
  room_type: string;
  unread_count: number;
  member_count: number;
  last_message: ChatMessageItem | null;
}
```

---

## 12. Operational Workflow (Real-Time)

1.  **Start Socket Server**: Admin executes `php artisan reverb:start`.
2.  **App Connection**: Mobile app initializes `laravel-echo` on launch.
3.  **Room Entry**: App joins `PresenceChannel` (`chat.room.{id}`).
4.  **Interaction**: Any message/reaction event triggers a Laravel Broadcast event.
5.  **Sync**: Other participants receive the event via Echo and update local state instantly.

---

## 13. Test Checklist

- [x] Auto-join command room on login/sync
- [x] Auto-join management room for AC+ officers
- [x] Management room NOT visible for below-AC officers
- [x] Create group with multiple members
- [x] **Real-Time Delivery**: Messages appear on recipient's screen in <100ms.
- [x] **Live Reactions**: Emojis update instantly via WebSockets.
- [x] **Threaded Replies**: Quoted context displays correctly with sender names.
- [x] **Sticky Pins**: Pinned list at the top updates in real-time.
- [x] **Read Receipts**: Info screen correctly identifies members via `last_read_at`.
- [x] **Broadcast UI**: Golden accents and megaphone icons render for marked messages.
- [x] **Soft Deletion**: Messages are replaced with "This message was deleted" text for all users.
- [x] Send file attachment (Images/Documents) with client-side compression (0.7).
- [x] Double-tick indicators appear on sent messages.
- [x] Staff Officer can add/remove members from command room.
- [x] **Instant Web Sync**: `OfficerObserver` triggers membership updates on web postings.
- [x] **Keyboard Safety**: Typing area never covered by OS keyboard.

---

## 14. Future Roadmap

1.  **Typing Indicators**: Show "Officer X is typing..." in the room header.
2.  **Offline Queuing**: Allow officers to send messages while offline.
3.  **Message Search**: Search for text within a chat room.
4.  **Audio/Voice Notes**: Support for recording and sending voice messages.

---

## 15. Production Deployment (VPS)

For local development, `php artisan reverb:start` is sufficient. For a live VPS, you must ensure the process is persistent and secure (SSL/WSS).

### Step 1: Supervisor (Process Persistence)
Supervisor ensures Reverb restarts automatically if it crashes or the server reboots.

1.  **Install Supervisor**: `sudo apt install supervisor -y`
2.  **Create Config**: `sudo nano /etc/supervisor/conf.d/reverb.conf`
3.  **Insert the following**:
```ini
[program:reverb]
process_name=%(program_name)s
command=php /var/www/pisportal/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/pisportal/storage/logs/reverb.log
stopasgroup=true
killasgroup=true
```
4.  **Activate**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

### Step 2: Firewall (Port 8080)
Ensure the WebSocket port is open to the public:
`sudo ufw allow 8080/tcp`

### Step 3: Nginx Reverse Proxy (SSL / WSS)
To use `wss://` (required for production mobile apps), use Nginx as a reverse proxy:

1.  **Edit Nginx Config**: `sudo nano /etc/nginx/sites-available/reverb`
2.  **Configuration**:
```nginx
server {
    listen 80;
    server_name reverb.yourdomain.com; # Replace with your subdomain

    location / {
        proxy_http_version 1.1;
        proxy_set_header Host $http_host;
        proxy_set_header Scheme $scheme;
        proxy_set_header SERVER_PORT $server_port;
        proxy_set_header REMOTE_ADDR $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";

        proxy_pass http://127.0.0.1:8080;
    }
}
```
3.  **Enable & Get SSL**:
```bash
sudo ln -s /etc/nginx/sites-available/reverb /etc/nginx/sites-enabled/
sudo certbot --nginx -d reverb.yourdomain.com
sudo systemctl restart nginx
```
