import { useHealth } from '@/hooks/queries/useHealth'
import { cn } from '@/lib/utils'

const dot = (state: 'ok' | 'fail' | 'pending') =>
  cn('inline-block h-2 w-2 rounded-full', {
    'bg-emerald-500': state === 'ok',
    'bg-rose-500': state === 'fail',
    'bg-slate-400 animate-pulse': state === 'pending',
  })

function App() {
  const { data, isLoading, isError } = useHealth()

  const checks = data?.data.checks
  const status = isLoading ? 'carregando' : isError ? 'offline' : data?.data.status

  return (
    <main className="min-h-screen bg-background text-foreground flex items-center justify-center p-6">
      <div className="max-w-md w-full space-y-6">
        <header className="text-center space-y-2">
          <h1 className="text-3xl font-semibold tracking-tight">finance-app</h1>
          <p className="text-sm text-muted-foreground">
            Plataforma de acúmulo e consciência de consumo.
          </p>
        </header>

        <section className="rounded-lg border border-border p-4 space-y-3">
          <div className="flex items-center justify-between">
            <span className="text-sm font-medium">backend</span>
            <span className="text-xs uppercase tracking-wide text-muted-foreground">{status}</span>
          </div>

          <ul className="space-y-2 text-sm">
            <li className="flex items-center gap-2">
              <span className={dot(isLoading ? 'pending' : (checks?.app ?? 'fail'))} />
              <span>app</span>
            </li>
            <li className="flex items-center gap-2">
              <span className={dot(isLoading ? 'pending' : (checks?.database ?? 'fail'))} />
              <span>postgres</span>
            </li>
            <li className="flex items-center gap-2">
              <span className={dot(isLoading ? 'pending' : (checks?.redis ?? 'fail'))} />
              <span>redis</span>
            </li>
          </ul>

          {data?.data.timestamp && (
            <p className="text-xs text-muted-foreground pt-1 border-t border-border">
              último check: {new Date(data.data.timestamp).toLocaleTimeString('pt-BR')}
            </p>
          )}
        </section>
      </div>
    </main>
  )
}

export default App
