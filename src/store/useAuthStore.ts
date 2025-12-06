/**
 * Authentication Store
 * Manages user authentication state with Zustand
 */

import { create } from 'zustand';
import { persist, createJSONStorage } from 'zustand/middleware';
import * as SecureStore from 'expo-secure-store';
import { authApi } from '../services/api/auth';
import { STORAGE_KEYS } from '../constants/Config';
import type { User, AuthTokens, LoginCredentials } from '../types/auth';

// Secure storage adapter for Zustand persist
const secureStorage = {
  getItem: async (name: string): Promise<string | null> => {
    try {
      return await SecureStore.getItemAsync(name);
    } catch (error) {
      console.error('SecureStore getItem error:', error);
      return null;
    }
  },
  setItem: async (name: string, value: string): Promise<void> => {
    try {
      await SecureStore.setItemAsync(name, value);
    } catch (error) {
      console.error('SecureStore setItem error:', error);
    }
  },
  removeItem: async (name: string): Promise<void> => {
    try {
      await SecureStore.deleteItemAsync(name);
    } catch (error) {
      console.error('SecureStore removeItem error:', error);
    }
  },
};

interface AuthStore {
  // State
  user: User | null;
  tokens: AuthTokens | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  isInitialized: boolean;
  error: string | null;

  // Actions
  login: (credentials: LoginCredentials) => Promise<boolean>;
  logout: () => Promise<void>;
  refreshUser: () => Promise<void>;
  setUser: (user: User) => void;
  clearError: () => void;
  initialize: () => Promise<void>;
}

export const useAuthStore = create<AuthStore>()(
  persist(
    (set, get) => ({
      // Initial state
      user: null,
      tokens: null,
      isAuthenticated: false,
      isLoading: false,
      isInitialized: false,
      error: null,

      // Login action
      login: async (credentials: LoginCredentials) => {
        set({ isLoading: true, error: null });
        try {
          const response = await authApi.login(credentials);
          
          if (response.success && response.data) {
            // PHP backend returns: { user, accessToken, refreshToken, expiresIn }
            const { user: rawUser, accessToken, refreshToken } = response.data as any;
            
            console.log('🔍 Raw user from backend:', JSON.stringify(rawUser, null, 2));
            
            // Construct fullName if not provided
            const firstName = rawUser.first_name || rawUser.firstName || '';
            const middleName = rawUser.middle_name || rawUser.middleName || '';
            const lastName = rawUser.last_name || rawUser.lastName || '';
            const fullName = rawUser.fullName || 
              `${firstName} ${middleName} ${lastName}`.trim().replace(/\s+/g, ' ');
            
            // Normalize user data from snake_case to camelCase
            const user: User = {
              id: rawUser.id?.toString() || '',
              studentId: rawUser.student_id || rawUser.studentId || '',
              email: rawUser.email || '',
              firstName: firstName,
              lastName: lastName,
              middleName: middleName,
              fullName: fullName,
              program: rawUser.program,
              programCode: rawUser.program_code || rawUser.programCode,
              department: rawUser.department,
              college: rawUser.college,
              yearLevel: rawUser.year_level || rawUser.yearLevel,
              section: rawUser.section,
              status: rawUser.status || 'active',
              enrollmentDate: rawUser.enrollment_date || rawUser.enrollmentDate,
              createdAt: rawUser.created_at || rawUser.createdAt,
              updatedAt: rawUser.updated_at || rawUser.updatedAt,
            };
            
            console.log('✅ Normalized user:', JSON.stringify(user, null, 2));
            
            const tokens = {
              accessToken,
              refreshToken,
            };
            
            // Store tokens securely
            await SecureStore.setItemAsync(STORAGE_KEYS.AUTH_TOKEN, accessToken);
            await SecureStore.setItemAsync(STORAGE_KEYS.REFRESH_TOKEN, refreshToken);
            
            set({
              user,
              tokens,
              isAuthenticated: true,
              isLoading: false,
              error: null,
            });
            
            return true;
          } else {
            set({
              isLoading: false,
              error: response.message || 'Login failed',
            });
            return false;
          }
        } catch (error: any) {
          const message = error.response?.data?.message || error.message || 'Network error. Check your connection.';
          set({
            isLoading: false,
            error: message,
          });
          return false;
        }
      },

      // Logout action
      logout: async () => {
        set({ isLoading: true });
        try {
          await authApi.logout();
        } catch (error) {
          // Continue with logout even if API call fails
          console.error('Logout API error:', error);
        } finally {
          // Clear secure storage
          await SecureStore.deleteItemAsync(STORAGE_KEYS.AUTH_TOKEN);
          await SecureStore.deleteItemAsync(STORAGE_KEYS.REFRESH_TOKEN);
          
          set({
            user: null,
            tokens: null,
            isAuthenticated: false,
            isLoading: false,
            error: null,
          });
        }
      },

      // Refresh user data
      refreshUser: async () => {
        try {
          const response = await authApi.getProfile();
          if (response.success) {
            set({ user: response.data });
          }
        } catch (error: any) {
          console.error('Error refreshing user:', error);
        }
      },

      // Set user directly
      setUser: (user: User) => {
        set({ user });
      },

      // Clear error
      clearError: () => {
        set({ error: null });
      },

      // Initialize auth state
      initialize: async () => {
        try {
          const token = await SecureStore.getItemAsync(STORAGE_KEYS.AUTH_TOKEN);
          if (token) {
            // Verify token by fetching profile
            const response = await authApi.getProfile();
            if (response.success) {
              set({
                user: response.data,
                isAuthenticated: true,
                isInitialized: true,
              });
              return;
            }
          }
        } catch (error) {
          console.error('Error initializing auth:', error);
          // Clear invalid tokens
          await SecureStore.deleteItemAsync(STORAGE_KEYS.AUTH_TOKEN);
          await SecureStore.deleteItemAsync(STORAGE_KEYS.REFRESH_TOKEN);
        }
        
        set({ isInitialized: true });
      },
    }),
    {
      name: 'auth-storage',
      storage: createJSONStorage(() => secureStorage),
      partialize: (state) => ({
        user: state.user,
        isAuthenticated: state.isAuthenticated,
      }),
    }
  )
);

export default useAuthStore;
