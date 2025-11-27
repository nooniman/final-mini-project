/**
 * Common/Shared Types
 */

// API Response wrapper
export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
  meta?: PaginationMeta;
}

export interface ApiError {
  success: false;
  message: string;
  errors?: Record<string, string[]>;
  code?: string;
}

export interface PaginationMeta {
  currentPage: number;
  totalPages: number;
  totalItems: number;
  itemsPerPage: number;
  hasNextPage: boolean;
  hasPreviousPage: boolean;
}

export interface PaginationParams {
  page?: number;
  limit?: number;
  sortBy?: string;
  sortOrder?: 'asc' | 'desc';
}

// Theme types
export type ThemeMode = 'light' | 'dark' | 'system';

// Loading states
export interface LoadingState {
  isLoading: boolean;
  isRefreshing: boolean;
  error: string | null;
}

// Selection types
export interface SelectOption<T = string> {
  label: string;
  value: T;
  disabled?: boolean;
}

// Date range
export interface DateRange {
  startDate: string;
  endDate: string;
}
