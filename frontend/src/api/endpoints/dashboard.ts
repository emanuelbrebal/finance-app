import { apiClient } from '@/api/client'
import type { Transaction } from './transactions'

export interface AccountBalance {
  account_id: number
  name: string
  type: string
  color: string | null
  balance: string
}

export interface TopExpense {
  category_id: number | null
  category_name: string
  category_color: string | null
  total: string
  count: number
}

export interface DashboardData {
  net_worth: string
  net_worth_by_account: AccountBalance[]
  month: string
  income: string
  expenses: string
  saved: string
  savings_rate: number | null
  burn_rate_3m: string
  burn_rate_months_sampled: number
  runway_months: number | null
  top_expenses: TopExpense[]
  recent_transactions: Transaction[]
}

interface DashboardResponse {
  data: DashboardData
}

export async function getDashboard(): Promise<DashboardData> {
  const { data } = await apiClient.get<DashboardResponse>('/dashboard')
  return data.data
}
