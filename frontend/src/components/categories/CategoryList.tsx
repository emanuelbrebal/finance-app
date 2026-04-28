import { Button } from '@/components/ui/button'
import { useArchiveCategory, useCategories } from '@/hooks/queries/useCategories'
import { CATEGORY_KIND_LABELS } from '@/lib/validators/category'
import type { Category } from '@/api/endpoints/categories'

export function CategoryList() {
  const { data: categories, isLoading, isError } = useCategories()
  const archiveMutation = useArchiveCategory()

  if (isLoading) {
    return <p className="text-center text-xs text-muted-foreground">carregando categorias...</p>
  }

  if (isError) {
    return <p className="text-center text-xs text-destructive">erro ao carregar categorias</p>
  }

  if (!categories || categories.length === 0) {
    return (
      <div className="rounded-lg border border-dashed border-border p-6 text-center text-xs text-muted-foreground">
        nenhuma categoria. use o botão "categorias padrão" para começar rápido.
      </div>
    )
  }

  const grouped = categories.reduce<Record<string, Category[]>>((acc, c) => {
    acc[c.kind] ??= []
    acc[c.kind].push(c)
    return acc
  }, {})

  return (
    <div className="space-y-4">
      {(['income', 'expense'] as const).map((kind) =>
        grouped[kind] && grouped[kind].length > 0 ? (
          <div key={kind}>
            <h4 className="mb-2 text-xs uppercase tracking-wide text-muted-foreground">
              {CATEGORY_KIND_LABELS[kind]} ({grouped[kind].length})
            </h4>
            <ul className="space-y-1">
              {grouped[kind].map((category) => (
                <li
                  key={category.id}
                  className="flex items-center justify-between rounded-md border border-border px-3 py-2"
                >
                  <div className="flex items-center gap-2">
                    <span
                      className="inline-block h-3 w-3 rounded-full"
                      style={{ backgroundColor: category.color }}
                      aria-hidden
                    />
                    <span className="text-sm">{category.name}</span>
                    {!category.is_essential && category.kind === 'expense' && (
                      <span className="rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] uppercase tracking-wide text-amber-600 dark:text-amber-400">
                        supérfluo
                      </span>
                    )}
                  </div>
                  <Button
                    variant="ghost"
                    size="sm"
                    disabled={archiveMutation.isPending}
                    onClick={() => {
                      if (confirm(`arquivar "${category.name}"?`)) {
                        archiveMutation.mutate(category.id)
                      }
                    }}
                  >
                    arquivar
                  </Button>
                </li>
              ))}
            </ul>
          </div>
        ) : null,
      )}
    </div>
  )
}
