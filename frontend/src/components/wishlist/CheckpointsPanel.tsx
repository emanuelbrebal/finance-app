import { CheckCircle2, XCircle, Clock } from 'lucide-react'
import type { Checkpoint } from '@/api/endpoints/wishlist'

function CheckpointRow({ checkpoint }: { checkpoint: Checkpoint }) {
  const isPassed = checkpoint.passed === true
  const isFailed = checkpoint.passed === false
  const isPending = checkpoint.passed === null

  const Icon = isPassed ? CheckCircle2 : isFailed ? XCircle : Clock
  const colorClass = isPassed
    ? 'text-green-500'
    : isFailed
    ? 'text-rose-500'
    : 'text-muted-foreground'
  const bgClass = isPassed
    ? 'bg-green-500/5 border-green-500/20'
    : isFailed
    ? 'bg-rose-500/5 border-rose-500/20'
    : 'bg-muted/30 border-border'

  return (
    <div className={`rounded-lg border p-3 ${bgClass}`}>
      <div className="flex items-start gap-3">
        <Icon className={`w-5 h-5 shrink-0 mt-0.5 ${colorClass}`} />
        <div className="flex-1 min-w-0">
          <p className="text-sm font-medium">{checkpoint.label}</p>
          <p className="text-xs text-muted-foreground mt-0.5">{checkpoint.reason}</p>
          {!isPending && checkpoint.progress_pct < 100 && (
            <div className="h-1 mt-2 bg-muted rounded-full overflow-hidden">
              <div
                className={isPassed ? 'h-full bg-green-500' : 'h-full bg-rose-400'}
                style={{ width: `${Math.min(100, checkpoint.progress_pct)}%` }}
              />
            </div>
          )}
        </div>
      </div>
    </div>
  )
}

export function CheckpointsPanel({ checkpoints }: { checkpoints: Checkpoint[] }) {
  return (
    <div className="space-y-2">
      {checkpoints.map((cp) => (
        <CheckpointRow key={cp.id} checkpoint={cp} />
      ))}
    </div>
  )
}
