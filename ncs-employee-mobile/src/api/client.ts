import axios, { AxiosInstance } from 'axios';
import { API_BASE_URL } from '../utils/constants';

export type GetToken = () => string | null;

let getToken: GetToken = () => null;
let onUnauthorized: (() => void) | null = null;

export function setAuthTokenGetter(fn: GetToken) {
  getToken = fn;
}

/** Call when 401 is received; app should clear token and redirect to login. */
export function setUnauthorizedHandler(fn: (() => void) | null) {
  onUnauthorized = fn;
}

export function createApiClient(): AxiosInstance {
  const client = axios.create({
    baseURL: API_BASE_URL,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-Platform': 'react-native',
    },
  });

  client.interceptors.request.use((config) => {
    const token = getToken();
    if (token) config.headers.Authorization = `Bearer ${token}`;
    return config;
  });

  client.interceptors.response.use(
    (r) => r,
    (err) => {
      if (err.response?.status === 401 && onUnauthorized) {
        onUnauthorized();
      }
      return Promise.reject(err);
    }
  );

  return client;
}

export const apiClient = createApiClient();
