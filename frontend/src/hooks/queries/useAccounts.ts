import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  archiveAccount,
  createAccount,
  listAccounts,
  updateAccount,
  type Account,
  type CreateAccountPayload,
  type UpdateAccountPayload,
} from '@/api/endpoints/accounts'

export const ACCOUNTS_KEY = ['accounts'] as const

export function useAccounts() {
  return useQuery<Account[]>({
    queryKey: ACCOUNTS_KEY,
    queryFn: listAccounts,
    staleTime: 30_000,
  })
}

export function useCreateAccount() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateAccountPayload) => createAccount(payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ACCOUNTS_KEY })
    },
  })
}

export function useUpdateAccount(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateAccountPayload) => updateAccount(id, payload),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ACCOUNTS_KEY })
    },
  })
}

export function useArchiveAccount() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => archiveAccount(id),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: ACCOUNTS_KEY })
    },
  })
}
