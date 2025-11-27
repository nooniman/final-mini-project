/**
 * Attendance Store
 * Manages attendance/monitoring data with Zustand
 */

import { create } from 'zustand';
import { attendanceApi } from '../services/api/attendance';
import type { AttendanceRecord, AttendanceStats, AttendanceFilter } from '../types/attendance';

interface AttendanceStore {
  // State
  records: AttendanceRecord[];
  stats: AttendanceStats | null;
  isLoading: boolean;
  isRefreshing: boolean;
  error: string | null;

  // Actions
  fetchRecords: (filters?: AttendanceFilter) => Promise<void>;
  fetchStats: () => Promise<void>;
  refreshAttendance: () => Promise<void>;
  clearError: () => void;
  reset: () => void;
}

export const useAttendanceStore = create<AttendanceStore>((set, get) => ({
  // Initial state
  records: [],
  stats: null,
  isLoading: false,
  isRefreshing: false,
  error: null,

  // Fetch attendance records
  fetchRecords: async (filters?: AttendanceFilter) => {
    set({ isLoading: true, error: null });
    try {
      const response = await attendanceApi.getAll(filters);
      if (response.success) {
        set({ records: response.data, isLoading: false });
      } else {
        set({ error: response.message, isLoading: false });
      }
    } catch (error: any) {
      set({ error: error.message || 'Failed to fetch attendance', isLoading: false });
    }
  },

  // Fetch attendance stats
  fetchStats: async () => {
    try {
      const response = await attendanceApi.getSummary();
      if (response.success) {
        set({ stats: response.data });
      }
    } catch (error: any) {
      console.error('Error fetching attendance stats:', error);
    }
  },

  // Refresh attendance
  refreshAttendance: async () => {
    set({ isRefreshing: true });
    try {
      await Promise.all([get().fetchRecords(), get().fetchStats()]);
    } finally {
      set({ isRefreshing: false });
    }
  },

  // Clear error
  clearError: () => set({ error: null }),

  // Reset store
  reset: () =>
    set({
      records: [],
      stats: null,
      isLoading: false,
      isRefreshing: false,
      error: null,
    }),
}));

export default useAttendanceStore;
