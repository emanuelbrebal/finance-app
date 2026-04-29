import { apiClient } from '@/api/client'

export type MatchType = 'contains' | 'starts_with' | 'exact' | 'regex'

export interface CategorizationRule {
  id: number
  match_type: MatchType
  pattern: string
  category_id: number
  category?: { id: number; name: string; color: string; kind: string }
  priority: number
  auto_learned: boolean
  hits: number
  created_at: string
  updated_at: string
}

export interface CreateRulePayload {
  match_type: MatchType
  pattern: string
  category_id: number
  priority?: number
  auto_learned?: boolean
}

export interface UpdateRulePayload extends Partial<CreateRulePayload> {}

interface ListResponse { data: CategorizationRule[] }
interface SingleResponse { data: CategorizationRule }
interface ApplyResponse { matched_count: number }

export const categorizationRulesApi = {
  list: (params?: { auto_learned?: boolean }) =>
    apiClient.get<ListResponse>('/categorization-rules', { params }).then((r) => r.data.data),

  create: (payload: CreateRulePayload) =>
    apiClient.post<SingleResponse>('/categorization-rules', payload).then((r) => r.data.data),

  update: (id: number, payload: UpdateRulePayload) =>
    apiClient.patch<SingleResponse>(`/categorization-rules/${id}`, payload).then((r) => r.data.data),

  remove: (id: number) =>
    apiClient.delete(`/categorization-rules/${id}`).then(() => undefined),

  applyToExisting: (id: number) =>
    apiClient.post<ApplyResponse>(`/categorization-rules/${id}/apply-to-existing`).then((r) => r.data),
}
