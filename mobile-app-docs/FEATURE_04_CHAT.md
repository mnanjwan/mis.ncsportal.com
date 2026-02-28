# Feature 04: Chat System

> **Source studied:** `ChatController.php` (API, 145 lines), `ChatRoom.php` model, `ChatMessage.php` model, `ChatRoomMember.php` model, web chat routes, `NotificationService.php`

---

## 1. Feature Overview

The NCS Employee app has **four distinct chat capabilities** that are unified into a single feature:

| Sub-Feature | Description | Auto-Join Logic |
|-------------|-------------|-----------------|
| **Command Chat Room** | Every command has a chat room. Officers auto-join their command's room | Officer's `present_station` → auto-add to that command's room |
| **Management Chat Room** | AC (Assistant Controller) rank and above | Officer's `substantive_rank` ≥ AC → auto-add to management room |
| **Staff Officer Admin** | Staff Officers can manage chat rooms, add/remove members | Role-based: `Staff Officer` role |
| **Group Creation** | WhatsApp-style groups for document sharing | Any officer can create, invite members |

---

## 2. Data Models

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
│ message_text │ text     │ Message content                          │
│ attachment_url│ string  │ File attachment URL (nullable)           │
│ is_broadcast │ boolean  │ Is this a broadcast/announcement?        │
│ created_at   │ timestamp│                                          │
│ updated_at   │ timestamp│                                          │
└──────────────┴──────────┴──────────────────────────────────────────┘
```

---

## 3. Room Types & Auto-Join Logic

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

## 4. API Endpoints

### Existing API Endpoints (from `api.php`)

```
GET  /api/v1/chat/rooms                  → List user's chat rooms
POST /api/v1/chat/rooms                  → Create chat room
GET  /api/v1/chat/rooms/{id}/messages    → Get room messages (paginated)
POST /api/v1/chat/rooms/{id}/messages    → Send message
```

### New/Enhanced API Endpoints Needed

```
# Room management
GET    /api/v1/chat/rooms                         → List all rooms (user is member of)
POST   /api/v1/chat/rooms                         → Create group room
GET    /api/v1/chat/rooms/{id}                    → Room detail + members
PUT    /api/v1/chat/rooms/{id}                    → Update room name/description
DELETE /api/v1/chat/rooms/{id}                    → Delete group (creator only)

# Membership
POST   /api/v1/chat/rooms/{id}/join               → Auto-join (command/management)
POST   /api/v1/chat/rooms/{id}/leave              → Leave group
POST   /api/v1/chat/rooms/{id}/members            → Add members (admin/creator)
DELETE /api/v1/chat/rooms/{id}/members/{officerId} → Remove member (admin/creator)

# Messages
GET    /api/v1/chat/rooms/{id}/messages            → Paginated messages (cursor-based)
POST   /api/v1/chat/rooms/{id}/messages            → Send text message
POST   /api/v1/chat/rooms/{id}/messages/attachment  → Send file/document

# Search
GET    /api/v1/officers/search?q={query}           → Search officers for member selection

# Sync
POST   /api/v1/chat/sync                           → Sync auto-join rooms on login
```

### API Request/Response Specs

#### `GET /api/v1/chat/rooms` — List Rooms

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Lagos Command",
      "room_type": "command",
      "command_id": 5,
      "description": "Official Lagos Command chat",
      "is_active": true,
      "member_count": 45,
      "unread_count": 3,
      "last_message": {
        "id": 892,
        "sender": { "id": 15, "initials": "A.B.", "surname": "Smith", "rank": "ASC II" },
        "message_text": "Meeting at 15:00",
        "created_at": "2026-02-24T14:30:00Z"
      }
    },
    {
      "id": 2,
      "name": "Management Chat",
      "room_type": "management",
      "command_id": null,
      "member_count": 12,
      "unread_count": 0,
      "last_message": null
    },
    {
      "id": 15,
      "name": "Project Alpha Team",
      "room_type": "group",
      "command_id": null,
      "member_count": 6,
      "unread_count": 7,
      "last_message": {
        "id": 1050,
        "sender": { "id": 22, "initials": "C.D.", "surname": "Johnson", "rank": "Insp" },
        "message_text": "Shared the updated report",
        "attachment_url": "/storage/chat/report.pdf",
        "created_at": "2026-02-24T16:00:00Z"
      }
    }
  ]
}
```

#### `POST /api/v1/chat/rooms/{id}/messages` — Send Message

**Request:**
```json
{
  "message_text": "Meeting rescheduled to 16:00",
  "is_broadcast": false
}
```

**Validation:**
```php
'message_text' => 'required|string|max:5000',
'is_broadcast' => 'nullable|boolean'
```

#### `POST /api/v1/chat/rooms/{id}/messages/attachment` — Send Attachment

**Request (multipart/form-data):**
```
message_text: "Here's the updated document"
attachment: [file] (PDF, JPEG, PNG, DOCX, XLSX — max 10MB)
```

#### `POST /api/v1/chat/rooms` — Create Group

**Request:**
```json
{
  "name": "Project Alpha Team",
  "description": "Team for Project Alpha coordination",
  "room_type": "group",
  "member_ids": [15, 22, 33, 44]
}
```

---

## 5. Real-Time Messaging (WebSocket)

### Technology: Laravel Echo + Pusher/Soketi

```typescript
// Real-time message listener
import Echo from 'laravel-echo';

const echo = new Echo({
  broadcaster: 'pusher',
  key: process.env.PUSHER_KEY,
  cluster: process.env.PUSHER_CLUSTER,
  wsHost: process.env.WS_HOST,
  wsPort: process.env.WS_PORT,
  forceTLS: false,
  disableStats: true,
  authEndpoint: '/api/v1/broadcasting/auth',
  auth: {
    headers: { Authorization: `Bearer ${token}` },
  },
});

// Subscribe to a room channel
function subscribeToRoom(roomId: number) {
  echo.private(`chat.room.${roomId}`)
    .listen('MessageSent', (event: ChatMessageEvent) => {
      // Add message to local state
      dispatch(addMessage({ roomId, message: event.message }));
    })
    .listen('MemberJoined', (event: MemberEvent) => {
      dispatch(addMember({ roomId, member: event.member }));
    })
    .listen('MemberLeft', (event: MemberEvent) => {
      dispatch(removeMember({ roomId, memberId: event.member.id }));
    });
}
```

### WebSocket Events

| Event | Channel | Payload |
|-------|---------|---------|
| `MessageSent` | `private-chat.room.{id}` | `{ message, sender, room_id }` |
| `MemberJoined` | `private-chat.room.{id}` | `{ member, room_id }` |
| `MemberLeft` | `private-chat.room.{id}` | `{ member_id, room_id }` |
| `RoomUpdated` | `private-chat.room.{id}` | `{ room }` |

---

## 6. Mobile Screens

### Screen 6.1: Chat Room List

```
┌─────────────────────────────────────┐
│  💬 Messages                        │
│  ─────────────────────────────────  │
│  [🔍 Search rooms...]              │
│                                     │
│  ── OFFICIAL ROOMS ──               │
│  ┌─────────────────────────────┐   │
│  │ 🏢 Lagos Command            │   │
│  │ A.B. Smith: Meeting at 15:00│   │
│  │ 14:30              🔴 3     │   │
│  └─────────────────────────────┘   │
│  ┌─────────────────────────────┐   │
│  │ 👔 Management Chat          │   │
│  │ No messages yet             │   │
│  │                             │   │
│  └─────────────────────────────┘   │
│                                     │
│  ── MY GROUPS ──                    │
│  ┌─────────────────────────────┐   │
│  │ 📁 Project Alpha Team       │   │
│  │ C.D. Johnson: Shared the...│   │
│  │ 16:00              🔴 7     │   │
│  └─────────────────────────────┘   │
│                                     │
│              [+ New Group]          │
└─────────────────────────────────────┘
```

### Screen 6.2: Chat Conversation

```
┌─────────────────────────────────────┐
│  ← Lagos Command        👥 45      │
│  ─────────────────────────────────  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ ASC II A.B. Smith    14:25  │   │
│  │ Reminder: Quarterly review  │   │
│  │ meeting at Conference Room  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Insp C.D. Johnson   14:30  │   │
│  │ Meeting at 15:00            │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ 📎 report.pdf (2.5MB)      │   │  ← Attachment
│  │ DC E.F. Williams    14:45   │   │
│  │ Here's the quarterly report │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌────────────────┬──┬──┐          │
│  │ Type a message │📎│➤ │          │
│  └────────────────┴──┴──┘          │
└─────────────────────────────────────┘
```

### Screen 6.3: Create Group

```
┌─────────────────────────────────────┐
│  ← New Group                        │
│  ─────────────────────────────────  │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Group Name                  │   │
│  │ [Project Alpha Team     ]  │   │
│  └─────────────────────────────┘   │
│                                     │
│  ┌─────────────────────────────┐   │
│  │ Description (optional)      │   │
│  │ [Team coordination      ]  │   │
│  └─────────────────────────────┘   │
│                                     │
│  Add Members                        │
│  [🔍 Search officers...]           │
│                                     │
│  Selected (3):                      │
│  ┌──────────────────────────┐      │
│  │ ✓ ASC II A.B. Smith  ✕  │      │
│  │ ✓ Insp C.D. Johnson  ✕  │      │
│  │ ✓ DC E.F. Williams   ✕  │      │
│  └──────────────────────────┘      │
│                                     │
│  Search Results:                    │
│  ┌──────────────────────────┐      │
│  │ ☐ ASC I G.H. Brown      │      │
│  │ ☐ CI I.J. Davis         │      │
│  └──────────────────────────┘      │
│                                     │
│         [Create Group]              │
└─────────────────────────────────────┘
```

---

## 7. React Native Implementation

### Component Structure
```
src/features/chat/
├── screens/
│   ├── ChatRoomListScreen.tsx       → List of all rooms
│   ├── ChatConversationScreen.tsx   → Messages + send
│   ├── CreateGroupScreen.tsx        → New group creation
│   ├── RoomInfoScreen.tsx           → Room details + members
│   └── MemberSearchScreen.tsx       → Search officers to add
├── components/
│   ├── ChatRoomCard.tsx             → Room preview card
│   ├── MessageBubble.tsx            → Individual message
│   ├── MessageInput.tsx             → Text input + attach + send
│   ├── AttachmentPreview.tsx        → File/image preview
│   ├── MemberList.tsx               → Room members list
│   ├── OfficerSearchItem.tsx        → Officer search result
│   └── UnreadBadge.tsx              → Unread count indicator
├── hooks/
│   ├── useChatRooms.ts
│   ├── useMessages.ts
│   ├── useWebSocket.ts             → Echo/Pusher connection
│   └── useChatSync.ts              → Auto-join sync
├── api/
│   └── chatApi.ts
├── services/
│   └── websocketService.ts          → WebSocket connection manager
└── types/
    └── chat.ts
```

### TypeScript Types
```typescript
export type RoomType = 'command' | 'management' | 'group';

export interface ChatRoom {
  id: number;
  command_id: number | null;
  room_type: RoomType;
  name: string;
  description: string | null;
  is_active: boolean;
  member_count: number;
  unread_count: number;
  last_message: ChatMessage | null;
}

export interface ChatMessage {
  id: number;
  chat_room_id: number;
  sender_id: number;
  sender?: Officer;
  message_text: string;
  attachment_url: string | null;
  is_broadcast: boolean;
  created_at: string;
}

export interface ChatRoomMember {
  id: number;
  chat_room_id: number;
  officer_id: number;
  officer?: Officer;
  added_by: number | null;
  is_active: boolean;
  joined_at: string;
  left_at: string | null;
}
```

---

## 8. Staff Officer Admin Capabilities

Staff Officers get additional powers within their command's chat rooms:

| Capability | Scope |
|-----------|-------|
| Pin messages | Command room only |
| Broadcast messages | `is_broadcast = true` — highlighted |
| Remove members | Command room |
| Mute members | Temporary message suppression |
| View member activity | See joined_at, last active |

---

## 9. Offline Support

| Feature | Offline Behavior |
|---------|-----------------|
| View messages | Cached locally (SQLite via WatermelonDB or Realm) |
| Send messages | Queue in local storage, send when back online |
| Attachments | Download for offline viewing (encrypted storage) |
| Room list | Cached with last sync timestamp |

---

## 10. Test Checklist

- [ ] Auto-join command room on login
- [ ] Auto-join management room for AC+ officers
- [ ] Management room NOT visible for below-AC officers
- [ ] Create group with multiple members
- [ ] Send text message in real-time
- [ ] Receive message in real-time (WebSocket)
- [ ] Send file attachment (PDF, JPEG)
- [ ] Download/preview attachment
- [ ] Staff Officer can broadcast message
- [ ] Staff Officer can remove member from command room
- [ ] Leave group
- [ ] Unread count badge updates in real-time
- [ ] Search officers when creating group
- [ ] Message pagination (load older messages)
- [ ] Offline: view cached messages
- [ ] Offline: queued messages send when back online
