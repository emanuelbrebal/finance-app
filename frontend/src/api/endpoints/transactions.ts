import { apiClient } from '@/api/client'
import type { Direction } from '@/lib/validators/transaction'

export interface Transaction {
  id: number
  user_id: number
  account_id: number
  category_id: number | null
  occurred_on: string
  description: string
  amount: string
  direction: Direction
  notes: string | null
  tags: string[]
  out_of_scope: boolean
  dedup_hash: string
  created_at: string
  updated_at: string
  deleted_at: string | null
}

interface TransactionResponse {
  data: Transaction
}

interface TransactionListResponse {
  data: Transaction[]
  meta: { current_page: number; last_page: number; total: number }
}

interface BulkUpdateResponse {
  data: { updated: number }
}

export interface CreateTransactionPayload {
  account_id: number
  category_id?: number | null
  occurred_on: string
  description: string
  amount: string
  direction: Direction
  notes?: string | null
  tags?: string[]
  out_of_scope?: boolean
}

export type UpdateTransactionPayload = Partial<CreateTransactionPayload>

export interface ListTransactionsParams {
  from?: string
  to?: string
  account_id?: number
  category_id?: number
  direction?: Direction
  search?: string
  tag?: string
  out_of_scope?: boolean
  page?: number
  per_page?: number
}

export async function listTransactions(
  params: ListTransactionsParams = {},
): Promise<TransactionListResponse> {
  const { data } = await apiClient.get<TransactionListResponse>('/transactions', { params })
  return data
}

export async function createTransaction(
  payload: CreateTransactionPayload,
): Promise<{ transaction: Transaction; created: boolean }> {
  const response = await apiClient.post<TransactionResponse>('/transactions', payload, {
    validateStatus: (s) => s === 200 || s === 201,
  })
  return { transaction: response.data.data, created: response.status === 201 }
}

export async function getTransaction(id: number): Promise<Transaction> {
  const { data } = await apiClient.get<TransactionResponse>(`/transactions/${id}`)
  return data.data
}

export async function updateTransaction(
  id: number,
  payload: UpdateTransactionPayload,
): Promise<Transaction> {
  const { data } = await apiClient.patch<TransactionResponse>(`/transactions/${id}`, payload)
  return data.data
}

export async function deleteTransaction(id: number): Promise<void> {
  await apiClient.delete(`/transactions/${id}`)
}

export interface MonthlySummaryRow {
  month: string      // "YYYY-MM"
  direction: 'in' | 'out'
  total: string
  count: number
}

interface MonthlySummaryResponse {
  data: MonthlySummaryRow[]
}

export async function getMonthlySummary(from: string, to: string): Promise<MonthlySummaryRow[]> {
  const { data } = await apiClient.get<MonthlySummaryResponse>('/transactions/summary', {
    params: { group_by: 'month', from, to },
  })
  return data.data
}

export async function bulkCategorize(
  ids: number[],
  categoryId: number,
): Promise<number> {
  const { data } = await apiClient.post<BulkUpdateResponse>('/transactions/bulk-categorize', {
    ids,
    category_id: categoryId,
  })
  return data.data.updated
}
