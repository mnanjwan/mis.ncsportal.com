import { apiClient } from './client';

export type QuarterRequestItem = {
  id: number;
  officer_id: number;
  quarter_id?: number | null;
  preferred_quarter_type?: string | null;
  status: string;
  quarter?: { id: number; quarter_number?: string; quarter_type?: string };
  created_at?: string;
};

export type OfficerQuarterItem = {
  id: number;
  officer_id: number;
  quarter_id: number;
  status: string;
  is_current?: boolean;
  quarter?: { id: number; quarter_number?: string; quarter_type?: string };
  allocated_at?: string;
  created_at?: string;
};

export const quarterApi = {
  async myRequests(): Promise<{ success: boolean; data?: QuarterRequestItem[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: QuarterRequestItem[] }>('/quarters/my-requests');
    return data;
  },

  async myAllocations(): Promise<{ success: boolean; data?: OfficerQuarterItem[] }> {
    const { data } = await apiClient.get<{ success: boolean; data?: OfficerQuarterItem[] }>('/quarters/my-allocations');
    return data;
  },

  async submitRequest(body: { quarter_id?: number; preferred_quarter_type?: string }): Promise<{ success: boolean; data?: { id: number; status: string } }> {
    const { data } = await apiClient.post<{ success: boolean; data?: { id: number; status: string } }>('/quarters/request', body);
    return data;
  },

  async acceptAllocation(id: number): Promise<{ success: boolean }> {
    const { data } = await apiClient.post<{ success: boolean }>(`/quarters/allocations/${id}/accept`, {});
    return data;
  },

  async rejectAllocation(id: number, body?: { reason?: string }): Promise<{ success: boolean }> {
    const { data } = await apiClient.post<{ success: boolean }>(`/quarters/allocations/${id}/reject`, body ?? {});
    return data;
  },
};
