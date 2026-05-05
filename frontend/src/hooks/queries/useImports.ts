import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { importsApi, type ConfirmOverride } from '@/api/endpoints/imports'

export const importKeys = {
  all: ['imports'] as const,
  list: (page: number) => [...importKeys.all, 'list', page] as const,
  detail: (id: number) => [...importKeys.all, id] as const,
  preview: (id: number) => [...importKeys.all, id, 'preview'] as const,
}

export function useImportList(page = 1) {
  return useQuery({
    queryKey: importKeys.list(page),
    queryFn: () => importsApi.list(page),
  })
}

export function useImport(id: number) {
  return useQuery({
    queryKey: importKeys.detail(id),
    queryFn: () => importsApi.get(id),
  })
}

export function useImportPreview(id: number, enabled = true) {
  return useQuery({
    queryKey: importKeys.preview(id),
    queryFn: () => importsApi.preview(id),
    enabled,
    // Poll until preview_ready or failed
    refetchInterval: (query) => {
      const status = query.state.data?.data?.status
      return status === 'preview_ready' || status === 'failed' ? false : 2000
    },
  })
}

export function useUploadImport() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: importsApi.upload,
    onSuccess: () => qc.invalidateQueries({ queryKey: importKeys.all }),
  })
}

export function useConfirmImport(id: number) {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: (overrides: ConfirmOverride[]) => importsApi.confirm(id, overrides),
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: importKeys.all })
      qc.invalidateQueries({ queryKey: ['transactions'] })
      qc.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}

export function useRevertImport() {
  const qc = useQueryClient()
  return useMutation({
    mutationFn: importsApi.revert,
    onSuccess: () => {
      qc.invalidateQueries({ queryKey: importKeys.all })
      qc.invalidateQueries({ queryKey: ['transactions'] })
      qc.invalidateQueries({ queryKey: ['dashboard'] })
    },
  })
}
