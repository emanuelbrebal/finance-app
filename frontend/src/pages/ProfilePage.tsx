import { useEffect, useState } from 'react'
import { CheckCircle2, User, Target } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { cn } from '@/lib/utils'
import { useMe } from '@/hooks/useAuth'
import { useUpdateProfile } from '@/hooks/mutations/useUpdateProfile'
import { updateProfileSchema } from '@/lib/validators/profile'

// ── Section wrapper ───────────────────────────────────────────────────────────

function Section({
  icon: Icon,
  title,
  children,
}: {
  icon: React.ComponentType<{ className?: string }>
  title: string
  children: React.ReactNode
}) {
  return (
    <div className="rounded-lg border border-border bg-card p-5 space-y-4">
      <div className="flex items-center gap-2 text-xs uppercase tracking-wide text-muted-foreground">
        <Icon className="h-3.5 w-3.5" />
        {title}
      </div>
      {children}
    </div>
  )
}

// ── Field ─────────────────────────────────────────────────────────────────────

function Field({
  id,
  label,
  error,
  children,
}: {
  id?: string
  label: string
  error?: string
  children: React.ReactNode
}) {
  return (
    <div className="space-y-1.5">
      <Label htmlFor={id} className="text-xs text-muted-foreground">
        {label}
      </Label>
      {children}
      {error && <p className="text-xs text-destructive">{error}</p>}
    </div>
  )
}

// ── Page ──────────────────────────────────────────────────────────────────────

export function ProfilePage() {
  const { data: user } = useMe()
  const mutation = useUpdateProfile()

  const [name, setName] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [passwordConfirm, setPasswordConfirm] = useState('')
  const [targetNetWorth, setTargetNetWorth] = useState('')
  const [targetDate, setTargetDate] = useState('')
  const [estimatedIncome, setEstimatedIncome] = useState('')

  const [errors, setErrors] = useState<Record<string, string>>({})
  const [saved, setSaved] = useState(false)

  // Populate once user data loads
  useEffect(() => {
    if (user) {
      setName(user.name)
      setEmail(user.email)
      setTargetNetWorth(user.target_net_worth ?? '')
      setTargetDate(user.target_date ?? '')
      setEstimatedIncome(user.estimated_monthly_income ?? '')
    }
  }, [user])

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})
    setSaved(false)

    const parsed = updateProfileSchema.safeParse({
      name,
      email,
      password: password || undefined,
      password_confirmation: passwordConfirm || undefined,
      target_net_worth: targetNetWorth || undefined,
      target_date: targetDate || undefined,
      estimated_monthly_income: estimatedIncome || undefined,
    })

    if (!parsed.success) {
      setErrors(flattenZodErrors(parsed.error.issues))
      return
    }

    const payload = { ...parsed.data }
    if (!payload.password) {
      delete payload.password
      delete payload.password_confirmation
    }

    mutation.mutate(payload, {
      onSuccess: () => {
        setPassword('')
        setPasswordConfirm('')
        setSaved(true)
        setTimeout(() => setSaved(false), 3000)
      },
      onError: (err) => setErrors(extractServerErrors(err)),
    })
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-4">

      {/* ── Dados pessoais ── */}
      <Section icon={User} title="dados pessoais">
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Field id="name" label="nome" error={errors.name}>
            <Input
              id="name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              placeholder="Seu nome"
            />
          </Field>
          <Field id="email" label="e-mail" error={errors.email}>
            <Input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              placeholder="seu@email.com"
            />
          </Field>
        </div>

        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Field id="password" label="nova senha" error={errors.password}>
            <Input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              placeholder="deixe em branco para não alterar"
              autoComplete="new-password"
            />
          </Field>
          <Field id="password_confirmation" label="confirmar senha" error={errors.password_confirmation}>
            <Input
              id="password_confirmation"
              type="password"
              value={passwordConfirm}
              onChange={(e) => setPasswordConfirm(e.target.value)}
              placeholder="repita a nova senha"
              autoComplete="new-password"
            />
          </Field>
        </div>
      </Section>

      {/* ── Objetivos financeiros ── */}
      <Section icon={Target} title="objetivos financeiros">
        <p className="text-xs text-muted-foreground -mt-2">
          usados para calcular projeções e runway. todos opcionais.
        </p>

        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <Field id="target_net_worth" label="patrimônio-alvo (R$)" error={errors.target_net_worth}>
            <Input
              id="target_net_worth"
              type="number"
              min="0"
              step="0.01"
              value={targetNetWorth}
              onChange={(e) => setTargetNetWorth(e.target.value)}
              placeholder="ex: 100000.00"
            />
          </Field>
          <Field id="target_date" label="data-alvo" error={errors.target_date}>
            <Input
              id="target_date"
              type="date"
              value={targetDate}
              onChange={(e) => setTargetDate(e.target.value)}
            />
          </Field>
        </div>

        <Field id="estimated_income" label="renda mensal estimada (R$)" error={errors.estimated_monthly_income}>
          <Input
            id="estimated_income"
            type="number"
            min="0"
            step="0.01"
            value={estimatedIncome}
            onChange={(e) => setEstimatedIncome(e.target.value)}
            placeholder="ex: 8000.00"
            className="sm:max-w-xs"
          />
        </Field>
      </Section>

      {/* ── Actions ── */}
      <div className="flex items-center gap-3">
        <Button type="submit" disabled={mutation.isPending} size="sm">
          {mutation.isPending ? 'salvando...' : 'salvar alterações'}
        </Button>

        {saved && (
          <span className={cn('flex items-center gap-1.5 text-xs text-emerald-600 dark:text-emerald-400')}>
            <CheckCircle2 className="h-3.5 w-3.5" />
            salvo com sucesso
          </span>
        )}

        {mutation.isError && !Object.keys(errors).length && (
          <span className="text-xs text-destructive">erro ao salvar. tente novamente.</span>
        )}
      </div>
    </form>
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
