import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  goalsApi,
  type CreateGoalPayload,
  type UpdateGoalPayload,
} from '@/api/endpoints/goals'

export const GOALS_KEY = ['goals'] as const
export const EMERGENCY_KEY = ['goals', 'emergency-fund'] as const

export function useGoals() {
  return useQuery({ queryKey: GOALS_KEY, queryFn: () => goalsApi.list() })
}

export function useEmergencyFund() {
  return useQuery({ queryKey: EMERGENCY_KEY, queryFn: () => goalsApi.emergencyFund() })
}

export function useCreateGoal() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateGoalPayload) => goalsApi.create(payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: GOALS_KEY })
      qc.invalidateQueries({ queryKey: EMERGENCY_KEY })
    },
  })
}

export function useUpdateGoal(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateGoalPayload) => goalsApi.update(id, payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: GOALS_KEY })
      qc.invalidateQueries({ queryKey: EMERGENCY_KEY })
    },
  })
}

export function useDeleteGoal() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => goalsApi.remove(id),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: GOALS_KEY })
      qc.invalidateQueries({ queryKey: EMERGENCY_KEY })
    },
  })
}

export function useDepositGoal() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: ({ id, amount }: { id: number; amount: number }) => goalsApi.deposit(id, amount),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: GOALS_KEY })
      qc.invalidateQueries({ queryKey: EMERGENCY_KEY })
    },
  })
}

export function useAutoTargetEmergency() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: () => goalsApi.autoTargetEmergencyFund(),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: GOALS_KEY })
      qc.invalidateQueries({ queryKey: EMERGENCY_KEY })
    },
  })
}
