import { useState } from 'react'
import { Plus, Trash2, Loader2, Target, Shield, Wand2, ArrowUpCircle } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Money } from '@/components/Money'
import {
  useGoals,
  useCreateGoal,
  useDeleteGoal,
  useDepositGoal,
  useEmergencyFund,
  useAutoTargetEmergency,
} from '@/hooks/queries/useGoals'
import type { Goal } from '@/api/endpoints/goals'

function GoalCard({ goal }: { goal: Goal }) {
  const deleteGoal = useDeleteGoal()
  const deposit = useDepositGoal()
  const [depositOpen, setDepositOpen] = useState(false)
  const [depositAmount, setDepositAmount] = useState('')

  const handleDeposit = async (e: React.FormEvent) => {
    e.preventDefault()
    const amt = Number(depositAmount)
    if (!amt || amt <= 0) return
    await deposit.mutateAsync({ id: goal.id, amount: amt })
    setDepositAmount('')
    setDepositOpen(false)
  }

  return (
    <div className="rounded-lg border p-4 space-y-3 bg-card">
      <div className="flex items-start justify-between gap-3">
        <div className="flex items-start gap-3 min-w-0">
          {goal.is_emergency_fund ? (
            <Shield className="w-5 h-5 text-blue-500 shrink-0 mt-0.5" />
          ) : (
            <Target className="w-5 h-5 text-primary shrink-0 mt-0.5" />
          )}
          <div className="min-w-0">
            <div className="flex items-center gap-2">
              <h3 className="font-medium truncate">{goal.name}</h3>
              {goal.achieved_at && <Badge variant="secondary" className="text-xs">conquistado</Badge>}
              {goal.is_emergency_fund && <Badge variant="outline" className="text-xs">reserva</Badge>}
            </div>
            <p className="text-xs text-muted-foreground mt-0.5">
              <Money value={goal.current_amount} /> de <Money value={goal.target_amount} />
              {goal.target_date && (
                <> · meta {new Date(goal.target_date + 'T00:00:00').toLocaleDateString('pt-BR')}</>
              )}
            </p>
          </div>
        </div>
        <div className="flex gap-1 shrink-0">
          <Button size="sm" variant="ghost" title="Aporte" onClick={() => setDepositOpen(!depositOpen)}>
            <ArrowUpCircle className="w-4 h-4" />
          </Button>
          <Button
            size="sm" variant="ghost"
            className="text-muted-foreground hover:text-destructive"
            onClick={() => {
              if (window.confirm(`Excluir "${goal.name}"?`)) deleteGoal.mutate(goal.id)
            }}
          >
            <Trash2 className="w-4 h-4" />
          </Button>
        </div>
      </div>

      {/* Progress bar */}
      <div className="space-y-1.5">
        <div className="h-2 bg-muted rounded-full overflow-hidden">
          <div
            className={`h-full transition-all ${goal.achieved_at ? 'bg-green-500' : 'bg-primary'}`}
            style={{ width: `${Math.min(100, goal.progress_pct)}%` }}
          />
        </div>
        <div className="flex justify-between text-xs text-muted-foreground">
          <span className="tabular-nums">{goal.progress_pct.toFixed(1)}%</span>
          {goal.monthly_needed && (
            <span><Money value={goal.monthly_needed} />/mês para bater em {goal.months_left} meses</span>
          )}
        </div>
      </div>

      {depositOpen && (
        <form onSubmit={handleDeposit} className="flex gap-2 pt-2 border-t">
          <Input
            type="number" step="0.01" min="0" placeholder="Valor do aporte"
            value={depositAmount} onChange={(e) => setDepositAmount(e.target.value)}
            autoFocus
          />
          <Button type="submit" size="sm" disabled={deposit.isPending}>Aportar</Button>
          <Button type="button" size="sm" variant="ghost" onClick={() => setDepositOpen(false)}>X</Button>
        </form>
      )}
    </div>
  )
}

function CreateForm({ onClose }: { onClose: () => void }) {
  const create = useCreateGoal()
  const [name, setName] = useState('')
  const [targetAmount, setTargetAmount] = useState('')
  const [targetDate, setTargetDate] = useState('')
  const [isEmergencyFund, setIsEmergencyFund] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!name || !targetAmount) return
    await create.mutateAsync({
      name,
      target_amount: targetAmount,
      target_date: targetDate || null,
      is_emergency_fund: isEmergencyFund,
    })
    onClose()
  }

  return (
    <form onSubmit={handleSubmit} className="rounded-lg border p-4 space-y-3 bg-card">
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div className="space-y-1 sm:col-span-2">
          <Label>Nome</Label>
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Viagem para o Japão" />
        </div>
        <div className="space-y-1">
          <Label>Valor alvo (R$)</Label>
          <Input type="number" step="0.01" min="0" value={targetAmount} onChange={(e) => setTargetAmount(e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label>Data alvo (opcional)</Label>
          <Input type="date" value={targetDate} onChange={(e) => setTargetDate(e.target.value)} />
        </div>
        <label className="sm:col-span-2 flex items-center gap-2 text-sm cursor-pointer">
          <input type="checkbox" checked={isEmergencyFund} onChange={(e) => setIsEmergencyFund(e.target.checked)} />
          Esta é minha reserva de emergência
        </label>
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <Button type="button" variant="ghost" onClick={onClose}>Cancelar</Button>
        <Button type="submit" disabled={create.isPending}>{create.isPending ? 'Salvando...' : 'Criar'}</Button>
      </div>
    </form>
  )
}

export default function GoalsPage() {
  const { data: goals, isLoading } = useGoals()
  const { data: emergency } = useEmergencyFund()
  const autoTarget = useAutoTargetEmergency()
  const [creating, setCreating] = useState(false)

  return (
    <div className="max-w-3xl mx-auto py-10 px-4 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Objetivos</h1>
          <p className="text-sm text-muted-foreground mt-1">
            Metas de acúmulo + sua reserva de emergência.
          </p>
        </div>
        {!creating && (
          <Button onClick={() => setCreating(true)}>
            <Plus className="w-4 h-4 mr-1" /> Novo objetivo
          </Button>
        )}
      </div>

      {creating && <CreateForm onClose={() => setCreating(false)} />}

      {/* Emergency fund insights card */}
      {emergency && (
        <div className="rounded-lg border border-blue-200 dark:border-blue-900 bg-blue-50 dark:bg-blue-950/30 p-4 space-y-2">
          <div className="flex items-start justify-between gap-2">
            <div className="text-sm">
              <span className="font-medium text-blue-900 dark:text-blue-200">Reserva de emergência</span>
              <p className="text-xs text-muted-foreground mt-1">
                Burn rate (3m): <Money value={emergency.burn_rate_3m} />
                {emergency.coverage_months !== null && (
                  <> · Cobertura: <strong>{emergency.coverage_months} meses</strong></>
                )}
              </p>
            </div>
            <Button
              size="sm" variant="outline"
              onClick={async () => {
                if (window.confirm('Recalcular alvo da reserva como 6× burn rate dos últimos 6 meses?')) {
                  const r = await autoTarget.mutateAsync()
                  alert(`Novo alvo: R$ ${r.data.target_amount} (baseado em burn rate de R$ ${r.computed_from.burn_rate_6m})`)
                }
              }}
            >
              <Wand2 className="w-4 h-4 mr-1" /> Auto-target
            </Button>
          </div>
        </div>
      )}

      {/* Goals list */}
      {isLoading ? (
        <div className="text-center py-12 text-muted-foreground">
          <Loader2 className="w-5 h-5 mx-auto animate-spin" />
        </div>
      ) : (goals ?? []).length === 0 ? (
        <div className="text-center py-12 text-muted-foreground space-y-2">
          <Target className="w-8 h-8 mx-auto opacity-40" />
          <p className="text-sm">Nenhum objetivo cadastrado.</p>
          <p className="text-xs">Comece criando sua reserva de emergência.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
          {(goals ?? []).map((goal) => <GoalCard key={goal.id} goal={goal} />)}
        </div>
      )}
    </div>
  )
}
