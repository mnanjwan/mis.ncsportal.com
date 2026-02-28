import axios from 'axios';
import { API_BASE_URL } from '../utils/constants';
import { apiClient } from './client';

export type LoginResponse = {
  success: boolean;
  data?: {
    token?: string;
    user: {
      id: number;
      email: string;
      roles?: string[];
      officer?: {
        id: number;
        service_number: string;
        name: string;
        rank: string;
        command: { id: number; name: string } | null;
      } | null;
    };
    requires_two_factor?: boolean;
    temporary_token?: string;
  };
  message?: string;
};

export type ApiResponse<T = unknown> = {
  success: boolean;
  data?: T;
  message?: string;
};

export const authApi = {
  async login(
    identifier: string,
    password: string,
    push_token?: string
  ): Promise<LoginResponse> {
    const isEmail = identifier.includes('@');
    const payload = {
      ...(isEmail ? { email: identifier } : { service_number: identifier }),
      password,
      ...(push_token && { push_token }),
    };
    const { data } = await apiClient.post<LoginResponse>('/auth/login', payload);
    return data;
  },

  async logout(token: string): Promise<ApiResponse> {
    const { data } = await apiClient.post<ApiResponse>(
      '/auth/logout',
      {},
      { headers: { Authorization: `Bearer ${token}` } }
    );
    return data;
  },

  async me(): Promise<ApiResponse<{ user: unknown }>> {
    const { data } = await apiClient.get<ApiResponse<{ user: unknown }>>(
      '/auth/me'
    );
    return data;
  },

  async registerPushToken(token: string): Promise<ApiResponse> {
    const { data } = await apiClient.post<ApiResponse>(
      '/notifications/register-token',
      { token }
    );
    return data;
  },

  /** Exchange temporary 2FA token + code for full auth token. Uses temporary_token as Bearer. */
  async verifyTwoFactor(
    temporaryToken: string,
    body: { code?: string; recovery_code?: string; push_token?: string }
  ): Promise<ApiResponse<{ token: string; user: unknown }>> {
    const { data } = await axios.post<ApiResponse<{ token: string; user: unknown }>>(
      `${API_BASE_URL}/auth/two-factor/verify`,
      body,
      {
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${temporaryToken}`,
          'X-Platform': 'react-native',
        },
      }
    );
    return data;
  },
};
