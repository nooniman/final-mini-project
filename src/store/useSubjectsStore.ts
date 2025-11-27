/**
 * Subjects Store
 * Manages subjects and schedule data with Zustand
 */

import { create } from 'zustand';
import { subjectsApi } from '../services/api/subjects';
import type { Subject, Semester, SubjectsFilter } from '../types/subjects';

interface SubjectsStore {
  // State
  subjects: Subject[];
  semesters: Semester[];
  currentSemester: Semester | null;
  selectedSemesterId: string | null;
  isLoading: boolean;
  isRefreshing: boolean;
  error: string | null;

  // Actions
  fetchSubjects: (filters?: SubjectsFilter) => Promise<void>;
  fetchSemesters: () => Promise<void>;
  fetchCurrentSemester: () => Promise<void>;
  setSelectedSemester: (semesterId: string | null) => void;
  refreshSubjects: () => Promise<void>;
  clearError: () => void;
  reset: () => void;
}

export const useSubjectsStore = create<SubjectsStore>((set, get) => ({
  // Initial state
  subjects: [],
  semesters: [],
  currentSemester: null,
  selectedSemesterId: null,
  isLoading: false,
  isRefreshing: false,
  error: null,

  // Fetch subjects
  fetchSubjects: async (filters?: SubjectsFilter) => {
    set({ isLoading: true, error: null });
    try {
      const response = await subjectsApi.getAll(filters);
      if (response.success) {
        set({ subjects: response.data, isLoading: false });
      } else {
        set({ error: response.message, isLoading: false });
      }
    } catch (error: any) {
      set({ error: error.message || 'Failed to fetch subjects', isLoading: false });
    }
  },

  // Fetch semesters
  fetchSemesters: async () => {
    try {
      const response = await subjectsApi.getSemesters();
      if (response.success) {
        set({ semesters: response.data });
      }
    } catch (error: any) {
      console.error('Error fetching semesters:', error);
    }
  },

  // Fetch current semester
  fetchCurrentSemester: async () => {
    try {
      const response = await subjectsApi.getCurrentSemester();
      if (response.success) {
        set({ currentSemester: response.data, selectedSemesterId: response.data.id });
      }
    } catch (error: any) {
      console.error('Error fetching current semester:', error);
    }
  },

  // Set selected semester
  setSelectedSemester: (semesterId: string | null) => {
    set({ selectedSemesterId: semesterId });
    get().fetchSubjects(semesterId ? { semesterId } : undefined);
  },

  // Refresh subjects
  refreshSubjects: async () => {
    set({ isRefreshing: true });
    const { selectedSemesterId } = get();
    try {
      await get().fetchSubjects(selectedSemesterId ? { semesterId: selectedSemesterId } : undefined);
    } finally {
      set({ isRefreshing: false });
    }
  },

  // Clear error
  clearError: () => set({ error: null }),

  // Reset store
  reset: () =>
    set({
      subjects: [],
      semesters: [],
      currentSemester: null,
      selectedSemesterId: null,
      isLoading: false,
      isRefreshing: false,
      error: null,
    }),
}));

export default useSubjectsStore;
