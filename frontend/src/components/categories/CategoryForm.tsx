import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useCreateCategory, useUpdateCategory } from '@/hooks/queries/useCategories'
import {
  CATEGORY_KINDS,
  CATEGORY_KIND_LABELS,
  CreateCategorySchema,
  UpdateCategorySchema,
  type CategoryKind,
} from '@/lib/validators/category'
import type { Category } from '@/api/endpoints/categories'

interface CategoryFormProps {
  /** When provided, the form runs in edit mode for this category */
  category?: Category
  onSuccess?: () => void
  onCancel?: () => void
}

export function CategoryForm({ category, onSuccess, onCancel }: CategoryFormProps) {
  const isEdit = category !== undefined

  const [name, setName] = useState(category?.name ?? '')
  const [kind, setKind] = useState<CategoryKind>(category?.kind ?? 'expense')
  const [color, setColor] = useState(category?.color ?? '#22c55e')
  const [icon, setIcon] = useState(category?.icon ?? 'circle')
  const [isEssential, setIsEssential] = useState(category?.is_essential ?? true)
  const [errors, setErrors] = useState<Record<string, string>>({})

  const createMutation = useCreateCategory()
  const updateMutation = useUpdateCategory(category?.id ?? 0)

  const isPending = isEdit ? updateMutation.isPending : createMutation.isPending

  function submit(e: React.FormEvent) {
    e.preventDefault()
    setErrors({})

    const raw = { name, kind, color, icon, is_essential: isEssential }

    if (isEdit) {
      const parsed = UpdateCategorySchema.safeParse(raw)
      if (!parsed.success) {
        setErrors(flattenZodErrors(parsed.error.issues))
        return
      }
      updateMutation.mutate(parsed.data, {
        onSuccess: () => onSuccess?.(),
        onError: (err) => setErrors(extractServerErrors(err)),
      })
    } else {
      const parsed = CreateCategorySchema.safeParse(raw)
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
  }

  return (
    <form onSubmit={submit} className="space-y-4 rounded-lg border border-border p-5">
      <h3 className="text-sm font-semibold">
        {isEdit ? 'editar categoria' : 'nova categoria'}
      </h3>

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
            <option key={k} value={k}>{CATEGORY_KIND_LABELS[k]}</option>
          ))}
        </select>
        {errors.kind && <p className="text-xs text-destructive">{errors.kind}</p>}
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="space-y-1">
          <Label htmlFor="cat-color">cor</Label>
          <div className="flex items-center gap-2">
            <Input
              id="cat-color"
              value={color}
              onChange={(e) => setColor(e.target.value)}
              placeholder="#22c55e"
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
        <label className="flex items-center gap-2 text-xs cursor-pointer">
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
        <Button type="submit" disabled={isPending} className="flex-1">
          {isPending ? 'salvando...' : isEdit ? 'salvar alterações' : 'criar categoria'}
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
