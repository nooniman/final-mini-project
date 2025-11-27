/**
 * Authentication API Service
 */

import { apiClient } from './client';
import { API_ENDPOINTS, API_CONFIG } from '../../constants/Config';
import type { 
  LoginCredentials, 
  LoginResponse, 
  User, 
  ChangePasswordRequest 
} from '../../types/auth';
import type { ApiResponse } from '../../types/common';

export const authApi = {
  /**
   * Login with student credentials
   */
  login: async (credentials: LoginCredentials): Promise<LoginResponse> => {
    const url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.AUTH.LOGIN}`;
    console.log('🔐 Attempting login to:', url);
    console.log('📦 Request body:', { student_id: credentials.studentId, password: '***' });
    
    try {
      // Use fetch as a fallback since Expo Go may have axios issues with HTTP
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          student_id: credentials.studentId,
          password: credentials.password,
        }),
      });
      
      console.log('📥 Response status:', response.status);
      const data = await response.json();
      console.log('✅ Login response data:', JSON.stringify(data, null, 2));
      
      return data as LoginResponse;
    } catch (error: any) {
      console.error('❌ Login error:', error);
      console.error('❌ Error message:', error.message);
      console.error('❌ Error details:', JSON.stringify(error, null, 2));
      throw {
        success: false,
        message: error.message || 'Network error. Make sure you are connected to the same WiFi as the server.',
        code: 'ERR_NETWORK',
      };
    }
  },

  /**
   * Logout current user
   */
  logout: async (): Promise<ApiResponse<null>> => {
    const response = await apiClient.post<ApiResponse<null>>(
      API_ENDPOINTS.AUTH.LOGOUT
    );
    return response.data;
  },

  /**
   * Get current user profile
   */
  getProfile: async (): Promise<ApiResponse<User>> => {
    const response = await apiClient.get<ApiResponse<User>>(
      API_ENDPOINTS.AUTH.PROFILE
    );
    return response.data;
  },

  /**
   * Refresh authentication token
   */
  refreshToken: async (refreshToken: string): Promise<LoginResponse> => {
    const response = await apiClient.post<LoginResponse>(
      API_ENDPOINTS.AUTH.REFRESH,
      { refreshToken }
    );
    return response.data;
  },

  /**
   * Change user password
   */
  changePassword: async (data: ChangePasswordRequest): Promise<ApiResponse<null>> => {
    const response = await apiClient.post<ApiResponse<null>>(
      API_ENDPOINTS.AUTH.CHANGE_PASSWORD,
      data
    );
    return response.data;
  },
};

export default authApi;
