// User and Authentication Types
export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at?: string;
  created_at: string;
  updated_at: string;
}

export interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  isLoading: boolean;
}

// Organization Types
export interface Organization {
  id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface OrgMember {
  id: number;
  org_id: number;
  user_id: number;
  role: 'admin' | 'member';
  created_at: string;
  updated_at: string;
  user: User;
}

// Team Types
export interface Team {
  id: number;
  org_id: number;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface TeamMember {
  id: number;
  team_id: number;
  user_id: number;
  role: 'leader' | 'member';
  created_at: string;
  updated_at: string;
  user: User;
}

// Budget and Transaction Types
export interface Budget {
  id: number;
  org_id: number;
  team_id?: number;
  name: string;
  amount: number;
  spent: number;
  period: 'monthly' | 'quarterly' | 'yearly';
  created_at: string;
  updated_at: string;
}

export interface Transaction {
  id: number;
  org_id: number;
  team_id?: number;
  user_id: number;
  amount: number;
  description: string;
  category_id: number;
  created_at: string;
  updated_at: string;
  category: Category;
  user: User;
}

export interface Category {
  id: number;
  org_id: number;
  name: string;
  color: string;
  created_at: string;
  updated_at: string;
}

// Receipt Types
export interface Receipt {
  id: number;
  org_id: number;
  team_id?: number;
  user_id: number;
  transaction_id?: number;
  file_id: number;
  amount: number;
  description: string;
  created_at: string;
  updated_at: string;
  file: File;
  user: User;
}

export interface File {
  id: number;
  org_id: number;
  filename: string;
  original_name: string;
  mime_type: string;
  size: number;
  path: string;
  created_at: string;
  updated_at: string;
}

// Invitation Types
export interface Invitation {
  id: number;
  org_id: number;
  team_id?: number;
  email: string;
  role: 'admin' | 'leader' | 'member';
  token: string;
  expires_at: string;
  accepted_at?: string;
  created_at: string;
  updated_at: string;
}

// API Response Types
export interface ApiResponse<T> {
  data: T;
  message?: string;
  success: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

// Form Types
export interface LoginForm {
  email: string;
  password: string;
}

export interface RegisterForm {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface CreateTeamForm {
  name: string;
}

export interface InviteUserForm {
  email: string;
  role: 'admin' | 'leader' | 'member';
  team_id?: number;
}

// Navigation Types
export interface NavItem {
  label: string;
  href: string;
  icon?: string;
  children?: NavItem[];
}

// Screen Size Types
export type ScreenSize = 'xs' | 'sm' | 'md' | 'lg' | 'xl' | '2xl';

// Component Props Types
export interface BaseComponentProps {
  className?: string;
  children?: React.ReactNode;
}

export interface ButtonProps extends BaseComponentProps {
  variant?: 'primary' | 'secondary' | 'destructive' | 'outline' | 'ghost';
  size?: 'sm' | 'md' | 'lg';
  disabled?: boolean;
  loading?: boolean;
  onClick?: () => void;
  type?: 'button' | 'submit' | 'reset';
}

export interface InputProps extends BaseComponentProps {
  type?: 'text' | 'email' | 'password' | 'number' | 'tel' | 'url';
  placeholder?: string;
  value?: string;
  onChange?: (value: string) => void;
  error?: string;
  disabled?: boolean;
  required?: boolean;
}
