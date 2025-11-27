/**
 * Subjects API Service
 */

import { API_ENDPOINTS, API_CONFIG } from '../../constants/Config';
import type { 
  Subject, 
  SubjectDetails, 
  Semester,
  SubjectsFilter 
} from '../../types/subjects';
import type { ApiResponse } from '../../types/common';
import * as SecureStore from 'expo-secure-store';
import { STORAGE_KEYS } from '../../constants/Config';

async function fetchWithAuth(url: string) {
  const token = await SecureStore.getItemAsync(STORAGE_KEYS.AUTH_TOKEN);
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': token ? `Bearer ${token}` : '',
    },
  });
  return response.json();
}

export const subjectsApi = {
  /**
   * Get all subjects with optional filters
   */
  getAll: async (filters?: SubjectsFilter): Promise<ApiResponse<Subject[]>> => {
    let url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.SUBJECTS.LIST}`;
    if (filters?.semesterId) {
      url += `?semester_id=${filters.semesterId}`;
    }
    console.log('📚 Fetching subjects from:', url);
    const data = await fetchWithAuth(url);
    console.log('📚 Subjects response:', JSON.stringify(data, null, 2));
    return data;
  },

  /**
   * Get subject details by ID
   */
  getById: async (id: string): Promise<ApiResponse<SubjectDetails>> => {
    const url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.SUBJECTS.DETAIL(id)}`;
    console.log('📖 Fetching subject detail:', url);
    const data = await fetchWithAuth(url);
    return data;
  },

  /**
   * Get class schedule
   */
  getSchedule: async (): Promise<ApiResponse<Subject[]>> => {
    const url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.SUBJECTS.SCHEDULE}`;
    console.log('📅 Fetching schedule:', url);
    const data = await fetchWithAuth(url);
    return data;
  },

  /**
   * Get all semesters
   */
  getSemesters: async (): Promise<ApiResponse<Semester[]>> => {
    const url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.SEMESTERS.LIST}`;
    const data = await fetchWithAuth(url);
    return data;
  },

  /**
   * Get current semester
   */
  getCurrentSemester: async (): Promise<ApiResponse<Semester>> => {
    const url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.SEMESTERS.CURRENT}`;
    const data = await fetchWithAuth(url);
    return data;
  },
};

export default subjectsApi;
