import { apiClient } from './client';
import type { ApiResponse, PassApplicationItem } from './types';

export const passApi = {
  async list(params?: { per_page?: number; status?: string }): Promise<ApiResponse<PassApplicationItem[]>> {
    const { data } = await apiClient.get<ApiResponse<PassApplicationItem[]>>('/pass-applications', { params });
    return data;
  },

  async get(id: number): Promise<ApiResponse<PassApplicationItem>> {
    const { data } = await apiClient.get<ApiResponse<PassApplicationItem>>(`/pass-applications/${id}`);
    return data;
  },

  async apply(
    officerId: number,
    body: FormData | { start_date: string; end_date: string; reason?: string }
  ): Promise<ApiResponse<{ id: number; status: string }>> {
    const isFormData = body instanceof FormData;
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>(
      `/officers/${officerId}/pass-applications`,
      body,
      isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : undefined
    );
    return data;
  },

  /** DC Admin: approve or reject pass (status must be MINUTED) */
  async approve(id: number, body: { action: 'approve' | 'reject'; comments?: string }): Promise<ApiResponse<{ id: number; status: string }>> {
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>(
      `/pass-applications/${id}/approve`,
      body
    );
    return data;
  },
};
