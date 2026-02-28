import { apiClient } from './client';
import type { ApiResponse, EmolumentItem, EmolumentTimelineItem } from './types';

export const emolumentApi = {
  async myEmoluments(): Promise<ApiResponse<EmolumentItem[]>> {
    const { data } = await apiClient.get<ApiResponse<EmolumentItem[]>>('/emoluments/my-emoluments');
    return data;
  },

  async get(id: number): Promise<ApiResponse<EmolumentItem>> {
    const { data } = await apiClient.get<ApiResponse<EmolumentItem>>(`/emoluments/${id}`);
    return data;
  },

  async getActiveTimeline(): Promise<ApiResponse<EmolumentTimelineItem>> {
    const { data } = await apiClient.get<ApiResponse<EmolumentTimelineItem>>('/emolument-timelines/active');
    return data;
  },

  async raise(body: {
    timeline_id: number;
    bank_name: string;
    bank_account_number: string;
    pfa_name: string;
    rsa_pin: string;
    notes?: string;
  }): Promise<ApiResponse<{ id: number; status: string }>> {
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>('/emoluments', body);
    return data;
  },

  async resubmit(id: number): Promise<ApiResponse<{ id: number; status: string }>> {
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>(`/emoluments/${id}/resubmit`);
    return data;
  },
};
