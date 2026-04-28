import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useAccounts } from '@/hooks/queries/useAccounts'
import { useCategories } from '@/hooks/queries/useCategories'
import { useCreateTransaction } from '@/hooks/queries/useTransactions'
import { CreateTransactionSchema, DIRECTIONS, DIRECTION_LABELS, type Direction } from '@/lib/validators/transaction'

interface TransactionFormProps {
  onSuccess?: () => void
  onCancel?: () => void
}

export function TransactionForm({ onSuccess, onCancel }: TransactionFormProps) {
  const today = new Date().toISOString().slice(0, 10)

  const [accountId, setAccountId] = useState<number | ''>('')
  const [categoryId, setCategoryId] = useState<number | ''>('')
  const [occurredOn, setOccurredOn] = useState(today)
  const [description, setDescription] = useState('')
  const [amount, setAmount] = useState('')
  const [direction, setDirection] = useState<Direction>('out')
  const [notes, setNotes] = useState('')
  const [errors, setErrors] = useState<Record<string, string>>({})

  const { data: accounts = [] } = useAccounts()
  const { data: categories = [] } = useCategories()
  const createMutation = useCreateTransaction()

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    const parsed = CreateTransactionSchema.safeParse({
      account_id: accountId === '' ? undefined : accountId,
      category_id: categoryId === '' ? null : categoryId,
      occurred_on: occurredOn,
      description,
      amount,
      direction,
      notes: notes || null,
    })

    if (!parsed.success) {
      setErrors(flattenZodErrors(parsed.error.issues))
      return
    }

    createMutation.mutate(parsed.data, {
      onSuccess: ({ created }) => {
        if (!created) {
          setErrors({ _root: 'transação duplicada — já foi registrada antes.' })
          return
        }
        setDescription('')
        setAmount('')
        setNotes('')
        onSuccess?.()
      },
      onError: (err) => setErrors(extractServerErrors(err)),
    })
  }

  const expenseCategories = categories.filter((c) => c.kind === 'expense')
  const incomeCategories = categories.filter((c) => c.kind === 'income')
  const filteredCategories = direction === 'out' ? expenseCategories : incomeCategories

  return (
    <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-6">
      <div className="space-y-1">
        <h3 className="text-base font-semibold">nova movimentação</h3>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-1">
          <Label htmlFor="tx-direction">tipo</Label>
          <select
            id="tx-direction"
            value={direction}
            onChange={(e) => setDirection(e.target.value as Direction)}
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
          >
            {DIRECTIONS.map((d) => (
              <option key={d} value={d}>{DIRECTION_LABELS[d]}</option>
            ))}
          </select>
        </div>
        <div className="space-y-1">
          <Label htmlFor="tx-date">data</Label>
          <Input
            id="tx-date"
            type="date"
            value={occurredOn}
            onChange={(e) => setOccurredOn(e.target.value)}
          />
          {errors.occurred_on && <p className="text-xs text-destructive">{errors.occurred_on}</p>}
        </div>
      </div>

      <div className="space-y-1">
        <Label htmlFor="tx-description">descrição</Label>
        <Input
          id="tx-description"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          placeholder="Mercado, Salário, ..."
        />
        {errors.description && <p className="text-xs text-destructive">{errors.description}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="tx-amount">valor (R$)</Label>
        <Input
          id="tx-amount"
          inputMode="decimal"
          value={amount}
          onChange={(e) => setAmount(e.target.value.replace(',', '.'))}
          placeholder="0.00"
        />
        {errors.amount && <p className="text-xs text-destructive">{errors.amount}</p>}
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-1">
          <Label htmlFor="tx-account">conta</Label>
          <select
            id="tx-account"
            value={accountId}
            onChange={(e) => setAccountId(e.target.value === '' ? '' : Number(e.target.value))}
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
          >
            <option value="">selecione</option>
            {accounts.map((a) => (
              <option key={a.id} value={a.id}>{a.name}</option>
            ))}
          </select>
          {errors.account_id && <p className="text-xs text-destructive">{errors.account_id}</p>}
        </div>
        <div className="space-y-1">
          <Label htmlFor="tx-category">categoria</Label>
          <select
            id="tx-category"
            value={categoryId}
            onChange={(e) => setCategoryId(e.target.value === '' ? '' : Number(e.target.value))}
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
          >
            <option value="">sem categoria</option>
            {filteredCategories.map((c) => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </div>
      </div>

      {errors._root && <p className="text-xs text-destructive">{errors._root}</p>}

      <div className="flex gap-2">
        <Button type="submit" disabled={createMutation.isPending} className="flex-1">
          {createMutation.isPending ? 'salvando...' : 'registrar'}
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
