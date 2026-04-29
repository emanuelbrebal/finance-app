import { apiClient } from '@/api/client'

export interface NetWorthPoint {
  date: string
  value: string
  is_projection: boolean
  is_current?: boolean
}

export interface NetWorthEvolution {
  history: NetWorthPoint[]
  projection: NetWorthPoint[]
  target: string | null
  target_date: string | null
}

export interface IncomeVsExpensesPoint {
  month: string // YYYY-MM
  income: string
  expenses: string
}

export interface CategoryDistributionPoint {
  category_id: number | null
  name: string
  color: string
  amount: string
  pct: number
}

export interface DayOfWeekPoint {
  dow: number
  name: string
  amount: string
  count: number
}

export const chartsApi = {
  netWorthEvolution: () =>
    apiClient.get<{ data: NetWorthEvolution }>('/charts/net-worth-evolution').then((r) => r.data.data),

  incomeVsExpenses: (months = 12) =>
    apiClient.get<{ data: IncomeVsExpensesPoint[] }>('/charts/income-vs-expenses', { params: { months } }).then((r) => r.data.data),

  categoryDistribution: (period: 'current_month' | 'last_month' | 'last_3m' = 'current_month') =>
    apiClient.get<{ data: CategoryDistributionPoint[] }>('/charts/category-distribution', { params: { period } }).then((r) => r.data.data),

  dayOfWeekHeatmap: (months = 3) =>
    apiClient.get<{ data: DayOfWeekPoint[] }>('/charts/day-of-week-heatmap', { params: { months } }).then((r) => r.data.data),
}
