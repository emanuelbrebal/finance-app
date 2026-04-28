import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useCreateAccount } from '@/hooks/queries/useAccounts'
import {
  ACCOUNT_TYPES,
  ACCOUNT_TYPE_LABELS,
  CreateAccountSchema,
  type AccountType,
} from '@/lib/validators/account'

interface AccountFormProps {
  onSuccess?: () => void
  onCancel?: () => void
}

export function AccountForm({ onSuccess, onCancel }: AccountFormProps) {
  const [name, setName] = useState('')
  const [type, setType] = useState<AccountType>('checking')
  const [initialBalance, setInitialBalance] = useState('')
  const [color, setColor] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})

  const createMutation = useCreateAccount()

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    const parsed = CreateAccountSchema.safeParse({
      name,
      type,
      initial_balance: initialBalance,
      color,
    })

    if (!parsed.success) {
      setErrors(flattenZodErrors(parsed.error.issues))
      return
    }

    const payload = {
      name: parsed.data.name,
      type: parsed.data.type,
      ...(parsed.data.initial_balance ? { initial_balance: parsed.data.initial_balance } : {}),
      ...(parsed.data.color ? { color: parsed.data.color } : {}),
    }

    createMutation.mutate(payload, {
      onSuccess: () => {
        setName('')
        setInitialBalance('')
        setColor('')
        onSuccess?.()
      },
      onError: (err) => setErrors(extractServerErrors(err)),
    })
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-6">
      <div className="space-y-1">
        <h3 className="text-base font-semibold">nova conta</h3>
        <p className="text-xs text-muted-foreground">cadastre uma conta para acompanhar movimentações</p>
      </div>

      <div className="space-y-1">
        <Label htmlFor="name">nome</Label>
        <Input
          id="name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Nubank, Carteira, ..."
        />
        {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="type">tipo</Label>
        <select
          id="type"
          value={type}
          onChange={(e) => setType(e.target.value as AccountType)}
          className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
        >
          {ACCOUNT_TYPES.map((t) => (
            <option key={t} value={t}>
              {ACCOUNT_TYPE_LABELS[t]}
            </option>
          ))}
        </select>
        {errors.type && <p className="text-xs text-destructive">{errors.type}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="initial_balance">saldo inicial</Label>
        <Input
          id="initial_balance"
          inputMode="decimal"
          value={initialBalance}
          onChange={(e) => setInitialBalance(e.target.value.replace(',', '.'))}
          placeholder="0.00"
        />
        {errors.initial_balance && <p className="text-xs text-destructive">{errors.initial_balance}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="color">cor (opcional)</Label>
        <Input
          id="color"
          value={color}
          onChange={(e) => setColor(e.target.value)}
          placeholder="#820AD1"
        />
        {errors.color && <p className="text-xs text-destructive">{errors.color}</p>}
      </div>

      {errors._root && <p className="text-xs text-destructive">{errors._root}</p>}

      <div className="flex gap-2">
        <Button type="submit" disabled={createMutation.isPending} className="flex-1">
          {createMutation.isPending ? 'salvando...' : 'criar conta'}
        </Button>
        {onCancel && (
          <Button type="button" variant="outline" onClick={onCancel}>
            cancelar
          </Button>
        )}
      </div>
    </form>
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
    const data = (err as { response: { data: { message?: string; errors?: Record<string, string[]> } } })
      .response.data
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
