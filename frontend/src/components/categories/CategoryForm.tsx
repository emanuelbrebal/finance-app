import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useCreateCategory } from '@/hooks/queries/useCategories'
import {
  CATEGORY_KINDS,
  CATEGORY_KIND_LABELS,
  CreateCategorySchema,
  type CategoryKind,
} from '@/lib/validators/category'

interface CategoryFormProps {
  onSuccess?: () => void
  onCancel?: () => void
}

export function CategoryForm({ onSuccess, onCancel }: CategoryFormProps) {
  const [name, setName] = useState('')
  const [kind, setKind] = useState<CategoryKind>('expense')
  const [color, setColor] = useState('#22c55e')
  const [icon, setIcon] = useState('circle')
  const [isEssential, setIsEssential] = useState(true)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const createMutation = useCreateCategory()

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    const parsed = CreateCategorySchema.safeParse({
      name,
      kind,
      color,
      icon,
      is_essential: isEssential,
    })

    if (!parsed.success) {
      setErrors(flattenZodErrors(parsed.error.issues))
      return
    }

    createMutation.mutate(parsed.data, {
      onSuccess: () => {
        setName('')
        setIcon('circle')
        onSuccess?.()
      },
      onError: (err) => setErrors(extractServerErrors(err)),
    })
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-6">
      <div className="space-y-1">
        <h3 className="text-base font-semibold">nova categoria</h3>
        <p className="text-xs text-muted-foreground">organize entradas e saídas</p>
      </div>

      <div className="space-y-1">
        <Label htmlFor="cat-name">nome</Label>
        <Input
          id="cat-name"
          value={name}
          onChange={(e) => setName(e.target.value)}
          placeholder="Mercado, Salário, ..."
        />
        {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
      </div>

      <div className="space-y-1">
        <Label htmlFor="cat-kind">tipo</Label>
        <select
          id="cat-kind"
          value={kind}
          onChange={(e) => setKind(e.target.value as CategoryKind)}
          className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
        >
          {CATEGORY_KINDS.map((k) => (
            <option key={k} value={k}>
              {CATEGORY_KIND_LABELS[k]}
            </option>
          ))}
        </select>
        {errors.kind && <p className="text-xs text-destructive">{errors.kind}</p>}
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-1">
          <Label htmlFor="cat-color">cor</Label>
          <Input
            id="cat-color"
            value={color}
            onChange={(e) => setColor(e.target.value)}
            placeholder="#22c55e"
          />
          {errors.color && <p className="text-xs text-destructive">{errors.color}</p>}
        </div>
        <div className="space-y-1">
          <Label htmlFor="cat-icon">ícone</Label>
          <Input
            id="cat-icon"
            value={icon}
            onChange={(e) => setIcon(e.target.value)}
            placeholder="circle"
          />
          {errors.icon && <p className="text-xs text-destructive">{errors.icon}</p>}
        </div>
      </div>

      {kind === 'expense' && (
        <label className="flex items-center gap-2 text-xs">
          <input
            type="checkbox"
            checked={isEssential}
            onChange={(e) => setIsEssential(e.target.checked)}
            className="h-4 w-4"
          />
          <span>essencial (não-supérfluo)</span>
        </label>
      )}

      {errors._root && <p className="text-xs text-destructive">{errors._root}</p>}

      <div className="flex gap-2">
        <Button type="submit" disabled={createMutation.isPending} className="flex-1">
          {createMutation.isPending ? 'salvando...' : 'criar categoria'}
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
