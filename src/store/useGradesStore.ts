/**
 * Grades Store
 * Manages grades data with Zustand
 */

import { create } from 'zustand';
import { gradesApi } from '../services/api/grades';
import type { Grade, GWASummary, GradesFilter, GradesSummary } from '../types/grades';

interface GradesStore {
  // State
  grades: Grade[];
  gwaSummary: GWASummary | null;
  gradesSummary: GradesSummary | null;
  selectedSemesterId: string | null;
  isLoading: boolean;
  isRefreshing: boolean;
  error: string | null;

  // Actions
  fetchGrades: (filters?: GradesFilter) => Promise<void>;
  fetchGWA: (academicYear?: string) => Promise<void>;
  fetchSummary: (semesterId?: string) => Promise<void>;
  setSelectedSemester: (semesterId: string | null) => void;
  refreshGrades: () => Promise<void>;
  clearError: () => void;
  reset: () => void;
}

export const useGradesStore = create<GradesStore>((set, get) => ({
  // Initial state
  grades: [],
  gwaSummary: null,
  gradesSummary: null,
  selectedSemesterId: null,
  isLoading: false,
  isRefreshing: false,
  error: null,

  // Fetch grades
  fetchGrades: async (filters?: GradesFilter) => {
    set({ isLoading: true, error: null });
    try {
      const response = await gradesApi.getAll(filters);
      if (response.success) {
        set({ grades: response.data, isLoading: false });
      } else {
        set({ error: response.message, isLoading: false });
      }
    } catch (error: any) {
      set({ error: error.message || 'Failed to fetch grades', isLoading: false });
    }
  },

  // Fetch GWA summary
  fetchGWA: async (academicYear?: string) => {
    try {
      const response = await gradesApi.getGWA(academicYear);
      if (response.success) {
        set({ gwaSummary: response.data });
      }
    } catch (error: any) {
      console.error('Error fetching GWA:', error);
    }
  },

  // Fetch grades summary
  fetchSummary: async (semesterId?: string) => {
    try {
      const response = await gradesApi.getSummary(semesterId);
      if (response.success) {
        set({ gradesSummary: response.data });
      }
    } catch (error: any) {
      console.error('Error fetching grades summary:', error);
    }
  },

  // Set selected semester
  setSelectedSemester: (semesterId: string | null) => {
    set({ selectedSemesterId: semesterId });
    get().fetchGrades(semesterId ? { semesterId } : undefined);
  },

  // Refresh grades
  refreshGrades: async () => {
    set({ isRefreshing: true });
    const { selectedSemesterId } = get();
    try {
      await get().fetchGrades(selectedSemesterId ? { semesterId: selectedSemesterId } : undefined);
      await get().fetchGWA();
    } finally {
      set({ isRefreshing: false });
    }
  },

  // Clear error
  clearError: () => set({ error: null }),

  // Reset store
  reset: () =>
    set({
      grades: [],
      gwaSummary: null,
      gradesSummary: null,
      selectedSemesterId: null,
      isLoading: false,
      isRefreshing: false,
      error: null,
    }),
}));

export default useGradesStore;
