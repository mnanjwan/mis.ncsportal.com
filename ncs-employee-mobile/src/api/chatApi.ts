import { apiClient } from './client';

export type ChatRoomItem = {
  id: number;
  name: string;
  description?: string | null;
  room_type: 'command' | 'management' | 'group' | 'UNIT' | string;
  command_id?: number | null;
  is_active?: boolean;
  member_count?: number;
  unread_count?: number;
  last_message?: {
    id: number;
    message_text: string;
    created_at: string;
    sender_id?: number;
    sender?: { id: number; name?: string; rank?: string; initials?: string };
    attachment_url?: string | null;
  } | null;
};

export type ChatMessageItem = {
  id: number;
  chat_room_id: number;
  sender_id: number;
  parent_id?: number | null;
  message_text: string;
  attachment_url?: string | null;
  is_broadcast?: boolean;
  is_deleted?: boolean;
  is_pinned?: boolean;
  created_at: string;
  sender?: {
    id: number;
    full_name?: string;
    name?: string;
    service_number?: string;
    rank?: string;
  };
  parent?: {
    id: number;
    message_text: string;
    sender_name: string;
  } | null;
  reactions?: Record<string, number>;
  my_reaction?: string | null;
};

export type ChatRoomMessagesRes = {
  success: boolean;
  data: {
    messages: ChatMessageItem[];
    pinned_messages: {
      id: number;
      message_text: string;
      sender_name: string;
    }[];
    pagination: {
      current_page: number;
      per_page: number;
      total: number;
      last_page: number;
    };
  };
};

export type OfficerSearchResult = {
  id: number;
  surname: string;
  initials: string;
  full_name?: string;
  service_number: string;
  substantive_rank?: string;
  presentStation?: { id: number; name: string } | null;
};

export const chatApi = {
  // List all rooms the user is a member of
  async rooms(): Promise<{ success: boolean; data?: ChatRoomItem[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: ChatRoomItem[] }>('/chat/rooms');
    return data;
  },

  // Get room messages (paginated)
  async messages(
    roomId: number,
    page = 1,
    perPage = 50
  ): Promise<ChatRoomMessagesRes['data']> {
    const { data } = await apiClient.get<ChatRoomMessagesRes>(
      `/chat/rooms/${roomId}/messages`,
      { params: { page, per_page: perPage } }
    );
    return data.data;
  },

  // Send a text message
  async sendMessage(
    roomId: number,
    message: string,
    parentId?: number,
    isBroadcast = false
  ): Promise<{ success: boolean; data?: any }> {
    const { data } = await apiClient.post<{ success: boolean; data?: any }>(
      `/chat/rooms/${roomId}/messages`,
      { message, parent_id: parentId, is_broadcast: isBroadcast }
    );
    return data;
  },

  // Send attachment (multipart)
  async sendAttachment(
    roomId: number,
    formData: FormData
  ): Promise<{ success: boolean; data?: any }> {
    const { data } = await apiClient.post<{ success: boolean; data?: any }>(
      `/chat/rooms/${roomId}/messages/attachment`,
      formData,
      { headers: { 'Content-Type': 'multipart/form-data' } }
    );
    return data;
  },

  // Create a group chat room
  async createGroup(payload: {
    name: string;
    description?: string;
    member_ids: number[];
  }): Promise<{ success: boolean; data?: ChatRoomItem; message?: string }> {
    const { data } = await apiClient.post<{ success: boolean; data?: ChatRoomItem; message?: string }>(
      '/chat/rooms',
      { ...payload, room_type: 'group' }
    );
    return data;
  },

  // Search ALL officers system-wide for DM creation (no role filtering)
  async searchOfficers(query: string): Promise<{ success: boolean; data?: OfficerSearchResult[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: OfficerSearchResult[] }>(
      '/chat/officers/search',
      { params: { search: query, per_page: 50 } }
    );
    return data;
  },

  // Sync auto-join rooms on login
  async syncRooms(): Promise<{ success: boolean; message?: string }> {
    const { data } = await apiClient.post<{ success: boolean; message?: string }>('/chat/sync');
    return data;
  },

  // List members of a chat room
  async members(roomId: number): Promise<{ success: boolean; data?: any[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: any[] }>(`/chat/rooms/${roomId}/members`);
    return data;
  },

  // Add members to a room
  async addMembers(roomId: number, officerIds: number[]): Promise<{ success: boolean }> {
    const { data } = await apiClient.post<{ success: boolean }>(`/chat/rooms/${roomId}/members`, { officer_ids: officerIds });
    return data;
  },

  // Remove member from a room
  async removeMember(roomId: number, userId: number): Promise<{ success: boolean; message?: string }> {
    const { data } = await apiClient.delete<{ success: boolean; message?: string }>(`/chat/rooms/${roomId}/members/${userId}`);
    return data;
  },

  // Mark all messages in a room as read
  async markRoomRead(roomId: number): Promise<{ success: boolean }> {
    const { data } = await apiClient.post<{ success: boolean }>(`/chat/rooms/${roomId}/mark-read`, {});
    return data;
  },

  // Toggle pinning on a message
  async togglePin(roomId: number, messageId: number): Promise<{ success: boolean; is_pinned: boolean }> {
    const { data } = await apiClient.post<{ success: boolean; is_pinned: boolean }>(`/chat/rooms/${roomId}/messages/${messageId}/pin`);
    return data;
  },

  // Toggle broadcast status on a message (Staff Officer)
  async toggleBroadcast(roomId: number, messageId: number): Promise<{ success: boolean; is_broadcast: boolean }> {
    const { data } = await apiClient.post<{ success: boolean; is_broadcast: boolean }>(`/chat/rooms/${roomId}/messages/${messageId}/broadcast`);
    return data;
  },

  // Get message info/read receipts
  async getMessageInfo(roomId: number, messageId: number): Promise<{ success: boolean; data: { read_by: any[], total_readers: number } }> {
    const { data } = await apiClient.get<{ success: boolean; data: { read_by: any[], total_readers: number } }>(`/chat/rooms/${roomId}/messages/${messageId}/info`);
    return data;
  },

  // Delete a message
  async deleteMessage(roomId: number, messageId: number): Promise<{ success: boolean; message?: string }> {
    const { data } = await apiClient.delete<{ success: boolean; message?: string }>(`/chat/rooms/${roomId}/messages/${messageId}`);
    return data;
  },

  // Toggle reaction on a message
  async toggleReaction(roomId: number, messageId: number, reaction: string): Promise<{ success: boolean; message?: string }> {
    const { data } = await apiClient.post<{ success: boolean; message?: string }>(
      `/chat/rooms/${roomId}/messages/${messageId}/react`,
      { reaction }
    );
    return data;
  },
};
