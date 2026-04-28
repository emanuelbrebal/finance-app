import { useState } from 'react'
import { Pencil } from 'lucide-react'
import { Money } from '@/components/Money'
import { Button } from '@/components/ui/button'
import { AccountForm } from './AccountForm'
import { useAccounts, useArchiveAccount } from '@/hooks/queries/useAccounts'
import { ACCOUNT_TYPE_LABELS } from '@/lib/validators/account'

export function AccountList() {
  const [editingId, setEditingId] = useState<number | null>(null)

  const { data: accounts, isLoading, isError } = useAccounts()
  const archiveMutation = useArchiveAccount()

  if (isLoading) {
    return <p className="text-center text-xs text-muted-foreground">carregando contas...</p>
  }

  if (isError) {
    return <p className="text-center text-xs text-destructive">erro ao carregar contas</p>
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
      {accounts.map((account) => {
        if (account.id === editingId) {
          return (
            <li key={account.id}>
              <AccountForm
                account={account}
                onSuccess={() => setEditingId(null)}
                onCancel={() => setEditingId(null)}
              />
            </li>
          )
        }

        return (
          <li
            key={account.id}
            className="group flex items-center justify-between rounded-lg border border-border p-4 transition-colors hover:bg-accent/30"
          >
            <div className="flex items-center gap-3">
              <span
                className="inline-block h-3 w-3 rounded-full shrink-0"
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

            <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
              <Button
                variant="ghost"
                size="sm"
                onClick={() => setEditingId(account.id)}
                className="h-8 w-8 p-0 text-muted-foreground hover:text-foreground"
                aria-label="editar"
              >
                <Pencil className="h-3.5 w-3.5" />
              </Button>
              <Button
                variant="ghost"
                size="sm"
                disabled={archiveMutation.isPending}
                onClick={() => {
                  if (confirm(`arquivar "${account.name}"?`)) archiveMutation.mutate(account.id)
                }}
                className="h-8 px-2 text-xs text-muted-foreground hover:text-destructive"
              >
                arquivar
              </Button>
            </div>
          </li>
        )
      })}
    </ul>
  )
}
