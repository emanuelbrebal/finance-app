import { useState } from 'react'
import { CategoryForm } from '@/components/categories/CategoryForm'
import { CategoryList } from '@/components/categories/CategoryList'
import { Button } from '@/components/ui/button'
import { useCategories, useSeedDefaultCategories } from '@/hooks/queries/useCategories'

export function CategoriesPage() {
  const [showForm, setShowForm] = useState(false)
  const { data: categories } = useCategories()
  const seedMutation = useSeedDefaultCategories()

  const isEmpty = (categories?.length ?? 0) === 0

  return (
    <section className="space-y-4">
      <div className="flex items-center justify-between gap-2">
        <div>
          <h2 className="text-lg font-semibold">categorias</h2>
          <p className="text-xs text-muted-foreground">
            entradas e saídas para classificar movimentações
          </p>
        </div>
        <div className="flex gap-2">
          {isEmpty && (
            <Button
              size="sm"
              variant="outline"
              disabled={seedMutation.isPending}
              onClick={() => seedMutation.mutate()}
            >
              {seedMutation.isPending ? 'criando...' : 'categorias padrão'}
            </Button>
          )}
          {!showForm && (
            <Button size="sm" onClick={() => setShowForm(true)}>
              nova
            </Button>
          )}
        </div>
      </div>

      {showForm && (
        <CategoryForm
          onSuccess={() => setShowForm(false)}
          onCancel={() => setShowForm(false)}
        />
      )}

      <CategoryList />
    </section>
  )
}
