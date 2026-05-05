import { Link } from 'react-router-dom'
import { Shield, Plus } from 'lucide-react'
import { Money } from '@/components/Money'
import { useEmergencyFund } from '@/hooks/queries/useGoals'

export function EmergencyFundWidget() {
  const { data: fund, isLoading } = useEmergencyFund()

  if (isLoading) return null

  if (!fund) {
    return (
      <Link
        to="/goals"
        className="flex items-center gap-3 rounded-lg border border-dashed border-border p-4 hover:bg-muted/30 transition-colors"
      >
        <Shield className="w-5 h-5 text-muted-foreground" />
        <div className="flex-1">
          <p className="text-sm font-medium">Configure sua reserva de emergência</p>
          <p className="text-xs text-muted-foreground mt-0.5">
            Recomendado: 6× seu burn rate mensal
          </p>
        </div>
        <Plus className="w-4 h-4 text-muted-foreground" />
      </Link>
    )
  }

  const coverageColor =
    fund.coverage_months === null ? 'text-muted-foreground' :
    fund.coverage_months >= 6 ? 'text-green-600' :
    fund.coverage_months >= 3 ? 'text-amber-600' : 'text-rose-500'

  return (
    <Link
      to="/goals"
      className="block rounded-lg border border-blue-200 dark:border-blue-900 bg-blue-50/50 dark:bg-blue-950/20 p-4 hover:bg-blue-50 dark:hover:bg-blue-950/40 transition-colors"
    >
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-center gap-2">
          <Shield className="w-5 h-5 text-blue-500" />
          <div>
            <p className="text-sm font-medium">Reserva de emergência</p>
            <p className="text-xs text-muted-foreground mt-0.5">
              <Money value={fund.current_amount} /> de <Money value={fund.target_amount} />
            </p>
          </div>
        </div>
        {fund.coverage_months !== null && (
          <div className="text-right">
            <p className={`text-lg font-semibold tabular-nums ${coverageColor}`}>
              {fund.coverage_months}m
            </p>
            <p className="text-xs text-muted-foreground">cobertura</p>
          </div>
        )}
      </div>

      <div className="h-1.5 mt-3 bg-blue-100 dark:bg-blue-900/40 rounded-full overflow-hidden">
        <div
          className="h-full bg-blue-500 transition-all"
          style={{ width: `${Math.min(100, fund.progress_pct)}%` }}
        />
      </div>
    </Link>
  )
}
