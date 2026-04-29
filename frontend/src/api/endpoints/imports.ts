import { apiClient } from '@/api/client'

export type ImportStatus = 'pending' | 'preview_ready' | 'completed' | 'failed' | 'reverted'

export interface ImportBatch {
  id: number
  account_id: number
  account?: { id: number; name: string }
  importer: string
  original_filename: string
  rows_total: number
  rows_imported: number
  rows_duplicated: number
  status: ImportStatus
  error_message?: string
  created_at: string
  updated_at: string
}

export interface PreviewRow {
  index: number
  occurred_on: string
  description: string
  amount: string
  direction: 'in' | 'out'
  external_id: string | null
  dedup_hash: string
  is_duplicate: boolean
  suggested_category_id: number | null
  category_id: number | null
}

export interface PreviewResponse {
  data: ImportBatch
  rows: PreviewRow[]
}

export interface ConfirmOverride {
  row_index: number
  category_id: number | null
}

interface BatchResponse {
  data: ImportBatch
}

interface BatchListResponse {
  data: ImportBatch[]
  meta: { current_page: number; last_page: number; total: number }
}

interface StoreResponse {
  data: ImportBatch
  preview_url: string
}

interface ConfirmResponse {
  data: ImportBatch
  imported: number
  skipped: number
}

export const importsApi = {
  list: (page = 1) =>
    apiClient.get<BatchListResponse>('/imports', { params: { page } }).then((r) => r.data),

  upload: (payload: { file: File; account_id: number; importer?: string; mapping?: Record<string, string> }) => {
    const form = new FormData()
    form.append('file', payload.file)
    form.append('account_id', String(payload.account_id))
    if (payload.importer) form.append('importer', payload.importer)
    if (payload.mapping) form.append('mapping', JSON.stringify(payload.mapping))
    return apiClient.post<StoreResponse>('/imports', form, {
      headers: { 'Content-Type': 'multipart/form-data' },
    }).then((r) => r.data)
  },

  get: (id: number) =>
    apiClient.get<BatchResponse>(`/imports/${id}`).then((r) => r.data),

  preview: (id: number) =>
    apiClient.get<PreviewResponse>(`/imports/${id}/preview`).then((r) => r.data),

  confirm: (id: number, overrides: ConfirmOverride[] = []) =>
    apiClient.post<ConfirmResponse>(`/imports/${id}/confirm`, { overrides }).then((r) => r.data),

  revert: (id: number) =>
    apiClient.post<BatchResponse>(`/imports/${id}/revert`).then((r) => r.data),
}
