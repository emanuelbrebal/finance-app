import { apiClient } from '@/api/client'
import type { User } from './auth'

interface UserResponse {
  data: User
}

export interface UpdateProfileInput {
  name?: string
  email?: string
  password?: string
  password_confirmation?: string
  target_net_worth?: string | null
  target_date?: string | null
  estimated_monthly_income?: string | null
  timezone?: string
}

export async function getProfile(): Promise<User> {
  const { data } = await apiClient.get<UserResponse>('/profile')
  return data.data
}

export async function updateProfile(input: UpdateProfileInput): Promise<User> {
  const { data } = await apiClient.patch<UserResponse>('/profile', input)
  return data.data
}
