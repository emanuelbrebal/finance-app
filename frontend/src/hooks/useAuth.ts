import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { login, logout, me, register, type User } from '@/api/endpoints/auth'
import type { LoginInput, RegisterInput } from '@/lib/validators/auth'

const ME_KEY = ['auth', 'me'] as const

export function useMe() {
  return useQuery<User | null>({
    queryKey: ME_KEY,
    queryFn: async () => {
      try {
        return await me()
      } catch (err) {
        if (isUnauthorized(err)) return null
        throw err
      }
    },
    staleTime: 60_000,
    retry: false,
  })
}

export function useLogin() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (input: LoginInput) => login(input),
    onSuccess: (user) => qc.setQueryData(ME_KEY, user),
  })
}

export function useRegister() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (input: RegisterInput) => register(input),
    onSuccess: (user) => qc.setQueryData(ME_KEY, user),
  })
}

export function useLogout() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: () => logout(),
    onSuccess: () => {
      qc.setQueryData(ME_KEY, null)
      qc.clear()
    },
  })
}

function isUnauthorized(err: unknown): boolean {
  return (
    typeof err === 'object' &&
    err !== null &&
    'response' in err &&
    typeof (err as { response?: { status?: number } }).response?.status === 'number' &&
    (err as { response: { status: number } }).response.status === 401
  )
}
