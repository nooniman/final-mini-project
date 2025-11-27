/**
 * Students API Service
 */

import { apiClient } from './client';
import { API_ENDPOINTS } from '../../constants/Config';
import type { User } from '../../types/auth';
import type { ApiResponse } from '../../types/common';

export const studentsApi = {
  /**
   * Get current student info
   */
  getInfo: async (): Promise<ApiResponse<User>> => {
    const response = await apiClient.get<ApiResponse<User>>(
      API_ENDPOINTS.STUDENTS.INFO
    );
    return response.data;
  },

  /**
   * Update student profile
   */
  updateProfile: async (data: Partial<User>): Promise<ApiResponse<User>> => {
    const response = await apiClient.put<ApiResponse<User>>(
      API_ENDPOINTS.STUDENTS.UPDATE_PROFILE,
      data
    );
    return response.data;
  },
};

export default studentsApi;
