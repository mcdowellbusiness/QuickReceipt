import apiService from './api';
import { User, LoginForm, RegisterForm, AuthState } from '../types';

class AuthService {
  // Login user
  async login(credentials: LoginForm): Promise<{ user: User; token: string }> {
    const response = await apiService.post<{ user: User; token: string }>('/auth/login', credentials);
    
    if (response.success && response.data) {
      apiService.setAuthToken(response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      return response.data;
    }
    
    throw new Error('Login failed');
  }

  // Register user
  async register(userData: RegisterForm): Promise<{ user: User; token: string }> {
    const response = await apiService.post<{ user: User; token: string }>('/auth/register', userData);
    
    if (response.success && response.data) {
      apiService.setAuthToken(response.data.token);
      localStorage.setItem('user', JSON.stringify(response.data.user));
      return response.data;
    }
    
    throw new Error('Registration failed');
  }

  // Logout user
  async logout(): Promise<void> {
    try {
      await apiService.post('/auth/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      apiService.clearAuthToken();
    }
  }

  // Get current user
  async getCurrentUser(): Promise<User> {
    const response = await apiService.get<User>('/auth/user');
    
    if (response.success && response.data) {
      localStorage.setItem('user', JSON.stringify(response.data));
      return response.data;
    }
    
    throw new Error('Failed to get current user');
  }

  // Refresh token
  async refreshToken(): Promise<{ token: string }> {
    const response = await apiService.post<{ token: string }>('/auth/refresh');
    
    if (response.success && response.data) {
      apiService.setAuthToken(response.data.token);
      return response.data;
    }
    
    throw new Error('Token refresh failed');
  }

  // Check if user is authenticated
  isAuthenticated(): boolean {
    const token = apiService.getAuthToken();
    const user = this.getStoredUser();
    return !!(token && user);
  }

  // Get stored user from localStorage
  getStoredUser(): User | null {
    try {
      const userStr = localStorage.getItem('user');
      return userStr ? JSON.parse(userStr) : null;
    } catch (error) {
      console.error('Error parsing stored user:', error);
      return null;
    }
  }

  // Get auth state
  getAuthState(): AuthState {
    const user = this.getStoredUser();
    const token = apiService.getAuthToken();
    
    return {
      user,
      token,
      isAuthenticated: this.isAuthenticated(),
      isLoading: false,
    };
  }

  // Verify email
  async verifyEmail(token: string): Promise<void> {
    const response = await apiService.post('/auth/verify-email', { token });
    
    if (!response.success) {
      throw new Error('Email verification failed');
    }
  }

  // Resend verification email
  async resendVerificationEmail(): Promise<void> {
    const response = await apiService.post('/auth/email/verification-notification');
    
    if (!response.success) {
      throw new Error('Failed to resend verification email');
    }
  }

  // Request password reset
  async requestPasswordReset(email: string): Promise<void> {
    const response = await apiService.post('/auth/forgot-password', { email });
    
    if (!response.success) {
      throw new Error('Failed to request password reset');
    }
  }

  // Reset password
  async resetPassword(token: string, email: string, password: string, passwordConfirmation: string): Promise<void> {
    const response = await apiService.post('/auth/reset-password', {
      token,
      email,
      password,
      password_confirmation: passwordConfirmation,
    });
    
    if (!response.success) {
      throw new Error('Password reset failed');
    }
  }
}

export const authService = new AuthService();
export default authService;
