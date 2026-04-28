import { useState, useCallback } from 'react'
import { TransactionForm } from '@/components/transactions/TransactionForm'
import { TransactionFilters } from '@/components/transactions/TransactionFilters'
import { TransactionList } from '@/components/transactions/TransactionList'
import { Button } from '@/components/ui/button'
import type { ListTransactionsParams } from '@/api/endpoints/transactions'

// ── Filter state type (exported so TransactionFilters can import it) ──────────

export interface TxFilters {
  yearMonth: string
  accountId: number | null
  categoryId: number | null
  direction: 'in' | 'out' | null
  search: string
}

function currentYearMonth(): string {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
}

function defaultFilters(): TxFilters {
  return {
    yearMonth: currentYearMonth(),
    accountId: null,
    categoryId: null,
    direction: null,
    search: '',
  }
}

function monthRange(yearMonth: string): { from: string; to: string } {
  const [y, m] = yearMonth.split('-').map(Number)
  const last = new Date(y, m, 0) // day-0 of next month = last day of this month
  return {
    from: `${yearMonth}-01`,
    to: last.toISOString().slice(0, 10),
  }
}

// ── Page ──────────────────────────────────────────────────────────────────────

export function TransactionsPage() {
  const [showForm, setShowForm] = useState(false)
  const [filters, setFilters] = useState<TxFilters>(defaultFilters)
  const [page, setPage] = useState(1)

  const handleFilterChange = useCallback((patch: Partial<TxFilters>) => {
    setFilters((prev) => ({ ...prev, ...patch }))
    setPage(1) // reset page whenever any filter changes
  }, [])

  const handleClear = useCallback(() => {
    setFilters(defaultFilters())
    setPage(1)
  }, [])

  // Build API params from current filter state
  const { from, to } = monthRange(filters.yearMonth)
  const params: ListTransactionsParams = {
    from,
    to,
    ...(filters.accountId !== null ? { account_id: filters.accountId } : {}),
    ...(filters.categoryId !== null ? { category_id: filters.categoryId } : {}),
    ...(filters.direction !== null ? { direction: filters.direction } : {}),
    ...(filters.search ? { search: filters.search } : {}),
    per_page: 20,
  }

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

      <TransactionFilters
        filters={filters}
        onChange={handleFilterChange}
        onClear={handleClear}
      />

      <TransactionList params={params} page={page} onPageChange={setPage} />
    </section>
  )
}
