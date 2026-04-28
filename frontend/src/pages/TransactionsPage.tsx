import { useState } from 'react'
import { TransactionForm } from '@/components/transactions/TransactionForm'
import { TransactionList } from '@/components/transactions/TransactionList'
import { Button } from '@/components/ui/button'

export function TransactionsPage() {
  const [showForm, setShowForm] = useState(false)

  return (
    <section className="space-y-4">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-lg font-semibold">movimentações</h2>
          <p className="text-xs text-muted-foreground">registre entradas e saídas</p>
        </div>
        {!showForm && (
          <Button size="sm" onClick={() => setShowForm(true)}>
            nova
          </Button>
        )}
      </div>

      {showForm && (
        <TransactionForm
          onSuccess={() => setShowForm(false)}
          onCancel={() => setShowForm(false)}
        />
      )}

      <TransactionList />
    </section>
  )
}
