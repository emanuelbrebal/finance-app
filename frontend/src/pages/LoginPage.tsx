import { useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { Moon, Sun } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useLogin, useMe } from '@/hooks/useAuth'
import { LoginSchema } from '@/lib/validators/auth'
import { useThemeContext } from '@/contexts/ThemeContext'

export function LoginPage() {
  const navigate = useNavigate()
  const { data: user, isLoading } = useMe()
  const { theme, toggle } = useThemeContext()
  const loginMutation = useLogin()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})

  useEffect(() => {
    if (user) navigate('/dashboard', { replace: true })
  }, [user, navigate])

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    const parsed = LoginSchema.safeParse({ email, password })
    if (!parsed.success) {
      setErrors(flattenZodErrors(parsed.error.issues))
      return
    }

    loginMutation.mutate(parsed.data, {
      onSuccess: () => navigate('/dashboard', { replace: true }),
      onError: (err) => setErrors(extractServerErrors(err)),
    })
  }

  if (isLoading) {
    return (
      <main className="min-h-screen bg-background text-foreground flex items-center justify-center">
        <p className="text-xs text-muted-foreground">verificando sessão...</p>
      </main>
    )
  }

  return (
    <main className="min-h-screen bg-background text-foreground flex items-center justify-center p-6">
      <div className="fixed top-4 right-4">
        <Button variant="ghost" size="sm" onClick={toggle} aria-label="alternar tema">
          {theme === 'dark' ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
        </Button>
      </div>

      <div className="max-w-sm w-full space-y-6">
        <header className="text-center space-y-1">
          <h1 className="text-2xl font-semibold tracking-tight">finance-app</h1>
          <p className="text-xs text-muted-foreground">plataforma de acúmulo e consciência de consumo</p>
        </header>

        <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-6">
          <div className="space-y-1">
            <h2 className="text-base font-semibold">entrar</h2>
            <p className="text-xs text-muted-foreground">use suas credenciais</p>
          </div>

          <div className="space-y-1">
            <Label htmlFor="email">email</Label>
            <Input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              autoComplete="email"
              placeholder="seu@email.com"
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
              autoComplete="current-password"
            />
            {errors.password && <p className="text-xs text-destructive">{errors.password}</p>}
          </div>

          {errors._root && <p className="text-xs text-destructive">{errors._root}</p>}

          <Button type="submit" className="w-full" disabled={loginMutation.isPending}>
            {loginMutation.isPending ? 'entrando...' : 'entrar'}
          </Button>

          <Link
            to="/register"
            className="block text-center text-xs text-muted-foreground hover:text-foreground transition-colors"
          >
            ainda não tenho conta — criar conta
          </Link>
        </form>
      </div>
    </main>
  )
}

// ── Helpers ───────────────────────────────────────────────────────────────────

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
    const data = (err as { response: { data: { message?: string; errors?: Record<string, string[]> } } })
      .response.data
    if (data.errors) {
      return Object.fromEntries(
        Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)]),
      )
    }
    if (data.message) return { _root: data.message }
  }
  return { _root: 'erro inesperado' }
}
