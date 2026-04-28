import { apiClient } from '@/api/client'
import type { LoginInput, RegisterInput } from '@/lib/validators/auth'

export interface User {
  id: number
  name: string
  email: string
  target_net_worth: string | null
  target_date: string | null
  estimated_monthly_income: string | null
  timezone: string
  journey_level: string | null
  preferences: Record<string, unknown>
  created_at: string
}

interface UserResponse {
  data: User
}

export async function register(input: RegisterInput): Promise<User> {
  const { data } = await apiClient.post<UserResponse>('/auth/register', input)
  return data.data
}

export async function login(input: LoginInput): Promise<User> {
  const { data } = await apiClient.post<UserResponse>('/auth/login', input)
  return data.data
}

export async function logout(): Promise<void> {
  await apiClient.post('/auth/logout')
}

export async function me(): Promise<User> {
  const { data } = await apiClient.get<UserResponse>('/auth/me')
  return data.data
}
