import { apiClient } from '@/api/client'

export interface RecurringTransaction {
  id: number
  account_id: number
  account?: { id: number; name: string }
  category_id: number | null
  category?: { id: number; name: string; color: string } | null
  description: string
  amount: string
  direction: 'in' | 'out'
  day_of_month: number
  starts_on: string
  ends_on: string | null
  last_generated_on: string | null
  active: boolean
  created_at: string
  updated_at: string
}

export interface CreateRecurringPayload {
  account_id: number
  category_id?: number | null
  description: string
  amount: string | number
  direction: 'in' | 'out'
  day_of_month: number
  starts_on: string
  ends_on?: string | null
  active?: boolean
}

export type UpdateRecurringPayload = Partial<CreateRecurringPayload>

interface ListResponse { data: RecurringTransaction[] }
interface SingleResponse { data: RecurringTransaction }

export const recurringApi = {
  list: () =>
    apiClient.get<ListResponse>('/recurring-transactions').then((r) => r.data.data),

  create: (payload: CreateRecurringPayload) =>
    apiClient.post<SingleResponse>('/recurring-transactions', payload).then((r) => r.data.data),

  update: (id: number, payload: UpdateRecurringPayload) =>
    apiClient.patch<SingleResponse>(`/recurring-transactions/${id}`, payload).then((r) => r.data.data),

  remove: (id: number) =>
    apiClient.delete(`/recurring-transactions/${id}`).then(() => undefined),

  generateNow: (id: number) =>
    apiClient.post(`/recurring-transactions/${id}/generate-now`).then((r) => r.data),
}
