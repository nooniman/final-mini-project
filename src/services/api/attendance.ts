/**
 * Attendance API Service
 */

import { apiClient } from './client';
import { API_ENDPOINTS } from '../../constants/Config';
import type { 
  AttendanceRecord, 
  AttendanceStats, 
  AttendanceSummary,
  AttendanceFilter 
} from '../../types/attendance';
import type { ApiResponse } from '../../types/common';

export const attendanceApi = {
  /**
   * Get all attendance records with optional filters
   */
  getAll: async (filters?: AttendanceFilter): Promise<ApiResponse<AttendanceRecord[]>> => {
    const response = await apiClient.get<ApiResponse<AttendanceRecord[]>>(
      API_ENDPOINTS.ATTENDANCE.LIST,
      { params: filters }
    );
    return response.data;
  },

  /**
   * Get attendance for a specific subject
   */
  getBySubject: async (subjectId: string): Promise<ApiResponse<AttendanceRecord[]>> => {
    const response = await apiClient.get<ApiResponse<AttendanceRecord[]>>(
      API_ENDPOINTS.ATTENDANCE.BY_SUBJECT(subjectId)
    );
    return response.data;
  },

  /**
   * Get attendance summary and statistics
   */
  getSummary: async (): Promise<ApiResponse<AttendanceStats>> => {
    const response = await apiClient.get<ApiResponse<AttendanceStats>>(
      API_ENDPOINTS.ATTENDANCE.SUMMARY
    );
    return response.data;
  },
};

export default attendanceApi;
