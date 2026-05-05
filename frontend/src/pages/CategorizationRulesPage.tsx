import { useState } from 'react'
import { Plus, Trash2, Sparkles, Wand2, Loader2 } from 'lucide-react'
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
import {
  useCategorizationRules,
  useCreateRule,
  useDeleteRule,
  useApplyRuleToExisting,
} from '@/hooks/queries/useCategorizationRules'
import { useCategories } from '@/hooks/queries/useCategories'
import type { MatchType } from '@/api/endpoints/categorizationRules'

const MATCH_LABELS: Record<MatchType, string> = {
  contains: 'contém',
  starts_with: 'começa com',
  exact: 'exato',
  regex: 'regex',
}

export default function CategorizationRulesPage() {
  const { data: rules, isLoading } = useCategorizationRules()
  const { data: categories } = useCategories({})
  const createRule = useCreateRule()
  const deleteRule = useDeleteRule()
  const applyRule = useApplyRuleToExisting()

  const [pattern, setPattern] = useState('')
  const [matchType, setMatchType] = useState<MatchType>('contains')
  const [categoryId, setCategoryId] = useState('')

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!pattern || !categoryId) return
    await createRule.mutateAsync({
      pattern,
      match_type: matchType,
      category_id: Number(categoryId),
    })
    setPattern('')
    setCategoryId('')
  }

  return (
    <div className="max-w-3xl mx-auto py-10 px-4 space-y-6">
      <div>
        <h1 className="text-2xl font-semibold">Regras de categorização</h1>
        <p className="text-sm text-muted-foreground mt-1">
          Categorize automaticamente transações por padrões na descrição.
        </p>
      </div>

      {/* Create form */}
      <form onSubmit={handleCreate} className="rounded-lg border p-4 space-y-3 bg-card">
        <div className="grid grid-cols-1 sm:grid-cols-[1fr_140px_1fr] gap-3">
          <div className="space-y-1">
            <Label htmlFor="pattern">Padrão</Label>
            <Input
              id="pattern"
              placeholder="Ex: IFOOD"
              value={pattern}
              onChange={(e) => setPattern(e.target.value)}
            />
          </div>
          <div className="space-y-1">
            <Label htmlFor="match">Tipo</Label>
            <Select value={matchType} onValueChange={(v) => setMatchType(v as MatchType)}>
              <SelectTrigger id="match"><SelectValue /></SelectTrigger>
              <SelectContent>
                {Object.entries(MATCH_LABELS).map(([k, v]) => (
                  <SelectItem key={k} value={k}>{v}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
          <div className="space-y-1">
            <Label htmlFor="category">Categoria</Label>
            <Select value={categoryId} onValueChange={setCategoryId}>
              <SelectTrigger id="category"><SelectValue placeholder="Selecione..." /></SelectTrigger>
              <SelectContent>
                {(categories ?? []).map((c) => (
                  <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>
        </div>
        <Button type="submit" disabled={!pattern || !categoryId || createRule.isPending} size="sm">
          <Plus className="w-4 h-4 mr-1" />
          Criar regra
        </Button>
      </form>

      {/* Rules list */}
      {isLoading ? (
        <div className="text-center py-12 text-muted-foreground">
          <Loader2 className="w-5 h-5 mx-auto animate-spin" />
        </div>
      ) : (rules ?? []).length === 0 ? (
        <div className="text-center py-12 text-muted-foreground space-y-2">
          <Sparkles className="w-8 h-8 mx-auto opacity-40" />
          <p className="text-sm">Nenhuma regra criada ainda.</p>
          <p className="text-xs">Crie regras manuais ou categorize transações para gerar automáticas.</p>
        </div>
      ) : (
        <div className="rounded-lg border overflow-hidden">
          <table className="w-full text-sm">
            <thead className="bg-muted/50 text-xs">
              <tr>
                <th className="text-left px-3 py-2 font-medium text-muted-foreground">Quando descrição</th>
                <th className="text-left px-3 py-2 font-medium text-muted-foreground">Vira</th>
                <th className="text-right px-3 py-2 font-medium text-muted-foreground">Acertos</th>
                <th className="text-right px-3 py-2 font-medium text-muted-foreground w-32">Ações</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-border">
              {(rules ?? []).map((rule) => (
                <tr key={rule.id} className="hover:bg-muted/20">
                  <td className="px-3 py-2">
                    <span className="text-muted-foreground text-xs mr-2">{MATCH_LABELS[rule.match_type]}</span>
                    <code className="bg-muted px-1.5 py-0.5 rounded text-xs">{rule.pattern}</code>
                    {rule.auto_learned && (
                      <Badge variant="secondary" className="ml-2 text-xs">aprendida</Badge>
                    )}
                  </td>
                  <td className="px-3 py-2">
                    <span
                      className="inline-flex items-center gap-1.5"
                      style={{ color: rule.category?.color ?? undefined }}
                    >
                      {rule.category?.name ?? '—'}
                    </span>
                  </td>
                  <td className="px-3 py-2 text-right tabular-nums text-muted-foreground">
                    {rule.hits}
                  </td>
                  <td className="px-3 py-2">
                    <div className="flex items-center justify-end gap-1">
                      <Button
                        size="sm"
                        variant="ghost"
                        title="Aplicar a transações sem categoria"
                        disabled={applyRule.isPending}
                        onClick={async () => {
                          const result = await applyRule.mutateAsync(rule.id)
                          alert(`${result.matched_count} transação(ões) categorizada(s).`)
                        }}
                      >
                        <Wand2 className="w-4 h-4" />
                      </Button>
                      <Button
                        size="sm"
                        variant="ghost"
                        className="text-muted-foreground hover:text-destructive"
                        onClick={() => {
                          if (window.confirm(`Remover regra "${rule.pattern}"?`)) {
                            deleteRule.mutate(rule.id)
                          }
                        }}
                      >
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  )
}
