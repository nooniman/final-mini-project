/**
 * App Configuration
 * Environment-specific settings and API configuration
 */

// API Configuration
export const API_CONFIG = {
  // Base URL for the PHP REST API backend
  // Change this to your actual API URL in production
  BASE_URL: process.env.EXPO_PUBLIC_API_URL || 'http://192.168.254.108/backend',
  
  // Request timeout in milliseconds
  TIMEOUT: 15000,
  
  // API Version
  VERSION: 'v1',
};

// App Configuration
export const APP_CONFIG = {
  // App Name
  NAME: 'WMSU Grading System',
  
  // App Version
  VERSION: '1.0.0',
  
  // University Information
  UNIVERSITY: {
    NAME: 'Western Mindanao State University',
    SHORT_NAME: 'WMSU',
    WEBSITE: 'https://wmsu.edu.ph',
  },
  
  // Feature Flags
  FEATURES: {
    ENABLE_NOTIFICATIONS: true,
    ENABLE_BIOMETRIC_AUTH: false,
    ENABLE_OFFLINE_MODE: true,
    ENABLE_DARK_MODE: true,
  },
  
  // Cache Configuration
  CACHE: {
    // How long to cache grades data (in milliseconds)
    GRADES_TTL: 1000 * 60 * 30, // 30 minutes
    
    // How long to cache student info (in milliseconds)
    STUDENT_INFO_TTL: 1000 * 60 * 60, // 1 hour
    
    // How long to cache subjects (in milliseconds)
    SUBJECTS_TTL: 1000 * 60 * 60, // 1 hour
  },
  
  // Pagination defaults
  PAGINATION: {
    DEFAULT_PAGE_SIZE: 20,
    MAX_PAGE_SIZE: 100,
  },
};

// Secure Storage Keys
export const STORAGE_KEYS = {
  AUTH_TOKEN: 'auth_token',
  REFRESH_TOKEN: 'refresh_token',
  USER_DATA: 'user_data',
  THEME_MODE: 'theme_mode',
  ONBOARDING_COMPLETE: 'onboarding_complete',
  LAST_SYNC: 'last_sync',
} as const;

// API Endpoints
export const API_ENDPOINTS = {
  // Authentication
  AUTH: {
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    REFRESH: '/auth/refresh',
    PROFILE: '/auth/profile',
    CHANGE_PASSWORD: '/auth/change-password',
  },
  
  // Students
  STUDENTS: {
    INFO: '/students/info',
    UPDATE_PROFILE: '/students/profile',
  },
  
  // Grades
  GRADES: {
    LIST: '/grades',
    BY_SUBJECT: (subjectId: string) => `/grades/subject/${subjectId}`,
    GWA: '/grades/gwa',
    SUMMARY: '/grades/summary',
  },
  
  // Subjects
  SUBJECTS: {
    LIST: '/subjects',
    DETAIL: (id: string) => `/subjects/${id}`,
    SCHEDULE: '/subjects/schedule',
  },
  
  // Attendance / Monitoring
  ATTENDANCE: {
    LIST: '/attendance',
    BY_SUBJECT: (subjectId: string) => `/attendance/subject/${subjectId}`,
    SUMMARY: '/attendance/summary',
  },
  
  // Notifications
  NOTIFICATIONS: {
    LIST: '/notifications',
    MARK_READ: (id: string) => `/notifications/${id}/read`,
    MARK_ALL_READ: '/notifications/read-all',
  },
  
  // Semesters
  SEMESTERS: {
    LIST: '/semesters',
    CURRENT: '/semesters/current',
  },
} as const;

export default {
  API_CONFIG,
  APP_CONFIG,
  STORAGE_KEYS,
  API_ENDPOINTS,
};
