import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useCreateAccount, useUpdateAccount } from '@/hooks/queries/useAccounts'
import {
  ACCOUNT_TYPES,
  ACCOUNT_TYPE_LABELS,
  CreateAccountSchema,
  UpdateAccountSchema,
  type AccountType,
} from '@/lib/validators/account'
import type { Account } from '@/api/endpoints/accounts'

interface AccountFormProps {
  /** When provided, the form runs in edit mode for this account */
  account?: Account
  onSuccess?: () => void
  onCancel?: () => void
}

export function AccountForm({ account, onSuccess, onCancel }: AccountFormProps) {
  const isEdit = account !== undefined

  const [name, setName] = useState(account?.name ?? '')
  const [type, setType] = useState<AccountType>(account?.type ?? 'checking')
  const [initialBalance, setInitialBalance] = useState(account?.initial_balance ?? '')
  const [color, setColor] = useState(account?.color ?? '')
  const [errors, setErrors] = useState<Record<string, string>>({})

  const createMutation = useCreateAccount()
  const updateMutation = useUpdateAccount(account?.id ?? 0)

  const isPending = isEdit ? updateMutation.isPending : createMutation.isPending

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    const raw = { name, type, initial_balance: initialBalance, color }

    if (isEdit) {
      const parsed = UpdateAccountSchema.safeParse(raw)
      if (!parsed.success) {
        setErrors(flattenZodErrors(parsed.error.issues))
        return
      }
      updateMutation.mutate(parsed.data, {
        onSuccess: () => onSuccess?.(),
        onError: (err) => setErrors(extractServerErrors(err)),
      })
    } else {
      const parsed = CreateAccountSchema.safeParse(raw)
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
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-5">
      <h3 className="text-sm font-semibold">
        {isEdit ? 'editar conta' : 'nova conta'}
      </h3>

      <div className="space-y-1">
        <Label htmlFor="acc-name">nome</Label>
        <Input
          id="acc-name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Nubank, Carteira, ..."
        />
        {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="acc-type">tipo</Label>
        <select
          id="acc-type"
          value={type}
          onChange={(e) => setType(e.target.value as AccountType)}
          className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
        >
          {ACCOUNT_TYPES.map((t) => (
            <option key={t} value={t}>{ACCOUNT_TYPE_LABELS[t]}</option>
          ))}
        </select>
        {errors.type && <p className="text-xs text-destructive">{errors.type}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="acc-balance">saldo inicial</Label>
        <Input
          id="acc-balance"
          inputMode="decimal"
          value={initialBalance}
          onChange={(e) => setInitialBalance(e.target.value.replace(',', '.'))}
          placeholder="0.00"
        />
        {errors.initial_balance && (
          <p className="text-xs text-destructive">{errors.initial_balance}</p>
        )}
      </div>

      <div className="space-y-1">
        <Label htmlFor="acc-color">cor (opcional)</Label>
        <div className="flex items-center gap-2">
          <Input
            id="acc-color"
            value={color}
            onChange={(e) => setColor(e.target.value)}
            placeholder="#820AD1"
            className="flex-1"
          />
          {color && /^#[0-9A-Fa-f]{6}$/.test(color) && (
            <span
              className="h-8 w-8 shrink-0 rounded border border-border"
              style={{ backgroundColor: color }}
            />
          )}
        </div>
        {errors.color && <p className="text-xs text-destructive">{errors.color}</p>}
      </div>

      {errors._root && <p className="text-xs text-destructive">{errors._root}</p>}

      <div className="flex gap-2">
        <Button type="submit" disabled={isPending} className="flex-1">
          {isPending ? 'salvando...' : isEdit ? 'salvar alterações' : 'criar conta'}
        </Button>
        {onCancel && (
          <Button type="button" variant="outline" onClick={onCancel}>cancelar</Button>
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
    typeof err === 'object' && err !== null && 'response' in err &&
    typeof (err as { response?: { data?: unknown } }).response?.data === 'object'
  ) {
    const data = (err as { response: { data: { message?: string; errors?: Record<string, string[]> } } }).response.data
    if (data.errors) {
      return Object.fromEntries(Object.entries(data.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : String(v)]))
    }
    if (data.message) return { _root: data.message }
  }
  return { _root: 'erro inesperado' }
}
