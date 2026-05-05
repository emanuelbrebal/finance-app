import { useState } from 'react'
import { Plus, Trash2, Play, Loader2, Repeat, Power, PowerOff } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Money } from '@/components/Money'
import {
  useRecurringTransactions,
  useCreateRecurring,
  useUpdateRecurring,
  useDeleteRecurring,
  useGenerateRecurringNow,
} from '@/hooks/queries/useRecurringTransactions'
import { useAccounts } from '@/hooks/queries/useAccounts'
import { useCategories } from '@/hooks/queries/useCategories'

function CreateForm({ onClose }: { onClose: () => void }) {
  const { data: accounts } = useAccounts()
  const { data: categories } = useCategories({})
  const create = useCreateRecurring()

  const [description, setDescription] = useState('')
  const [amount, setAmount] = useState('')
  const [direction, setDirection] = useState<'in' | 'out'>('out')
  const [accountId, setAccountId] = useState('')
  const [categoryId, setCategoryId] = useState('')
  const [dayOfMonth, setDayOfMonth] = useState('1')
  const [startsOn, setStartsOn] = useState(new Date().toISOString().slice(0, 10))

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!description || !amount || !accountId) return

    await create.mutateAsync({
      description,
      amount,
      direction,
      account_id: Number(accountId),
      category_id: categoryId ? Number(categoryId) : null,
      day_of_month: Number(dayOfMonth),
      starts_on: startsOn,
    })
    onClose()
  }

  return (
    <form onSubmit={handleSubmit} className="rounded-lg border p-4 space-y-3 bg-card">
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div className="space-y-1 sm:col-span-2">
          <Label>Descrição</Label>
          <Input value={description} onChange={(e) => setDescription(e.target.value)} placeholder="Ex: Aluguel" />
        </div>
        <div className="space-y-1">
          <Label>Valor (R$)</Label>
          <Input type="number" step="0.01" min="0" value={amount} onChange={(e) => setAmount(e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label>Direção</Label>
          <Select value={direction} onValueChange={(v) => setDirection(v as 'in' | 'out')}>
            <SelectTrigger><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem value="out">Saída</SelectItem>
              <SelectItem value="in">Entrada</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <Label>Conta</Label>
          <Select value={accountId} onValueChange={setAccountId}>
            <SelectTrigger><SelectValue placeholder="Selecione..." /></SelectTrigger>
            <SelectContent>
              {(accounts ?? []).map((a) => <SelectItem key={a.id} value={String(a.id)}>{a.name}</SelectItem>)}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <Label>Categoria (opcional)</Label>
          <Select value={categoryId} onValueChange={setCategoryId}>
            <SelectTrigger><SelectValue placeholder="Sem categoria" /></SelectTrigger>
            <SelectContent>
              {(categories ?? []).map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <Label>Dia do mês</Label>
          <Input type="number" min="1" max="31" value={dayOfMonth} onChange={(e) => setDayOfMonth(e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label>Início</Label>
          <Input type="date" value={startsOn} onChange={(e) => setStartsOn(e.target.value)} />
        </div>
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <Button type="button" variant="ghost" onClick={onClose}>Cancelar</Button>
        <Button type="submit" disabled={create.isPending}>
          {create.isPending ? 'Salvando...' : 'Criar'}
        </Button>
      </div>
    </form>
  )
}

export default function RecurringTransactionsPage() {
  const { data: items, isLoading } = useRecurringTransactions()
  const deleteRec = useDeleteRecurring()
  const generateNow = useGenerateRecurringNow()
  const [creating, setCreating] = useState(false)

  return (
    <div className="max-w-3xl mx-auto py-10 px-4 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Recorrentes</h1>
          <p className="text-sm text-muted-foreground mt-1">
            Templates que geram transações automaticamente todo mês.
          </p>
        </div>
        {!creating && (
          <Button onClick={() => setCreating(true)}>
            <Plus className="w-4 h-4 mr-1" /> Novo
          </Button>
        )}
      </div>

      {creating && <CreateForm onClose={() => setCreating(false)} />}

      {isLoading ? (
        <div className="text-center py-12 text-muted-foreground">
          <Loader2 className="w-5 h-5 mx-auto animate-spin" />
        </div>
      ) : (items ?? []).length === 0 ? (
        <div className="text-center py-12 text-muted-foreground space-y-2">
          <Repeat className="w-8 h-8 mx-auto opacity-40" />
          <p className="text-sm">Nenhum recorrente cadastrado.</p>
          <p className="text-xs">Cadastre aluguel, salário, assinaturas para automatizar.</p>
        </div>
      ) : (
        <div className="space-y-1">
          {(items ?? []).map((rt) => (
            <div key={rt.id} className="flex items-center gap-3 px-4 py-3 hover:bg-muted/20 rounded-lg">
              <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2">
                  <p className="font-medium text-sm truncate">{rt.description}</p>
                  {!rt.active && <Badge variant="outline" className="text-xs">inativo</Badge>}
                </div>
                <p className="text-xs text-muted-foreground">
                  Dia {rt.day_of_month} · {rt.account?.name}
                  {rt.category && <> · <span style={{ color: rt.category.color }}>{rt.category.name}</span></>}
                </p>
              </div>
              <div className={rt.direction === 'in' ? 'text-green-600 font-medium' : 'font-medium'}>
                {rt.direction === 'out' ? '−' : '+'}<Money value={rt.amount} className={rt.direction === 'in' ? 'text-green-600' : ''} />
              </div>
              <div className="flex gap-1">
                {rt.active && (
                  <Button
                    size="sm"
                    variant="ghost"
                    title="Gerar agora"
                    disabled={generateNow.isPending}
                    onClick={async () => {
                      const result = await generateNow.mutateAsync(rt.id)
                      if (result?.message) alert(result.message)
                    }}
                  >
                    <Play className="w-4 h-4" />
                  </Button>
                )}
                <Button
                  size="sm"
                  variant="ghost"
                  className="text-muted-foreground hover:text-destructive"
                  onClick={() => {
                    if (window.confirm(`Desativar "${rt.description}"?`)) {
                      deleteRec.mutate(rt.id)
                    }
                  }}
                >
                  {rt.active ? <PowerOff className="w-4 h-4" /> : <Trash2 className="w-4 h-4" />}
                </Button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  )
}
