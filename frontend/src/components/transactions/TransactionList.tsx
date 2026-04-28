import { ChevronLeft, ChevronRight } from 'lucide-react'
import { Money } from '@/components/Money'
import { Button } from '@/components/ui/button'
import { useDeleteTransaction, useTransactions } from '@/hooks/queries/useTransactions'
import { cn } from '@/lib/utils'
import type { ListTransactionsParams } from '@/api/endpoints/transactions'

interface TransactionListProps {
  params: ListTransactionsParams
  page: number
  onPageChange: (page: number) => void
}

export function TransactionList({ params, page, onPageChange }: TransactionListProps) {
  const { data, isLoading, isError } = useTransactions({ ...params, page })
  const deleteMutation = useDeleteTransaction()

  if (isLoading) {
    return <p className="text-center text-xs text-muted-foreground py-8">carregando movimentações...</p>
  }

  if (isError) {
    return <p className="text-center text-xs text-destructive py-8">erro ao carregar movimentações</p>
  }

  const transactions = data?.data ?? []
  const meta = data?.meta

  if (transactions.length === 0) {
    return (
      <div className="rounded-lg border border-dashed border-border p-8 text-center text-xs text-muted-foreground">
        nenhuma movimentação encontrada para este período.
      </div>
    )
  }

  return (
    <div className="space-y-1">
      {transactions.map((tx) => (
        <div
          key={tx.id}
          className="flex items-center justify-between rounded-md border border-border px-3 py-2.5"
        >
          <div className="flex items-center gap-3 min-w-0">
            <span
              className={cn(
                'shrink-0 inline-flex h-6 w-6 items-center justify-center rounded-full text-[10px] font-bold',
                tx.direction === 'in'
                  ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                  : 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
              )}
            >
              {tx.direction === 'in' ? '+' : '−'}
            </span>
            <div className="min-w-0">
              <p className="truncate text-sm font-medium">{tx.description}</p>
              <p className="text-xs text-muted-foreground">
                {new Date(tx.occurred_on + 'T12:00:00').toLocaleDateString('pt-BR')}
              </p>
            </div>
          </div>
          <div className="flex items-center gap-3 shrink-0">
            <Money
              value={tx.amount}
              signed={false}
              className={cn(
                'text-sm font-semibold',
                tx.direction === 'in'
                  ? 'text-emerald-600 dark:text-emerald-400'
                  : 'text-rose-600 dark:text-rose-400',
              )}
            />
            <Button
              variant="ghost"
              size="sm"
              disabled={deleteMutation.isPending}
              onClick={() => {
                if (confirm(`excluir "${tx.description}"?`)) deleteMutation.mutate(tx.id)
              }}
              className="h-7 w-7 p-0 text-muted-foreground hover:text-destructive"
            >
              ×
            </Button>
          </div>
        </div>
      ))}

      {/* Pagination */}
      {meta && meta.last_page > 1 && (
        <div className="flex items-center justify-between pt-2">
          <span className="text-xs text-muted-foreground">
            {transactions.length} de {meta.total} movimentações
          </span>
          <div className="flex items-center gap-1">
            <Button
              variant="ghost"
              size="sm"
              disabled={page <= 1}
              onClick={() => onPageChange(page - 1)}
              className="h-7 w-7 p-0"
            >
              <ChevronLeft className="h-3.5 w-3.5" />
            </Button>
            <span className="text-xs text-muted-foreground tabular-nums px-2">
              {page} / {meta.last_page}
            </span>
            <Button
              variant="ghost"
              size="sm"
              disabled={page >= meta.last_page}
              onClick={() => onPageChange(page + 1)}
              className="h-7 w-7 p-0"
            >
              <ChevronRight className="h-3.5 w-3.5" />
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}
