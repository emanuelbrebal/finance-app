import { Money } from '@/components/Money'
import { Button } from '@/components/ui/button'
import { useDeleteTransaction, useTransactions } from '@/hooks/queries/useTransactions'
import { cn } from '@/lib/utils'

export function TransactionList() {
  const { data, isLoading, isError } = useTransactions()
  const deleteMutation = useDeleteTransaction()

  if (isLoading) return <p className="text-center text-xs text-muted-foreground">carregando movimentações...</p>
  if (isError) return <p className="text-center text-xs text-destructive">erro ao carregar movimentações</p>

  const transactions = data?.data ?? []

  if (transactions.length === 0) {
    return (
      <div className="rounded-lg border border-dashed border-border p-6 text-center text-xs text-muted-foreground">
        nenhuma movimentação ainda. registre a primeira acima.
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
                tx.direction === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400',
              )}
            />
            <Button
              variant="ghost"
              size="sm"
              disabled={deleteMutation.isPending}
              onClick={() => {
                if (confirm(`excluir "${tx.description}"?`)) deleteMutation.mutate(tx.id)
              }}
            >
              ×
            </Button>
          </div>
        </div>
      ))}
      {data?.meta && data.meta.total > transactions.length && (
        <p className="text-center text-xs text-muted-foreground pt-2">
          mostrando {transactions.length} de {data.meta.total} movimentações
        </p>
      )}
    </div>
  )
}
