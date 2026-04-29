import { TrendingUp, Flame, Layers, PiggyBank, CalendarDays } from 'lucide-react'
import { Money } from '@/components/Money'
import { MonthlyChart } from '@/components/charts/MonthlyChart'
import { EmergencyFundWidget } from '@/components/EmergencyFundWidget'
import { useDashboard } from '@/hooks/queries/useDashboard'
import { cn } from '@/lib/utils'
import type { DashboardData, TopExpense } from '@/api/endpoints/dashboard'
import type { Transaction } from '@/api/endpoints/transactions'

// ── Helpers ───────────────────────────────────────────────────────────────────

function ptMonth(yearMonth: string) {
  const [y, m] = yearMonth.split('-')
  const months = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez']
  return `${months[parseInt(m) - 1]} ${y}`
}

function Bar({ pct, color }: { pct: number; color: string | null }) {
  return (
    <div className="h-1.5 w-full overflow-hidden rounded-full bg-border">
      <div
        className="h-full rounded-full transition-all"
        style={{ width: `${Math.min(pct, 100)}%`, backgroundColor: color ?? '#94a3b8' }}
      />
    </div>
  )
}

// ── Widgets ───────────────────────────────────────────────────────────────────

function NetWorthCard({ data }: { data: DashboardData }) {
  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-3 col-span-2">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <TrendingUp className="h-3.5 w-3.5" />
        patrimônio líquido
      </div>
      <Money value={data.net_worth} className="text-3xl font-bold tracking-tight" />
      {data.net_worth_by_account.length > 0 && (
        <ul className="space-y-1.5 pt-1">
          {data.net_worth_by_account.map((a) => (
            <li key={a.account_id} className="flex items-center justify-between text-xs">
              <span className="flex items-center gap-1.5">
                <span
                  className="inline-block h-2 w-2 rounded-full"
                  style={{ backgroundColor: a.color ?? '#94a3b8' }}
                />
                <span className="text-muted-foreground">{a.name}</span>
              </span>
              <Money value={a.balance} className="tabular-nums" />
            </li>
          ))}
        </ul>
      )}
    </div>
  )
}

function MonthCard({ data }: { data: DashboardData }) {
  const rate = data.savings_rate
  const rateColor =
    rate === null ? 'text-muted-foreground'
    : rate >= 20 ? 'text-emerald-500'
    : rate >= 0  ? 'text-amber-500'
    : 'text-rose-500'

  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-4">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <CalendarDays className="h-3.5 w-3.5" />
        {ptMonth(data.month)}
      </div>
      <div className="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
        <div>
          <p className="text-xs text-muted-foreground">entradas</p>
          <Money value={data.income} className="font-semibold text-emerald-500" />
        </div>
        <div>
          <p className="text-xs text-muted-foreground">saídas</p>
          <Money value={data.expenses} className="font-semibold text-rose-500" />
        </div>
        <div>
          <p className="text-xs text-muted-foreground">guardado</p>
          <Money value={data.saved} className="font-semibold" />
        </div>
        <div>
          <p className="text-xs text-muted-foreground">taxa de poupança</p>
          <p className={cn('font-semibold', rateColor)}>
            {rate !== null ? `${rate}%` : '—'}
          </p>
        </div>
      </div>
    </div>
  )
}

function BurnRunwayCard({ data }: { data: DashboardData }) {
  const runway = data.runway_months

  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-4">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <Flame className="h-3.5 w-3.5" />
        burn rate &amp; runway
      </div>
      <div className="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
        <div>
          <p className="text-xs text-muted-foreground">
            gasto médio ({data.burn_rate_months_sampled}m)
          </p>
          <Money value={data.burn_rate_3m} className="font-semibold" />
        </div>
        <div>
          <p className="text-xs text-muted-foreground">runway</p>
          <p className={cn('font-semibold', runway !== null && runway < 3 ? 'text-rose-500' : runway !== null && runway < 6 ? 'text-amber-500' : 'text-emerald-500')}>
            {runway !== null ? `${runway} meses` : '—'}
          </p>
        </div>
      </div>
    </div>
  )
}

function TopExpensesCard({ expenses }: { expenses: TopExpense[] }) {
  if (expenses.length === 0) {
    return (
      <div className="rounded-lg border border-border bg-card p-5 space-y-3 col-span-2">
        <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
          <Layers className="h-3.5 w-3.5" />
          top gastos do mês
        </div>
        <p className="text-xs text-muted-foreground">nenhuma saída registrada ainda.</p>
      </div>
    )
  }

  const max = Math.max(...expenses.map((e) => parseFloat(e.total)))

  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-3 col-span-2">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <Layers className="h-3.5 w-3.5" />
        top gastos do mês
      </div>
      <ul className="space-y-2.5">
        {expenses.map((e) => (
          <li key={e.category_id ?? 'uncategorized'} className="space-y-1">
            <div className="flex items-center justify-between text-sm">
              <span className="flex items-center gap-1.5">
                <span
                  className="inline-block h-2 w-2 rounded-full"
                  style={{ backgroundColor: e.category_color ?? '#94a3b8' }}
                />
                {e.category_name}
                <span className="text-xs text-muted-foreground">({e.count})</span>
              </span>
              <Money value={e.total} className="tabular-nums text-sm font-medium" />
            </div>
            <Bar pct={(parseFloat(e.total) / max) * 100} color={e.category_color} />
          </li>
        ))}
      </ul>
    </div>
  )
}

function RecentTransactions({ transactions }: { transactions: Transaction[] }) {
  if (transactions.length === 0) {
    return (
      <div className="rounded-lg border border-border bg-card p-5 col-span-full">
        <p className="text-xs text-muted-foreground">nenhuma movimentação recente.</p>
      </div>
    )
  }

  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-3 col-span-full">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <PiggyBank className="h-3.5 w-3.5" />
        últimas movimentações
      </div>
      <ul className="divide-y divide-border">
        {transactions.map((tx) => (
          <li key={tx.id} className="flex items-center justify-between py-2">
            <div className="flex items-center gap-2 min-w-0">
              <span
                className={cn(
                  'shrink-0 inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold',
                  tx.direction === 'in'
                    ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                    : 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                )}
              >
                {tx.direction === 'in' ? '+' : '−'}
              </span>
              <div className="min-w-0">
                <p className="truncate text-sm">{tx.description}</p>
                <p className="text-xs text-muted-foreground">
                  {new Date(tx.occurred_on + 'T12:00:00').toLocaleDateString('pt-BR')}
                </p>
              </div>
            </div>
            <Money
              value={tx.amount}
              className={cn(
                'ml-4 shrink-0 text-sm font-medium tabular-nums',
                tx.direction === 'in' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400',
              )}
            />
          </li>
        ))}
      </ul>
    </div>
  )
}

// ── Page ──────────────────────────────────────────────────────────────────────

export function DashboardPage() {
  const { data, isLoading, isError } = useDashboard()

  if (isLoading) {
    return (
      <div className="flex h-64 items-center justify-center">
        <p className="text-xs text-muted-foreground">carregando dashboard...</p>
      </div>
    )
  }

  if (isError || !data) {
    return (
      <div className="flex h-64 items-center justify-center">
        <p className="text-xs text-destructive">erro ao carregar dashboard.</p>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 gap-4">
        <NetWorthCard data={data} />
        <MonthCard data={data} />
        <BurnRunwayCard data={data} />
        <TopExpensesCard expenses={data.top_expenses} />
        <div className="col-span-2">
          <EmergencyFundWidget />
        </div>
        <MonthlyChart />
        <RecentTransactions transactions={data.recent_transactions} />
      </div>
    </div>
  )
}
