import { apiClient } from './client';
import type { ApiResponse } from './types';

export const officerApi = {
  async update(officerId: number, body: { phone_number?: string }): Promise<ApiResponse> {
    const { data } = await apiClient.patch<ApiResponse>(`/officers/${officerId}`, body);
    return data;
  },

  async updateProfilePicture(officerId: number, formData: FormData): Promise<ApiResponse<{ profile_picture_url: string }>> {
    const token = apiClient.defaults.headers.common['Authorization'];
    const { data } = await apiClient.post<ApiResponse<{ profile_picture_url: string }>>(
      `/officers/${officerId}/profile-picture`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
          ...(token && { Authorization: token }),
        },
      }
    );
    return data;
  },
};
