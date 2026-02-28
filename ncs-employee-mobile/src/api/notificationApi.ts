import { apiClient } from './client';

export type NotificationItem = {
  id: number;
  user_id: number;
  notification_type: string;
  title: string | null;
  message: string | null;
  entity_type: string | null;
  entity_id: number | null;
  is_read: boolean;
  read_at: string | null;
  created_at: string;
};

export type PaginatedNotifications = {
  success: boolean;
  data?: NotificationItem[];
  meta?: { current_page: number; per_page: number; total: number; last_page: number };
};

export const notificationApi = {
  async list(params?: { per_page?: number; page?: number; is_read?: boolean }): Promise<PaginatedNotifications> {
    const { data } = await apiClient.get<PaginatedNotifications>('/notifications', { params: params ?? {} });
    return data;
  },

  async markAsRead(id: number): Promise<{ success: boolean }> {
    const { data } = await apiClient.patch<{ success: boolean }>(`/notifications/${id}/read`, {});
    return data;
  },

  async markAllAsRead(): Promise<{ success: boolean }> {
    const { data } = await apiClient.patch<{ success: boolean }>('/notifications/read-all', {});
    return data;
  },
};
