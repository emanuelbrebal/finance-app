import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  archiveCategory,
  createCategory,
  listCategories,
  seedDefaultCategories,
  updateCategory,
  type Category,
  type CreateCategoryPayload,
  type ListCategoriesParams,
  type UpdateCategoryPayload,
} from '@/api/endpoints/categories'

export const CATEGORIES_KEY = ['categories'] as const

export function useCategories(params: ListCategoriesParams = {}) {
  return useQuery<Category[]>({
    queryKey: [...CATEGORIES_KEY, params],
    queryFn: () => listCategories(params),
    staleTime: 30_000,
  })
}

export function useCreateCategory() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: CreateCategoryPayload) => createCategory(payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: CATEGORIES_KEY }),
  })
}

export function useUpdateCategory(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (payload: UpdateCategoryPayload) => updateCategory(id, payload),
    onSuccess: () => qc.invalidateQueries({ queryKey: CATEGORIES_KEY }),
  })
}

export function useArchiveCategory() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (id: number) => archiveCategory(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: CATEGORIES_KEY }),
  })
}

export function useSeedDefaultCategories() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: () => seedDefaultCategories(),
    onSuccess: () => qc.invalidateQueries({ queryKey: CATEGORIES_KEY }),
  })
}
