import { apiClient } from './client';

export type MovementOrderItem = {
  id: number;
  order_number: string;
  officer_id: number;
  from_command_id: number;
  to_command_id: number;
  effective_date: string;
  order_type?: string;
  reason?: string | null;
  officer?: { id: number; service_number?: string; full_name?: string };
  from_command?: { id: number; name: string };
  to_command?: { id: number; name: string };
  created_at?: string;
};

export type PaginatedMovementOrders = {
  success: boolean;
  data?: MovementOrderItem[];
  meta?: { current_page: number; per_page: number; total: number; last_page: number };
};

export const movementApi = {
  async list(params?: { officer_id?: number; per_page?: number; page?: number }): Promise<PaginatedMovementOrders> {
    const { data } = await apiClient.get<PaginatedMovementOrders>('/movement-orders', { params: params ?? {} });
    return data;
  },

  async show(id: number): Promise<{ success: boolean; data?: MovementOrderItem }> {
    const { data } = await apiClient.get<{ success: boolean; data?: MovementOrderItem }>(`/movement-orders/${id}`);
    return data;
  },
};
