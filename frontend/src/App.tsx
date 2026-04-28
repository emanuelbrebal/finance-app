import { useState } from 'react'
import { useHealth } from '@/hooks/queries/useHealth'
import { useLogin, useLogout, useMe, useRegister } from '@/hooks/useAuth'
import { LoginSchema, RegisterSchema } from '@/lib/validators/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { AccountsPage } from '@/pages/AccountsPage'
import { CategoriesPage } from '@/pages/CategoriesPage'
import { TransactionsPage } from '@/pages/TransactionsPage'
import { cn } from '@/lib/utils'

const dot = (state: 'ok' | 'fail' | 'pending') =>
  cn('inline-block h-2 w-2 rounded-full', {
    'bg-emerald-500': state === 'ok',
    'bg-rose-500': state === 'fail',
    'bg-slate-400 animate-pulse': state === 'pending',
  })

function HealthCard() {
  const { data, isLoading, isError } = useHealth()
  const checks = data?.data.checks
  const status = isLoading ? 'carregando' : isError ? 'offline' : data?.data.status

  return (
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
    </section>
  )
}

function AuthForm() {
  const [mode, setMode] = useState<'login' | 'register'>('login')
  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})

  const loginMutation = useLogin()
  const registerMutation = useRegister()
  const isPending = loginMutation.isPending || registerMutation.isPending

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    if (mode === 'login') {
      const parsed = LoginSchema.safeParse({ email, password })
      if (!parsed.success) {
        setErrors(flattenZodErrors(parsed.error.issues))
        return
      }
      loginMutation.mutate(parsed.data, {
        onError: (err) => setErrors(extractServerErrors(err)),
      })
    } else {
      const parsed = RegisterSchema.safeParse({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      })
      if (!parsed.success) {
        setErrors(flattenZodErrors(parsed.error.issues))
        return
      }
      registerMutation.mutate(parsed.data, {
        onError: (err) => setErrors(extractServerErrors(err)),
      })
    }
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-6">
      <div className="space-y-1">
        <h2 className="text-lg font-semibold">{mode === 'login' ? 'entrar' : 'criar conta'}</h2>
        <p className="text-xs text-muted-foreground">
          {mode === 'login' ? 'use suas credenciais' : 'comece a juntar capital'}
        </p>
      </div>

      {mode === 'register' && (
        <div className="space-y-1">
          <Label htmlFor="name">nome</Label>
          <Input id="name" value={name} onChange={(e) => setName(e.target.value)} autoComplete="name" />
          {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
        </div>
      )}

      <div className="space-y-1">
        <Label htmlFor="email">email</Label>
        <Input
          id="email"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          autoComplete="email"
        />
        {errors.email && <p className="text-xs text-destructive">{errors.email}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="password">senha</Label>
        <Input
          id="password"
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          autoComplete={mode === 'login' ? 'current-password' : 'new-password'}
        />
        {errors.password && <p className="text-xs text-destructive">{errors.password}</p>}
      </div>

      {mode === 'register' && (
        <div className="space-y-1">
          <Label htmlFor="password_confirmation">confirmar senha</Label>
          <Input
            id="password_confirmation"
            type="password"
            value={passwordConfirmation}
            onChange={(e) => setPasswordConfirmation(e.target.value)}
            autoComplete="new-password"
          />
          {errors.password_confirmation && (
            <p className="text-xs text-destructive">{errors.password_confirmation}</p>
          )}
        </div>
      )}

      <Button type="submit" className="w-full" disabled={isPending}>
        {isPending ? 'enviando...' : mode === 'login' ? 'entrar' : 'criar conta'}
      </Button>

      <button
        type="button"
        onClick={() => {
          setMode(mode === 'login' ? 'register' : 'login')
          setErrors({})
        }}
        className="block w-full text-center text-xs text-muted-foreground hover:text-foreground"
      >
        {mode === 'login' ? 'ainda não tenho conta' : 'já tenho conta'}
      </button>
    </form>
  )
}

function AuthedView({ name, email }: { name: string; email: string }) {
  const logoutMutation = useLogout()
  return (
    <section className="space-y-6">
      <div className="rounded-lg border border-border p-4 flex items-center justify-between">
        <div>
          <p className="text-sm font-medium">{name}</p>
          <p className="text-xs text-muted-foreground">{email}</p>
        </div>
        <Button variant="outline" size="sm" onClick={() => logoutMutation.mutate()}>
          sair
        </Button>
      </div>
      <TransactionsPage />
      <AccountsPage />
      <CategoriesPage />
      <HealthCard />
    </section>
  )
}

function App() {
  const { data: user, isLoading } = useMe()

  return (
    <main className="min-h-screen bg-background text-foreground flex items-center justify-center p-6">
      <div className="max-w-xl w-full space-y-6">
        <header className="text-center space-y-2">
          <h1 className="text-3xl font-semibold tracking-tight">finance-app</h1>
          <p className="text-sm text-muted-foreground">
            plataforma de acúmulo e consciência de consumo
          </p>
        </header>

        {isLoading ? (
          <p className="text-center text-xs text-muted-foreground">verificando sessão...</p>
        ) : user ? (
          <AuthedView name={user.name} email={user.email} />
        ) : (
          <AuthForm />
        )}
      </div>
    </main>
  )
}

function flattenZodErrors(issues: { path: PropertyKey[]; message: string }[]): Record<string, string> {
  return issues.reduce<Record<string, string>>((acc, issue) => {
    const key = issue.path.map(String).join('.')
    if (!acc[key]) acc[key] = issue.message
    return acc
  }, {})
}

function extractServerErrors(err: unknown): Record<string, string> {
  if (
    typeof err === 'object' &&
    err !== null &&
    'response' in err &&
    typeof (err as { response?: { data?: unknown } }).response?.data === 'object'
  ) {
    const data = (err as { response: { data: { message?: string; errors?: Record<string, string[]> } } }).response
      .data
    if (data.errors) {
      return Object.fromEntries(
        Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)]),
      )
    }
    if (data.message) {
      return { _root: data.message }
    }
  }
  return { _root: 'erro inesperado' }
}

export default App
