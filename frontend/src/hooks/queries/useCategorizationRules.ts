import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  categorizationRulesApi,
  type CreateRulePayload,
  type UpdateRulePayload,
} from '@/api/endpoints/categorizationRules'

export const RULES_KEY = ['categorization-rules'] as const

export function useCategorizationRules(params?: { auto_learned?: boolean }) {
  return useQuery({
    queryKey: [...RULES_KEY, params],
    queryFn: () => categorizationRulesApi.list(params),
  })
}

export function useCreateRule() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateRulePayload) => categorizationRulesApi.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: RULES_KEY }),
  })
}

export function useUpdateRule(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateRulePayload) => categorizationRulesApi.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: RULES_KEY }),
  })
}

export function useDeleteRule() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => categorizationRulesApi.remove(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: RULES_KEY }),
  })
}

export function useApplyRuleToExisting() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => categorizationRulesApi.applyToExisting(id),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: RULES_KEY })
      qc.invalidateQueries({ queryKey: ['transactions'] })
    },
  })
}
