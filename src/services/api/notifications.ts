/**
 * Notifications API Service
 */

import { apiClient } from './client';
import { API_ENDPOINTS } from '../../constants/Config';
import type { Notification } from '../../types/notifications';
import type { ApiResponse, PaginationParams } from '../../types/common';

export const notificationsApi = {
  /**
   * Get all notifications
   */
  getAll: async (params?: PaginationParams): Promise<ApiResponse<Notification[]>> => {
    const response = await apiClient.get<ApiResponse<Notification[]>>(
      API_ENDPOINTS.NOTIFICATIONS.LIST,
      { params }
    );
    return response.data;
  },

  /**
   * Mark a notification as read
   */
  markAsRead: async (id: string): Promise<ApiResponse<null>> => {
    const response = await apiClient.post<ApiResponse<null>>(
      API_ENDPOINTS.NOTIFICATIONS.MARK_READ(id)
    );
    return response.data;
  },

  /**
   * Mark all notifications as read
   */
  markAllAsRead: async (): Promise<ApiResponse<null>> => {
    const response = await apiClient.post<ApiResponse<null>>(
      API_ENDPOINTS.NOTIFICATIONS.MARK_ALL_READ
    );
    return response.data;
  },
};

export default notificationsApi;
