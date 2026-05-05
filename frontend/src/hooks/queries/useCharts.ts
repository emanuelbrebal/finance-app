import { useQuery } from '@tanstack/react-query'
import { chartsApi } from '@/api/endpoints/charts'

export function useNetWorthEvolution() {
  return useQuery({
    queryKey: ['charts', 'net-worth-evolution'],
    queryFn: () => chartsApi.netWorthEvolution(),
    staleTime: 60_000,
  })
}

export function useIncomeVsExpenses(months = 12) {
  return useQuery({
    queryKey: ['charts', 'income-vs-expenses', months],
    queryFn: () => chartsApi.incomeVsExpenses(months),
    staleTime: 60_000,
  })
}

export function useCategoryDistribution(period: 'current_month' | 'last_month' | 'last_3m' = 'current_month') {
  return useQuery({
    queryKey: ['charts', 'category-distribution', period],
    queryFn: () => chartsApi.categoryDistribution(period),
    staleTime: 60_000,
  })
}

export function useDayOfWeekHeatmap(months = 3) {
  return useQuery({
    queryKey: ['charts', 'day-of-week-heatmap', months],
    queryFn: () => chartsApi.dayOfWeekHeatmap(months),
    staleTime: 60_000,
  })
}
