import { useQuery } from '@tanstack/react-query'
import { getDashboard } from '@/api/endpoints/dashboard'

export const DASHBOARD_KEY = ['dashboard'] as const

export function useDashboard() {
  return useQuery({
    queryKey: DASHBOARD_KEY,
    queryFn: getDashboard,
    staleTime: 30_000,
  })
}
