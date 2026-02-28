/**
 * Secure token storage (expo-secure-store)
 */

import * as SecureStore from 'expo-secure-store';

const TOKEN_KEY = 'ncs_auth_token';
const USER_KEY = 'ncs_auth_user';
const BIOMETRIC_ENABLED_KEY = 'ncs_biometric_enabled';
const CREDS_KEY = 'ncs_saved_creds';

export const authStorage = {
  async getToken(): Promise<string | null> {
    return SecureStore.getItemAsync(TOKEN_KEY);
  },
  async setToken(token: string): Promise<void> {
    await SecureStore.setItemAsync(TOKEN_KEY, token);
  },
  async removeToken(): Promise<void> {
    await SecureStore.deleteItemAsync(TOKEN_KEY);
  },
  async getUser(): Promise<string | null> {
    return SecureStore.getItemAsync(USER_KEY);
  },
  async setUser(userJson: string): Promise<void> {
    await SecureStore.setItemAsync(USER_KEY, userJson);
  },
  async removeUser(): Promise<void> {
    await SecureStore.deleteItemAsync(USER_KEY);
  },
  async clear(): Promise<void> {
    await Promise.all([this.removeToken(), this.removeUser()]);
  },
  async getBiometricEnabled(): Promise<boolean> {
    const v = await SecureStore.getItemAsync(BIOMETRIC_ENABLED_KEY);
    return v === 'true';
  },
  async setBiometricEnabled(enabled: boolean): Promise<void> {
    await SecureStore.setItemAsync(BIOMETRIC_ENABLED_KEY, enabled ? 'true' : 'false');
  },
  async getCredentials(): Promise<string | null> {
    return SecureStore.getItemAsync(CREDS_KEY);
  },
  async setCredentials(credsJson: string): Promise<void> {
    await SecureStore.setItemAsync(CREDS_KEY, credsJson);
  },
  async removeCredentials(): Promise<void> {
    await SecureStore.deleteItemAsync(CREDS_KEY);
  },
};
