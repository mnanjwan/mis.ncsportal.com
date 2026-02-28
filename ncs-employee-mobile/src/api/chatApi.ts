import { apiClient } from './client';

export type ChatRoomItem = {
  id: number;
  name: string;
  description?: string | null;
  room_type: 'command' | 'management' | 'group';
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
  message_text: string;
  attachment_url?: string | null;
  is_broadcast?: boolean;
  created_at: string;
  sender?: {
    id: number;
    full_name?: string;
    name?: string;
    service_number?: string;
    rank?: string;
  };
};

export type OfficerSearchResult = {
  id: number;
  name: string;
  service_number: string;
  rank?: string;
  command?: { id: number; name: string } | null;
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
  ): Promise<{ success: boolean; data?: ChatMessageItem[]; meta?: any }> {
    const { data } = await apiClient.get<{ success: boolean; data?: ChatMessageItem[]; meta?: any }>(
      `/chat/rooms/${roomId}/messages`,
      { params: { page, per_page: perPage } }
    );
    return data;
  },

  // Send a text message
  async sendMessage(
    roomId: number,
    message: string,
    isBroadcast = false
  ): Promise<{ success: boolean; data?: { id: number; message: string; created_at: string } }> {
    const { data } = await apiClient.post<{ success: boolean; data?: any }>(
      `/chat/rooms/${roomId}/messages`,
      { message, is_broadcast: isBroadcast }
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

  // Search officers for group creation
  async searchOfficers(query: string): Promise<{ success: boolean; data?: OfficerSearchResult[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: OfficerSearchResult[] }>(
      '/officers/search',
      { params: { q: query } }
    );
    return data;
  },

  // Sync auto-join rooms on login
  async syncRooms(): Promise<{ success: boolean; message?: string }> {
    const { data } = await apiClient.post<{ success: boolean; message?: string }>('/chat/sync');
    return data;
  },
};
