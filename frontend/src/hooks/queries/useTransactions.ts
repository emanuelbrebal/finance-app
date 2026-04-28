import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  bulkCategorize,
  createTransaction,
  deleteTransaction,
  getMonthlySummary,
  listTransactions,
  updateTransaction,
  type CreateTransactionPayload,
  type ListTransactionsParams,
  type UpdateTransactionPayload,
} from '@/api/endpoints/transactions'

export const TRANSACTIONS_KEY = ['transactions'] as const

export function useTransactions(params: ListTransactionsParams = {}) {
  return useQuery({
    queryKey: [...TRANSACTIONS_KEY, params],
    queryFn: () => listTransactions(params),
    staleTime: 15_000,
  })
}

export function useCreateTransaction() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateTransactionPayload) => createTransaction(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: TRANSACTIONS_KEY }),
  })
}

export function useUpdateTransaction(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateTransactionPayload) => updateTransaction(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: TRANSACTIONS_KEY }),
  })
}

export function useDeleteTransaction() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => deleteTransaction(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: TRANSACTIONS_KEY }),
  })
}

/** Returns income/expense data for the last `months` complete months + current. */
export function useMonthlyStats(months = 6) {
  const now = new Date()
  const toDate = new Date(now.getFullYear(), now.getMonth() + 1, 0) // last day of current month
  const fromDate = new Date(now.getFullYear(), now.getMonth() - (months - 1), 1)
  const from = fromDate.toISOString().slice(0, 10)
  const to = toDate.toISOString().slice(0, 10)

  return useQuery({
    queryKey: [...TRANSACTIONS_KEY, 'monthly-stats', from, to],
    queryFn: () => getMonthlySummary(from, to),
    staleTime: 60_000,
  })
}

export function useBulkCategorize() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ ids, categoryId }: { ids: number[]; categoryId: number }) =>
      bulkCategorize(ids, categoryId),
    onSuccess: () => qc.invalidateQueries({ queryKey: TRANSACTIONS_KEY }),
  })
}
