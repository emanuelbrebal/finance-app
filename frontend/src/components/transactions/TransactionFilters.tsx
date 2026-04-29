import { ChevronLeft, ChevronRight, X } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { cn } from '@/lib/utils'
import { useAccounts } from '@/hooks/queries/useAccounts'
import { useCategories } from '@/hooks/queries/useCategories'
import type { TxFilters } from '@/pages/TransactionsPage'

// ── Helpers ───────────────────────────────────────────────────────────────────

function ptMonth(yearMonth: string): string {
  const [y, m] = yearMonth.split('-')
  const months = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez']
  return `${months[parseInt(m) - 1]} ${y}`
}

function addMonths(yearMonth: string, delta: number): string {
  const [y, m] = yearMonth.split('-').map(Number)
  const date = new Date(y, m - 1 + delta, 1)
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`
}

function currentYearMonth(): string {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
}

// ── Props ─────────────────────────────────────────────────────────────────────

interface TransactionFiltersProps {
  filters: TxFilters
  onChange: (patch: Partial<TxFilters>) => void
  onClear: () => void
}

// ── Component ─────────────────────────────────────────────────────────────────

export function TransactionFilters({ filters, onChange, onClear }: TransactionFiltersProps) {
  const { data: accounts = [] } = useAccounts()
  const { data: categories = [] } = useCategories()

  const isFiltered =
    filters.accountId !== null ||
    filters.categoryId !== null ||
    filters.direction !== null ||
    filters.search !== '' ||
    filters.yearMonth !== currentYearMonth()

  return (
    <div className="space-y-2">
      {/* Period row */}
      <div className="flex items-center gap-2">
        <div className="flex items-center rounded-md border border-border">
          <button
            type="button"
            onClick={() => onChange({ yearMonth: addMonths(filters.yearMonth, -1) })}
            className="flex h-8 items-center px-2 text-muted-foreground hover:text-foreground transition-colors"
            aria-label="mês anterior"
          >
            <ChevronLeft className="h-3.5 w-3.5" />
          </button>
          <span className="min-w-[6rem] text-center text-sm font-medium tabular-nums">
            {ptMonth(filters.yearMonth)}
          </span>
          <button
            type="button"
            onClick={() => onChange({ yearMonth: addMonths(filters.yearMonth, 1) })}
            className="flex h-8 items-center px-2 text-muted-foreground hover:text-foreground transition-colors"
            aria-label="próximo mês"
          >
            <ChevronRight className="h-3.5 w-3.5" />
          </button>
        </div>

        {filters.yearMonth !== currentYearMonth() && (
          <button
            type="button"
            onClick={() => onChange({ yearMonth: currentYearMonth() })}
            className="text-xs text-muted-foreground hover:text-foreground underline-offset-2 hover:underline transition-colors"
          >
            mês atual
          </button>
        )}

        {isFiltered && (
          <Button variant="ghost" size="sm" onClick={onClear} className="ml-auto gap-1.5 text-xs h-8">
            <X className="h-3 w-3" />
            limpar filtros
          </Button>
        )}
      </div>

      {/* Filters row */}
      <div className="flex flex-wrap gap-2">
        {/* Direction pills */}
        <div className="flex rounded-md border border-border text-xs">
          {(['all', 'in', 'out'] as const).map((d) => {
            const active = (d === 'all' && filters.direction === null) || filters.direction === d
            const label = d === 'all' ? 'todas' : d === 'in' ? 'entradas' : 'saídas'
            return (
              <button
                key={d}
                type="button"
                onClick={() => onChange({ direction: d === 'all' ? null : d })}
                className={cn(
                  'px-3 py-1.5 transition-colors first:rounded-l-md last:rounded-r-md',
                  active
                    ? 'bg-primary text-primary-foreground font-medium'
                    : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                )}
              >
                {label}
              </button>
            )
          })}
        </div>

        {/* Account select */}
        {accounts.length > 0 && (
          <select
            value={filters.accountId ?? ''}
            onChange={(e) =>
              onChange({ accountId: e.target.value ? Number(e.target.value) : null })
            }
            className="h-8 rounded-md border border-border bg-background px-2.5 text-xs text-foreground"
          >
            <option value="">todas as contas</option>
            {accounts.map((a) => (
              <option key={a.id} value={a.id}>
                {a.name}
              </option>
            ))}
          </select>
        )}

        {/* Category select */}
        {categories.length > 0 && (
          <select
            value={filters.categoryId ?? ''}
            onChange={(e) =>
              onChange({ categoryId: e.target.value ? Number(e.target.value) : null })
            }
            className="h-8 rounded-md border border-border bg-background px-2.5 text-xs text-foreground"
          >
            <option value="">todas as categorias</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        )}

        {/* Search */}
        <Input
          value={filters.search}
          onChange={(e) => onChange({ search: e.target.value })}
          placeholder="buscar descrição..."
          className="h-8 text-xs w-48"
        />
      </div>
    </div>
  )
}
