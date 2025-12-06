/**
 * Authentication Types
 */

export interface User {
  id: string;
  studentId: string;
  email: string;
  firstName: string;
  lastName: string;
  middleName?: string;
  fullName?: string;
  avatar?: string;
  program?: string;
  programCode?: string;
  department?: string;
  college?: string;
  yearLevel?: number;
  section?: string;
  status?: 'active' | 'inactive' | 'graduated' | 'on_leave';
  enrollmentDate?: string;
  createdAt?: string;
  updatedAt?: string;
}

export interface AuthTokens {
  accessToken: string;
  refreshToken: string;
  expiresIn?: number;
  tokenType?: 'Bearer';
}

export interface LoginCredentials {
  studentId: string;
  password: string;
  rememberMe?: boolean;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  data: {
    user: User;
    tokens: AuthTokens;
  };
}

export interface AuthState {
  user: User | null;
  tokens: AuthTokens | null;
  isAuthenticated: boolean;
  isLoading: boolean;
  error: string | null;
}

export interface ChangePasswordRequest {
  currentPassword: string;
  newPassword: string;
  confirmPassword: string;
}
