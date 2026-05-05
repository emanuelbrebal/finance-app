import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  wishlistApi,
  type CreateWishlistPayload,
  type UpdateWishlistPayload,
  type WishlistStatus,
} from '@/api/endpoints/wishlist'

export const WISHLIST_KEY = ['wishlist'] as const

export function useWishlist(params?: { status?: WishlistStatus; priority?: number }) {
  return useQuery({
    queryKey: [...WISHLIST_KEY, 'list', params],
    queryFn: () => wishlistApi.list(params),
  })
}

export function useWishlistItem(id: number) {
  return useQuery({
    queryKey: [...WISHLIST_KEY, id],
    queryFn: () => wishlistApi.get(id),
  })
}

export function useWishlistSummary() {
  return useQuery({
    queryKey: [...WISHLIST_KEY, 'summary'],
    queryFn: () => wishlistApi.summary(),
  })
}

export function useCreateWishlistItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateWishlistPayload) => wishlistApi.create(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: WISHLIST_KEY }),
  })
}

export function useUpdateWishlistItem(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateWishlistPayload) => wishlistApi.update(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: WISHLIST_KEY }),
  })
}

export function useDeleteWishlistItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => wishlistApi.remove(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: WISHLIST_KEY }),
  })
}

export function useExtendQuarantine() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => wishlistApi.extendQuarantine(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: WISHLIST_KEY }),
  })
}

export function useAbandonWishlistItem() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => wishlistApi.abandon(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: WISHLIST_KEY }),
  })
}
