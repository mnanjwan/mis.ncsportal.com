import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { authStorage } from './authStorage';
import { authApi } from '../api/authApi';

export type User = {
  id: number;
  email: string;
  roles: string[];
  officer: {
    id: number;
    service_number: string;
    name: string;
    rank: string;
    command: { id: number; name: string } | null;
    phone_number?: string | null;
    profile_picture_url?: string | null;
    bank_name?: string | null;
    bank_account_number?: string | null;
    pfa_name?: string | null;
    rsa_number?: string | null;
  } | null;
};

type AuthState = {
  token: string | null;
  user: User | null;
  temporaryToken: string | null;
  biometricRequired: boolean;
  biometricUnlocked: boolean;
  isLoading: boolean;
  isRestoring: boolean;
  error: string | null;
};

const initialState: AuthState = {
  token: null,
  user: null,
  temporaryToken: null,
  biometricRequired: false,
  biometricUnlocked: false,
  isLoading: false,
  isRestoring: true,
  error: null,
};

export const restoreSession = createAsyncThunk(
  'auth/restoreSession',
  async (_, { rejectWithValue }) => {
    const token = await authStorage.getToken();
    if (!token) return rejectWithValue('no_token');
    const userJson = await authStorage.getUser();
    if (!userJson) return rejectWithValue('no_user');
    const biometricRequired = await authStorage.getBiometricEnabled();
    try {
      const user = JSON.parse(userJson) as User;
      return { token, user, biometricRequired };
    } catch {
      return rejectWithValue('invalid_user');
    }
  }
);

type LoginData = {
  token?: string;
  user: User | { id: number; email: string };
  requires_two_factor?: boolean;
  temporary_token?: string;
};

export const login = createAsyncThunk(
  'auth/login',
  async (
    args: { identifier: string; password: string; push_token?: string; rememberMe?: boolean },
    { rejectWithValue }
  ) => {
    const res = await authApi.login(
      args.identifier,
      args.password,
      args.push_token
    );
    if (!res.success || !res.data) return rejectWithValue(res.message ?? 'Login failed');
    const data = res.data as LoginData;
    const { user } = data;
    if (!user) return rejectWithValue('Invalid response');
    if (data.requires_two_factor && data.temporary_token) {
      return { requiresTwoFactor: true, temporaryToken: data.temporary_token, user: { id: user.id, email: user.email, roles: [], officer: null } };
    }
    const { token } = data;
    if (!token) return rejectWithValue('Invalid response');
    await authStorage.setToken(token);
    await authStorage.setUser(JSON.stringify(user as User));

    if (args.rememberMe) {
      await authStorage.setCredentials(JSON.stringify({ identifier: args.identifier, password: args.password }));
    } else {
      await authStorage.removeCredentials();
    }

    return { token, user: user as User };
  }
);

export const verifyTwoFactor = createAsyncThunk(
  'auth/verifyTwoFactor',
  async (
    args: {
      temporary_token: string;
      code?: string;
      recovery_code?: string;
      push_token?: string;
    },
    { rejectWithValue }
  ) => {
    const res = await authApi.verifyTwoFactor(args.temporary_token, {
      code: args.code,
      recovery_code: args.recovery_code,
      push_token: args.push_token,
    });
    if (!res.success || !res.data) return rejectWithValue((res as { message?: string }).message ?? 'Verification failed');
    const { token, user } = res.data as { token: string; user: User };
    if (!token || !user) return rejectWithValue('Invalid response');
    await authStorage.setToken(token);
    await authStorage.setUser(JSON.stringify(user));
    return { token, user };
  }
);

export const logout = createAsyncThunk(
  'auth/logout',
  async (_, { getState }) => {
    const state = getState() as { auth: AuthState };
    if (state.auth.token) {
      try {
        await authApi.logout(state.auth.token);
      } catch {
        // ignore
      }
    }
    await authStorage.clear();
    await authStorage.removeCredentials();
  }
);

export const cancelTwoFactor = createAsyncThunk('auth/cancelTwoFactor', async () => { });

export const refreshUser = createAsyncThunk(
  'auth/refreshUser',
  async (_, { rejectWithValue }) => {
    const res = await authApi.me();
    if (!res.success || !res.data) return rejectWithValue(res.message ?? 'Failed to load user');
    const user = (res.data as { user: User }).user;
    if (!user) return rejectWithValue('Invalid user');
    await authStorage.setUser(JSON.stringify(user));
    return user;
  }
);

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearError: (state) => {
      state.error = null;
    },
    clearTwoFactor: (state) => {
      state.temporaryToken = null;
      state.user = null;
    },
    setBiometricUnlocked: (state) => {
      state.biometricUnlocked = true;
    },
  },
  extraReducers: (builder) => {
    builder
      // restoreSession
      .addCase(restoreSession.pending, (state) => {
        state.isRestoring = true;
      })
      .addCase(restoreSession.fulfilled, (state, action) => {
        state.token = action.payload.token;
        state.user = action.payload.user;
        state.biometricRequired = (action.payload as { biometricRequired?: boolean }).biometricRequired ?? false;
        state.biometricUnlocked = !state.biometricRequired;
        state.isRestoring = false;
        state.error = null;
      })
      .addCase(restoreSession.rejected, (state) => {
        state.token = null;
        state.user = null;
        state.isRestoring = false;
      })
      // login
      .addCase(login.pending, (state) => {
        state.isLoading = true;
        state.error = null;
      })
      .addCase(login.fulfilled, (state, action) => {
        const p = action.payload as { token?: string; user: User; requiresTwoFactor?: boolean; temporaryToken?: string };
        if (p.requiresTwoFactor && p.temporaryToken) {
          state.temporaryToken = p.temporaryToken;
          state.user = p.user as User;
          state.token = null;
        } else {
          state.token = p.token ?? null;
          state.user = p.user;
          state.temporaryToken = null;
        }
        state.isLoading = false;
        state.error = null;
      })
      .addCase(login.rejected, (state, action) => {
        state.isLoading = false;
        state.error = (action.payload as string) ?? action.error.message ?? 'Login failed';
      })
      // logout
      .addCase(logout.fulfilled, (state) => {
        state.token = null;
        state.user = null;
        state.temporaryToken = null;
        state.biometricRequired = false;
        state.biometricUnlocked = false;
      })
      .addCase(verifyTwoFactor.fulfilled, (state, action) => {
        state.token = action.payload.token;
        state.user = action.payload.user;
        state.temporaryToken = null;
        state.error = null;
      })
      .addCase(verifyTwoFactor.rejected, (state, action) => {
        state.error = (action.payload as string) ?? action.error.message ?? 'Verification failed';
      })
      .addCase(cancelTwoFactor.fulfilled, (state) => {
        state.temporaryToken = null;
        state.user = null;
      })
      // refreshUser
      .addCase(refreshUser.fulfilled, (state, action) => {
        state.user = action.payload;
      });
  },
});

export const { clearError, clearTwoFactor, setBiometricUnlocked } = authSlice.actions;
export default authSlice.reducer;
