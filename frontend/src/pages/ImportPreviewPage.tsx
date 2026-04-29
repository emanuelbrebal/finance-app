import { useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { CheckCircle, AlertCircle, Loader2, ArrowLeft } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Money } from '@/components/Money'
import { useImportPreview, useConfirmImport } from '@/hooks/queries/useImports'
import { useCategories } from '@/hooks/queries/useCategories'
import type { PreviewRow, ConfirmOverride } from '@/api/endpoints/imports'

export default function ImportPreviewPage() {
  const { id } = useParams<{ id: string }>()
  const batchId = Number(id)
  const navigate = useNavigate()

  const { data, isLoading } = useImportPreview(batchId)
  const { data: categoriesData } = useCategories({})
  const confirm = useConfirmImport(batchId)

  const [overrides, setOverrides] = useState<Record<number, number | null>>({})

  const categories = categoriesData ?? []
  const batch = data?.data
  const rows = data?.rows ?? []

  const newRows = rows.filter((r) => !r.is_duplicate)
  const duplicateRows = rows.filter((r) => r.is_duplicate)

  const getCategoryId = (row: PreviewRow): number | null => {
    if (row.index in overrides) return overrides[row.index]
    return row.category_id
  }

  const handleCategoryChange = (rowIndex: number, value: string) => {
    setOverrides((prev) => ({
      ...prev,
      [rowIndex]: value === 'none' ? null : Number(value),
    }))
  }

  const handleConfirm = async () => {
    const overrideList: ConfirmOverride[] = Object.entries(overrides).map(([idx, catId]) => ({
      row_index: Number(idx),
      category_id: catId,
    }))
    await confirm.mutateAsync(overrideList)
    navigate('/imports')
  }

  if (isLoading || !batch) {
    return (
      <div className="flex items-center justify-center h-64 gap-2 text-muted-foreground">
        <Loader2 className="animate-spin w-5 h-5" />
        <span>Processando arquivo...</span>
      </div>
    )
  }

  if (batch.status === 'failed') {
    return (
      <div className="max-w-xl mx-auto py-10 px-4 space-y-4">
        <div className="flex items-center gap-2 text-destructive">
          <AlertCircle className="w-5 h-5" />
          <h1 className="text-lg font-semibold">Falha na importação</h1>
        </div>
        <p className="text-sm text-muted-foreground">{batch.error_message}</p>
        <Button variant="outline" onClick={() => navigate('/imports/upload')}>
          <ArrowLeft className="w-4 h-4 mr-2" /> Tentar novamente
        </Button>
      </div>
    )
  }

  if (batch.status !== 'preview_ready') {
    return (
      <div className="flex items-center justify-center h-64 gap-2 text-muted-foreground">
        <Loader2 className="animate-spin w-5 h-5" />
        <span>Aguardando processamento...</span>
      </div>
    )
  }

  return (
    <div className="max-w-4xl mx-auto py-10 px-4 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Revisar importação</h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            {batch.original_filename} — {batch.account?.name}
          </p>
        </div>
        <div className="flex gap-2 text-sm text-muted-foreground">
          <Badge variant="secondary">{newRows.length} novas</Badge>
          {duplicateRows.length > 0 && (
            <Badge variant="outline">{duplicateRows.length} duplicadas</Badge>
          )}
        </div>
      </div>

      {newRows.length === 0 ? (
        <div className="text-center py-16 text-muted-foreground space-y-2">
          <CheckCircle className="w-10 h-10 mx-auto text-green-500" />
          <p className="font-medium">Todas as transações já foram importadas.</p>
          <p className="text-sm">Nenhuma linha nova para inserir.</p>
          <Button variant="outline" onClick={() => navigate('/imports')}>
            Ver histórico de importações
          </Button>
        </div>
      ) : (
        <>
          <div className="rounded-lg border overflow-hidden">
            <table className="w-full text-sm">
              <thead className="bg-muted/50">
                <tr>
                  <th className="text-left px-3 py-2 font-medium text-muted-foreground">Data</th>
                  <th className="text-left px-3 py-2 font-medium text-muted-foreground">Descrição</th>
                  <th className="text-right px-3 py-2 font-medium text-muted-foreground">Valor</th>
                  <th className="text-left px-3 py-2 font-medium text-muted-foreground w-48">Categoria</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-border">
                {newRows.map((row) => (
                  <tr key={row.index} className="hover:bg-muted/20">
                    <td className="px-3 py-2 text-muted-foreground tabular-nums whitespace-nowrap">
                      {new Date(row.occurred_on + 'T00:00:00').toLocaleDateString('pt-BR')}
                    </td>
                    <td className="px-3 py-2 max-w-[280px] truncate">{row.description}</td>
                    <td className="px-3 py-2 text-right tabular-nums">
                      <span className={row.direction === 'in' ? 'text-green-600' : ''}>
                        {row.direction === 'out' ? '−' : '+'}
                        <Money value={row.amount} className={row.direction === 'in' ? 'text-green-600' : ''} />
                      </span>
                    </td>
                    <td className="px-3 py-2">
                      <Select
                        value={String(getCategoryId(row) ?? 'none')}
                        onValueChange={(v) => handleCategoryChange(row.index, v)}
                      >
                        <SelectTrigger className="h-7 text-xs">
                          <SelectValue placeholder="Sem categoria" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectItem value="none">Sem categoria</SelectItem>
                          {categories.map((c) => (
                            <SelectItem key={c.id} value={String(c.id)}>
                              {c.name}
                            </SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          {duplicateRows.length > 0 && (
            <p className="text-xs text-muted-foreground">
              {duplicateRows.length} transação(ões) duplicada(s) foram omitidas e não serão importadas.
            </p>
          )}

          <div className="flex gap-3 justify-end">
            <Button variant="outline" onClick={() => navigate('/imports/upload')}>
              Cancelar
            </Button>
            <Button onClick={handleConfirm} disabled={confirm.isPending}>
              {confirm.isPending ? (
                <><Loader2 className="animate-spin w-4 h-4 mr-2" />Importando...</>
              ) : (
                <>Confirmar {newRows.length} transações</>
              )}
            </Button>
          </div>
        </>
      )}
    </div>
  )
}
