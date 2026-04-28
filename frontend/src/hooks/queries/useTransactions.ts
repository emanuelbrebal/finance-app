import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  bulkCategorize,
  createTransaction,
  deleteTransaction,
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

export function useBulkCategorize() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ ids, categoryId }: { ids: number[]; categoryId: number }) =>
      bulkCategorize(ids, categoryId),
    onSuccess: () => qc.invalidateQueries({ queryKey: TRANSACTIONS_KEY }),
  })
}
