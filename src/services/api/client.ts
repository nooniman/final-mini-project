/**
 * API Client
 * Axios-based HTTP client with authentication interceptors
 */

import axios, { AxiosError, AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import * as SecureStore from 'expo-secure-store';
import { API_CONFIG, STORAGE_KEYS } from '../../constants';
import { ApiError } from '../../types';

console.log('🌐 API Base URL:', API_CONFIG.BASE_URL);

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: API_CONFIG.BASE_URL,
  timeout: API_CONFIG.TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Request interceptor - attach auth token and log requests
apiClient.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    console.log(`📤 ${config.method?.toUpperCase()} ${config.baseURL}${config.url}`);
    try {
      const token = await SecureStore.getItemAsync(STORAGE_KEYS.AUTH_TOKEN);
      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    } catch (error) {
      console.error('Error getting auth token:', error);
    }
    return config;
  },
  (error) => {
    console.error('📤 Request error:', error);
    return Promise.reject(error);
  }
);

// Response interceptor - handle errors and token refresh
apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError<ApiError>) => {
    const originalRequest = error.config;

    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      try {
        // Clear tokens on auth failure
        await SecureStore.deleteItemAsync(STORAGE_KEYS.AUTH_TOKEN);
        await SecureStore.deleteItemAsync(STORAGE_KEYS.REFRESH_TOKEN);
        
        // You could implement token refresh logic here
        // For now, we just reject and let the auth context handle logout
      } catch (e) {
        console.error('Error clearing tokens:', e);
      }
    }

    // Format error response
    const apiError: ApiError = {
      success: false,
      message: error.response?.data?.message || error.message || 'An error occurred',
      errors: error.response?.data?.errors,
      code: error.code,
    };

    return Promise.reject(apiError);
  }
);

export { apiClient };
export default apiClient;
