import { apiClient } from '@/api/client'
import type { AccountType } from '@/lib/validators/account'

export interface Account {
  id: number
  user_id: number
  name: string
  type: AccountType
  initial_balance: string
  currency: string
  color: string | null
  icon: string | null
  archived_at: string | null
  created_at: string
  updated_at: string
}

interface AccountResponse {
  data: Account
}

interface AccountListResponse {
  data: Account[]
}

export interface CreateAccountPayload {
  name: string
  type: AccountType
  initial_balance?: string
  color?: string
  icon?: string
}

export type UpdateAccountPayload = Partial<CreateAccountPayload>

export async function listAccounts(): Promise<Account[]> {
  const { data } = await apiClient.get<AccountListResponse>('/accounts')
  return data.data
}

export async function createAccount(payload: CreateAccountPayload): Promise<Account> {
  const { data } = await apiClient.post<AccountResponse>('/accounts', payload)
  return data.data
}

export async function updateAccount(id: number, payload: UpdateAccountPayload): Promise<Account> {
  const { data } = await apiClient.patch<AccountResponse>(`/accounts/${id}`, payload)
  return data.data
}

export async function archiveAccount(id: number): Promise<void> {
  await apiClient.delete(`/accounts/${id}`)
}
