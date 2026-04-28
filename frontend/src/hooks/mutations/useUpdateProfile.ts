import { useMutation, useQueryClient } from '@tanstack/react-query'
import { updateProfile, type UpdateProfileInput } from '@/api/endpoints/profile'

export function useUpdateProfile() {
  const qc = useQueryClient()

  return useMutation({
    mutationFn: (input: UpdateProfileInput) => updateProfile(input),
    onSuccess: (user) => {
      // Update the cached user in auth/me so the topbar name reflects immediately
      qc.setQueryData(['auth', 'me'], user)
    },
  })
}
