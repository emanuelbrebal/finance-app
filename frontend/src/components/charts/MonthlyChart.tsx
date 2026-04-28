import {
  Bar,
  BarChart,
  CartesianGrid,
  Legend,
  Line,
  ComposedChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { TrendingUp } from 'lucide-react'
import { useMonthlyStats } from '@/hooks/queries/useTransactions'
import type { MonthlySummaryRow } from '@/api/endpoints/transactions'

// ── Data transform ────────────────────────────────────────────────────────────

const PT_MONTHS = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez']

function ptMonthShort(yearMonth: string): string {
  const [y, m] = yearMonth.split('-')
  return `${PT_MONTHS[parseInt(m) - 1]} ${y.slice(2)}`
}

interface ChartPoint {
  month: string
  income: number
  expenses: number
  saved: number
  rate: number | null
}

function toChartPoints(rows: MonthlySummaryRow[]): ChartPoint[] {
  const byMonth = new Map<string, { in: number; out: number }>()

  for (const row of rows) {
    const entry = byMonth.get(row.month) ?? { in: 0, out: 0 }
    entry[row.direction] = parseFloat(row.total)
    byMonth.set(row.month, entry)
  }

  return Array.from(byMonth.entries()).map(([month, v]) => {
    const saved = v.in - v.out
    return {
      month: ptMonthShort(month),
      income: v.in,
      expenses: v.out,
      saved,
      rate: v.in > 0 ? Math.round((saved / v.in) * 100) : null,
    }
  })
}

// ── Formatters ────────────────────────────────────────────────────────────────

const BRL = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 })

function formatK(value: number): string {
  if (Math.abs(value) >= 1000) return `R$${(value / 1000).toFixed(1)}k`
  return BRL.format(value)
}

// ── Component ─────────────────────────────────────────────────────────────────

export function MonthlyChart() {
  const { data: rows, isLoading, isError } = useMonthlyStats(6)

  if (isLoading) {
    return (
      <div className="rounded-lg border border-border bg-card p-5 col-span-full h-64 flex items-center justify-center">
        <p className="text-xs text-muted-foreground">carregando gráfico...</p>
      </div>
    )
  }

  if (isError || !rows || rows.length === 0) {
    return (
      <div className="rounded-lg border border-border bg-card p-5 col-span-full h-64 flex items-center justify-center">
        <p className="text-xs text-muted-foreground">sem dados suficientes para exibir o gráfico.</p>
      </div>
    )
  }

  const points = toChartPoints(rows)

  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-3 col-span-full">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <TrendingUp className="h-3.5 w-3.5" />
        entradas vs saídas — últimos 6 meses
      </div>

      <ResponsiveContainer width="100%" height={220}>
        <ComposedChart data={points} margin={{ top: 4, right: 16, left: 0, bottom: 0 }}>
          <CartesianGrid strokeDasharray="3 3" stroke="hsl(var(--border))" vertical={false} />

          <XAxis
            dataKey="month"
            tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }}
            axisLine={false}
            tickLine={false}
          />

          <YAxis
            yAxisId="brl"
            tickFormatter={formatK}
            tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }}
            axisLine={false}
            tickLine={false}
            width={56}
          />

          <YAxis
            yAxisId="pct"
            orientation="right"
            tickFormatter={(v) => `${v}%`}
            tick={{ fontSize: 11, fill: 'hsl(var(--muted-foreground))' }}
            axisLine={false}
            tickLine={false}
            width={40}
            domain={[-20, 100]}
          />

          <Tooltip
            contentStyle={{
              backgroundColor: 'hsl(var(--card))',
              border: '1px solid hsl(var(--border))',
              borderRadius: '0.5rem',
              fontSize: 12,
            }}
            formatter={(value, name) => {
              if (name === 'taxa (%)') return [`${value}%`, name]
              return [BRL.format(Number(value)), name]
            }}
          />

          <Legend
            wrapperStyle={{ fontSize: 11, paddingTop: 8 }}
            formatter={(value) => (
              <span style={{ color: 'hsl(var(--muted-foreground))' }}>{value}</span>
            )}
          />

          <Bar yAxisId="brl" dataKey="income" name="entradas" fill="#22c55e" radius={[3, 3, 0, 0]} maxBarSize={28} />
          <Bar yAxisId="brl" dataKey="expenses" name="saídas" fill="#f43f5e" radius={[3, 3, 0, 0]} maxBarSize={28} />

          <Line
            yAxisId="pct"
            type="monotone"
            dataKey="rate"
            name="taxa (%)"
            stroke="#818cf8"
            strokeWidth={2}
            dot={{ r: 3, fill: '#818cf8' }}
            connectNulls
          />
        </ComposedChart>
      </ResponsiveContainer>
    </div>
  )
}
