import { Money } from '@/components/Money'
import { Button } from '@/components/ui/button'
import { useAccounts, useArchiveAccount } from '@/hooks/queries/useAccounts'
import { ACCOUNT_TYPE_LABELS } from '@/lib/validators/account'

export function AccountList() {
  const { data: accounts, isLoading, isError } = useAccounts()
  const archiveMutation = useArchiveAccount()

  if (isLoading) {
    return <p className="text-center text-xs text-muted-foreground">carregando contas...</p>
  }

  if (isError) {
    return (
      <p className="text-center text-xs text-destructive">erro ao carregar contas</p>
    )
  }

  if (!accounts || accounts.length === 0) {
    return (
      <div className="rounded-lg border border-dashed border-border p-6 text-center text-xs text-muted-foreground">
        nenhuma conta ainda. cadastre a primeira para começar.
      </div>
    )
  }

  return (
    <ul className="space-y-2">
      {accounts.map((account) => (
        <li
          key={account.id}
          className="flex items-center justify-between rounded-lg border border-border p-4"
        >
          <div className="flex items-center gap-3">
            <span
              className="inline-block h-3 w-3 rounded-full"
              style={{ backgroundColor: account.color ?? '#94a3b8' }}
              aria-hidden
            />
            <div>
              <p className="text-sm font-medium">{account.name}</p>
              <p className="text-xs text-muted-foreground">
                {ACCOUNT_TYPE_LABELS[account.type]} · saldo inicial{' '}
                <Money value={account.initial_balance} />
              </p>
            </div>
          </div>
          <Button
            variant="ghost"
            size="sm"
            disabled={archiveMutation.isPending}
            onClick={() => {
              if (confirm(`arquivar "${account.name}"?`)) {
                archiveMutation.mutate(account.id)
              }
            }}
          >
            arquivar
          </Button>
        </li>
      ))}
    </ul>
  )
}
