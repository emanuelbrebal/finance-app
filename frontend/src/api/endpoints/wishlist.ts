import { apiClient } from '@/api/client'

export type WishlistStatus = 'waiting' | 'ready_to_buy' | 'purchased' | 'abandoned'

export interface Checkpoint {
  id: string
  label: string
  passed: boolean | null
  reason: string
  progress_pct: number
}

export interface WishlistItem {
  id: number
  name: string
  target_price: string
  current_price: string | null
  reference_url: string | null
  photo_path: string | null
  priority: number
  category_id: number | null
  category?: { id: number; name: string; color: string } | null
  quarantine_days: number
  status: WishlistStatus
  days_in_wishlist: number
  purchased_transaction_id: number | null
  abandoned_at: string | null
  last_review_prompt_at: string | null
  checkpoints?: Checkpoint[]
  created_at: string
  updated_at: string
}

export interface WishlistSummary {
  count_active: number
  total_target_amount: string
  oldest_item_days: number | null
  ready_to_buy_count: number
}

export interface CreateWishlistPayload {
  name: string
  target_price: string | number
  reference_url?: string | null
  priority?: number
  quarantine_days?: number
  category_id?: number | null
}

export type UpdateWishlistPayload = Partial<CreateWishlistPayload> & { current_price?: string | number | null }

interface ListResponse { data: WishlistItem[] }
interface SingleResponse { data: WishlistItem }
interface SummaryResponse { data: WishlistSummary }

export const wishlistApi = {
  list: (params?: { status?: WishlistStatus; priority?: number }) =>
    apiClient.get<ListResponse>('/wishlist', { params }).then((r) => r.data.data),

  get: (id: number) =>
    apiClient.get<SingleResponse>(`/wishlist/${id}`).then((r) => r.data.data),

  create: (payload: CreateWishlistPayload) =>
    apiClient.post<SingleResponse>('/wishlist', payload).then((r) => r.data.data),

  update: (id: number, payload: UpdateWishlistPayload) =>
    apiClient.patch<SingleResponse>(`/wishlist/${id}`, payload).then((r) => r.data.data),

  remove: (id: number) =>
    apiClient.delete(`/wishlist/${id}`).then(() => undefined),

  extendQuarantine: (id: number) =>
    apiClient.post<SingleResponse>(`/wishlist/${id}/extend-quarantine`).then((r) => r.data.data),

  abandon: (id: number) =>
    apiClient.post<SingleResponse>(`/wishlist/${id}/abandon`).then((r) => r.data.data),

  purchase: (id: number, transactionId: number) =>
    apiClient.post<SingleResponse>(`/wishlist/${id}/purchase`, { transaction_id: transactionId })
      .then((r) => r.data.data),

  summary: () =>
    apiClient.get<SummaryResponse>('/wishlist/summary').then((r) => r.data.data),
}
