import {
  Area,
  CartesianGrid,
  ComposedChart,
  Line,
  ReferenceLine,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts'
import { TrendingUp, Loader2 } from 'lucide-react'
import { useNetWorthEvolution } from '@/hooks/queries/useCharts'

const PT_MONTHS = ['jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez']

function formatLabel(date: string) {
  const d = new Date(date + 'T00:00:00')
  return `${PT_MONTHS[d.getMonth()]} ${String(d.getFullYear()).slice(2)}`
}

export function NetWorthEvolutionChart() {
  const { data, isLoading } = useNetWorthEvolution()

  if (isLoading) {
    return (
      <div className="rounded-lg border bg-card p-5 col-span-full h-72 flex items-center justify-center text-muted-foreground">
        <Loader2 className="w-5 h-5 animate-spin" />
      </div>
    )
  }

  if (!data || data.history.length === 0) {
    return (
      <div className="rounded-lg border bg-card p-5 col-span-full h-72 flex flex-col items-center justify-center text-muted-foreground gap-2">
        <TrendingUp className="w-8 h-8 opacity-40" />
        <p className="text-sm">Sem snapshots de patrimônio ainda.</p>
        <p className="text-xs">O primeiro snapshot é capturado no fim do mês corrente.</p>
      </div>
    )
  }

  const merged = [
    ...data.history.map((p) => ({
      label: formatLabel(p.date),
      historical: Number(p.value),
      projected: null as number | null,
    })),
    ...data.projection.map((p) => ({
      label: formatLabel(p.date),
      historical: null as number | null,
      projected: Number(p.value),
    })),
  ]

  const target = data.target ? Number(data.target) : null

  return (
    <div className="rounded-lg border bg-card p-5 col-span-full space-y-3">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <TrendingUp className="w-3.5 h-3.5" /> Evolução patrimonial
      </div>

      <ResponsiveContainer width="100%" height={260}>
        <ComposedChart data={merged} margin={{ top: 8, right: 12, left: 12, bottom: 8 }}>
          <CartesianGrid stroke="currentColor" strokeOpacity={0.1} vertical={false} />
          <XAxis
            dataKey="label"
            tick={{ fontSize: 10 }}
            stroke="currentColor"
            strokeOpacity={0.4}
          />
          <YAxis
            tick={{ fontSize: 10 }}
            stroke="currentColor"
            strokeOpacity={0.4}
            tickFormatter={(v) => `R$ ${(v / 1000).toFixed(0)}k`}
          />
          <Tooltip
            contentStyle={{ background: 'hsl(var(--card))', border: '1px solid hsl(var(--border))', borderRadius: 6, fontSize: 12 }}
            formatter={(v: number | null) =>
              v == null ? '—' : `R$ ${v.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`
            }
          />
          {target && (
            <ReferenceLine
              y={target}
              stroke="hsl(var(--primary))"
              strokeDasharray="3 3"
              label={{ value: 'meta', position: 'right', fill: 'hsl(var(--primary))', fontSize: 10 }}
            />
          )}
          <Line type="monotone" dataKey="historical" stroke="#10b981" strokeWidth={2} dot={false} connectNulls />
          <Line type="monotone" dataKey="projected" stroke="#10b981" strokeWidth={2} strokeDasharray="5 4" dot={false} connectNulls />
        </ComposedChart>
      </ResponsiveContainer>

      <p className="text-xs text-muted-foreground">
        Linha sólida = histórico real · Linha tracejada = projeção até a data-alvo
      </p>
    </div>
  )
}
