import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { Plus, RotateCcw, Loader2, CheckCircle2, XCircle, Clock, Eye } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useImportList, useRevertImport } from '@/hooks/queries/useImports'
import type { ImportBatch, ImportStatus } from '@/api/endpoints/imports'

const statusConfig: Record<ImportStatus, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline'; icon: React.ReactNode }> = {
  pending: { label: 'Processando', variant: 'secondary', icon: <Loader2 className="w-3 h-3 animate-spin" /> },
  preview_ready: { label: 'Aguardando revisão', variant: 'default', icon: <Eye className="w-3 h-3" /> },
  completed: { label: 'Concluída', variant: 'outline', icon: <CheckCircle2 className="w-3 h-3 text-green-500" /> },
  failed: { label: 'Falhou', variant: 'destructive', icon: <XCircle className="w-3 h-3" /> },
  reverted: { label: 'Revertida', variant: 'outline', icon: <RotateCcw className="w-3 h-3" /> },
}

function StatusBadge({ status }: { status: ImportStatus }) {
  const cfg = statusConfig[status]
  return (
    <Badge variant={cfg.variant} className="gap-1 text-xs">
      {cfg.icon}
      {cfg.label}
    </Badge>
  )
}

function BatchRow({ batch }: { batch: ImportBatch }) {
  const navigate = useNavigate()
  const revert = useRevertImport()

  return (
    <div className="flex items-center gap-4 px-4 py-3 hover:bg-muted/20 rounded-lg">
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium truncate">{batch.original_filename}</p>
        <p className="text-xs text-muted-foreground">
          {batch.account?.name} · {new Date(batch.created_at).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' })}
        </p>
      </div>

      <div className="text-xs text-muted-foreground tabular-nums text-right hidden sm:block">
        {batch.status === 'completed' && (
          <span>{batch.rows_imported} importadas · {batch.rows_duplicated} dup.</span>
        )}
        {batch.status === 'preview_ready' && (
          <span>{batch.rows_total} linhas</span>
        )}
      </div>

      <StatusBadge status={batch.status} />

      <div className="flex gap-2">
        {batch.status === 'preview_ready' && (
          <Button size="sm" variant="outline" onClick={() => navigate(`/imports/${batch.id}/preview`)}>
            Revisar
          </Button>
        )}
        {batch.status === 'completed' && (
          <Button
            size="sm"
            variant="ghost"
            className="text-muted-foreground hover:text-destructive"
            disabled={revert.isPending}
            onClick={() => {
              if (window.confirm(`Reverter importação de "${batch.original_filename}"? As ${batch.rows_imported} transações serão removidas.`)) {
                revert.mutate(batch.id)
              }
            }}
          >
            <RotateCcw className="w-4 h-4" />
          </Button>
        )}
      </div>
    </div>
  )
}

export default function ImportsPage() {
  const navigate = useNavigate()
  const [page, setPage] = useState(1)
  const { data, isLoading } = useImportList(page)

  const batches = data?.data ?? []
  const meta = data?.meta

  return (
    <div className="max-w-3xl mx-auto py-10 px-4 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Importações</h1>
          <p className="text-sm text-muted-foreground mt-0.5">
            Histórico de extratos importados
          </p>
        </div>
        <Button onClick={() => navigate('/imports/upload')}>
          <Plus className="w-4 h-4 mr-2" />
          Nova importação
        </Button>
      </div>

      {isLoading ? (
        <div className="flex items-center justify-center h-40 text-muted-foreground gap-2">
          <Loader2 className="animate-spin w-5 h-5" />
          Carregando...
        </div>
      ) : batches.length === 0 ? (
        <div className="text-center py-16 space-y-3">
          <Clock className="w-10 h-10 mx-auto text-muted-foreground/40" />
          <p className="text-muted-foreground text-sm">Nenhuma importação ainda.</p>
          <Button variant="outline" onClick={() => navigate('/imports/upload')}>
            Importar primeiro extrato
          </Button>
        </div>
      ) : (
        <div className="space-y-1">
          {batches.map((batch) => (
            <BatchRow key={batch.id} batch={batch} />
          ))}
        </div>
      )}

      {meta && meta.last_page > 1 && (
        <div className="flex justify-center gap-2 pt-2">
          <Button
            variant="outline"
            size="sm"
            disabled={page === 1}
            onClick={() => setPage((p) => p - 1)}
          >
            Anterior
          </Button>
          <span className="text-sm text-muted-foreground self-center">
            {page} / {meta.last_page}
          </span>
          <Button
            variant="outline"
            size="sm"
            disabled={page === meta.last_page}
            onClick={() => setPage((p) => p + 1)}
          >
            Próxima
          </Button>
        </div>
      )}
    </div>
  )
}
