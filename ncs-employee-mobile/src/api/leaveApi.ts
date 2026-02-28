import { apiClient } from './client';
import type { ApiResponse, LeaveApplicationItem, LeaveTypeItem } from './types';

export const leaveApi = {
  async list(params?: { per_page?: number; status?: string }): Promise<ApiResponse<LeaveApplicationItem[]>> {
    const { data } = await apiClient.get<ApiResponse<LeaveApplicationItem[]>>('/leave-applications', { params });
    return data;
  },

  async get(id: number): Promise<ApiResponse<LeaveApplicationItem>> {
    const { data } = await apiClient.get<ApiResponse<LeaveApplicationItem>>(`/leave-applications/${id}`);
    return data;
  },

  async getLeaveTypes(): Promise<ApiResponse<LeaveTypeItem[]>> {
    const { data } = await apiClient.get<ApiResponse<LeaveTypeItem[]>>('/leave-types');
    return data;
  },

  async apply(
    officerId: number,
    body: FormData | {
      leave_type_id: number;
      start_date: string;
      end_date: string;
      reason?: string;
      expected_date_of_delivery?: string;
    }
  ): Promise<ApiResponse<{ id: number; status: string }>> {
    const isFormData = body instanceof FormData;
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>(
      `/officers/${officerId}/leave-applications`,
      body,
      isFormData ? { headers: { 'Content-Type': 'multipart/form-data' } } : undefined
    );
    return data;
  },

  /** Staff Officer: minute leave (status must be PENDING) */
  async minute(id: number): Promise<ApiResponse<{ id: number; status: string }>> {
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>(
      `/leave-applications/${id}/minute`,
      {}
    );
    return data;
  },

  /** DC Admin: approve or reject leave (status must be MINUTED) */
  async approve(id: number, body: { action: 'approve' | 'reject'; comments?: string }): Promise<ApiResponse<{ id: number; status: string }>> {
    const { data } = await apiClient.post<ApiResponse<{ id: number; status: string }>>(
      `/leave-applications/${id}/approve`,
      body
    );
    return data;
  },
};
