import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  recurringApi,
  type CreateRecurringPayload,
  type UpdateRecurringPayload,
} from '@/api/endpoints/recurringTransactions'

export const RECURRING_KEY = ['recurring-transactions'] as const

export function useRecurringTransactions() {
  return useQuery({
    queryKey: RECURRING_KEY,
    queryFn: () => recurringApi.list(),
  })
}

export function useCreateRecurring() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateRecurringPayload) => recurringApi.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: RECURRING_KEY }),
  })
}

export function useUpdateRecurring(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateRecurringPayload) => recurringApi.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: RECURRING_KEY }),
  })
}

export function useDeleteRecurring() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => recurringApi.remove(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: RECURRING_KEY }),
  })
}

export function useGenerateRecurringNow() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => recurringApi.generateNow(id),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: RECURRING_KEY })
      qc.invalidateQueries({ queryKey: ['transactions'] })
      qc.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}
