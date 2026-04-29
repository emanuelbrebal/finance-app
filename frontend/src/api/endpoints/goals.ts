import { apiClient } from '@/api/client'

export interface Goal {
  id: number
  name: string
  target_amount: string
  current_amount: string
  progress_pct: number
  target_date: string | null
  account_id: number | null
  account?: { id: number; name: string } | null
  is_emergency_fund: boolean
  achieved_at: string | null
  monthly_needed?: string
  months_left?: number
  created_at: string
  updated_at: string
}

export interface EmergencyFund extends Goal {
  burn_rate_3m: string
  coverage_months: number | null
}

export interface CreateGoalPayload {
  name: string
  target_amount: string | number
  current_amount?: string | number
  target_date?: string | null
  account_id?: number | null
  is_emergency_fund?: boolean
}

export type UpdateGoalPayload = Partial<CreateGoalPayload>

interface ListResponse { data: Goal[] }
interface SingleResponse { data: Goal }
interface EmergencyResponse { data: EmergencyFund | null }
interface AutoTargetResponse { data: Goal; computed_from: { burn_rate_6m: string; multiplier: number } }

export const goalsApi = {
  list: () =>
    apiClient.get<ListResponse>('/goals').then((r) => r.data.data),

  get: (id: number) =>
    apiClient.get<SingleResponse>(`/goals/${id}`).then((r) => r.data.data),

  create: (payload: CreateGoalPayload) =>
    apiClient.post<SingleResponse>('/goals', payload).then((r) => r.data.data),

  update: (id: number, payload: UpdateGoalPayload) =>
    apiClient.patch<SingleResponse>(`/goals/${id}`, payload).then((r) => r.data.data),

  remove: (id: number) =>
    apiClient.delete(`/goals/${id}`).then(() => undefined),

  deposit: (id: number, amount: number) =>
    apiClient.post<SingleResponse>(`/goals/${id}/deposit`, { amount }).then((r) => r.data.data),

  emergencyFund: () =>
    apiClient.get<EmergencyResponse>('/goals/emergency-fund').then((r) => r.data.data),

  autoTargetEmergencyFund: () =>
    apiClient.post<AutoTargetResponse>('/goals/emergency-fund/auto-target').then((r) => r.data),
}
