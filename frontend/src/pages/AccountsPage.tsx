import { useState } from 'react'
import { AccountForm } from '@/components/accounts/AccountForm'
import { AccountList } from '@/components/accounts/AccountList'
import { Button } from '@/components/ui/button'

export function AccountsPage() {
  const [showForm, setShowForm] = useState(false)

  return (
    <section className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold">suas contas</h2>
          <p className="text-xs text-muted-foreground">
            cadastre suas carteiras, contas e cartões para começar
          </p>
        </div>
        {!showForm && (
          <Button size="sm" onClick={() => setShowForm(true)}>
            nova conta
          </Button>
        )}
      </div>

      {showForm && (
        <AccountForm
          onSuccess={() => setShowForm(false)}
          onCancel={() => setShowForm(false)}
        />
      )}

      <AccountList />
    </section>
  )
}
