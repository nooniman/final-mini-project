/**
 * Grades API Service
 */

import { API_ENDPOINTS, API_CONFIG, STORAGE_KEYS } from '../../constants/Config';
import type { 
  Grade, 
  GradeDetails, 
  GWASummary, 
  GradesFilter,
  GradesSummary 
} from '../../types/grades';
import type { ApiResponse } from '../../types/common';
import * as SecureStore from 'expo-secure-store';

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

export const gradesApi = {
  /**
   * Get all grades with optional filters
   */
  getAll: async (filters?: GradesFilter): Promise<ApiResponse<Grade[]>> => {
    let url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.GRADES.LIST}`;
    if (filters?.semesterId) {
      url += `?semester_id=${filters.semesterId}`;
    }
    console.log('📊 Fetching grades from:', url);
    const data = await fetchWithAuth(url);
    console.log('📊 Grades response:', JSON.stringify(data, null, 2));
    return data;
  },

  /**
   * Get grades for a specific subject
   */
  getBySubject: async (subjectId: string): Promise<ApiResponse<GradeDetails>> => {
    const url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.GRADES.BY_SUBJECT(subjectId)}`;
    console.log('📖 Fetching grades for subject:', url);
    const data = await fetchWithAuth(url);
    return data;
  },

  /**
   * Get GWA summary
   */
  getGWA: async (academicYear?: string): Promise<ApiResponse<GWASummary>> => {
    let url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.GRADES.GWA}`;
    if (academicYear) {
      url += `?academic_year=${academicYear}`;
    }
    console.log('🎓 Fetching GWA:', url);
    const data = await fetchWithAuth(url);
    return data;
  },

  /**
   * Get grades summary statistics
   */
  getSummary: async (semesterId?: string): Promise<ApiResponse<GradesSummary>> => {
    let url = `${API_CONFIG.BASE_URL}${API_ENDPOINTS.GRADES.SUMMARY}`;
    if (semesterId) {
      url += `?semester_id=${semesterId}`;
    }
    console.log('📈 Fetching grades summary:', url);
    const data = await fetchWithAuth(url);
    return data;
  },
};

export default gradesApi;
