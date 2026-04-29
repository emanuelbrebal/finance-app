import { apiClient } from '@/api/client'
import type { CategoryKind } from '@/lib/validators/category'

export interface Category {
  id: number
  user_id: number
  name: string
  kind: CategoryKind
  color: string
  icon: string
  is_essential: boolean
  monthly_budget: string | null
  archived_at: string | null
  created_at: string
  updated_at: string
}

interface CategoryResponse {
  data: Category
}

interface CategoryListResponse {
  data: Category[]
}

interface SeedResponse {
  data: { created: number }
}

export interface CreateCategoryPayload {
  name: string
  kind: CategoryKind
  color: string
  icon: string
  is_essential?: boolean
  monthly_budget?: string | null
}

export type UpdateCategoryPayload = Partial<CreateCategoryPayload>

export interface ListCategoriesParams {
  kind?: CategoryKind
  archived?: boolean
}

export async function listCategories(params: ListCategoriesParams = {}): Promise<Category[]> {
  const { data } = await apiClient.get<CategoryListResponse>('/categories', { params })
  return data.data
}

export async function createCategory(payload: CreateCategoryPayload): Promise<Category> {
  const { data } = await apiClient.post<CategoryResponse>('/categories', payload)
  return data.data
}

export async function updateCategory(id: number, payload: UpdateCategoryPayload): Promise<Category> {
  const { data } = await apiClient.patch<CategoryResponse>(`/categories/${id}`, payload)
  return data.data
}

export async function archiveCategory(id: number): Promise<void> {
  await apiClient.delete(`/categories/${id}`)
}

export async function seedDefaultCategories(): Promise<number> {
  const { data } = await apiClient.post<SeedResponse>('/categories/seed')
  return data.data.created
}
