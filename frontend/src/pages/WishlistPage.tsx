import { useState } from 'react'
import { Link } from 'react-router-dom'
import { Plus, Trash2, Loader2, ShoppingCart, Sparkles, ExternalLink } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Money } from '@/components/Money'
import {
  useWishlist,
  useWishlistSummary,
  useCreateWishlistItem,
  useDeleteWishlistItem,
} from '@/hooks/queries/useWishlist'
import { useCategories } from '@/hooks/queries/useCategories'
import type { WishlistItem, WishlistStatus } from '@/api/endpoints/wishlist'
import { Badge } from '@/components/ui/badge'

const STATUS_LABELS: Record<WishlistStatus, string> = {
  waiting: 'aguardando',
  ready_to_buy: 'liberado',
  purchased: 'comprado',
  abandoned: 'abandonado',
}

function StatusBadge({ status }: { status: WishlistStatus }) {
  const variant: Record<WishlistStatus, 'default' | 'secondary' | 'outline'> = {
    waiting: 'secondary',
    ready_to_buy: 'default',
    purchased: 'outline',
    abandoned: 'outline',
  }
  return (
    <Badge variant={variant[status]} className="text-xs">{STATUS_LABELS[status]}</Badge>
  )
}

function ItemCard({ item }: { item: WishlistItem }) {
  const deleteItem = useDeleteWishlistItem()
  const isReady = item.status === 'ready_to_buy'

  return (
    <Link
      to={`/wishlist/${item.id}`}
      className={`block rounded-lg border p-4 hover:bg-muted/30 transition-colors ${
        isReady ? 'border-green-500/40 bg-green-500/5' : ''
      }`}
    >
      <div className="flex items-start justify-between gap-3">
        <div className="flex-1 min-w-0">
          <div className="flex items-center gap-2 flex-wrap">
            <h3 className="font-medium truncate">{item.name}</h3>
            <StatusBadge status={item.status} />
            {isReady && <Sparkles className="w-4 h-4 text-green-500 shrink-0" />}
          </div>
          <p className="text-xs text-muted-foreground mt-1">
            <Money value={item.target_price} /> · {item.days_in_wishlist}d na lista
            {item.category && (
              <> · <span style={{ color: item.category.color }}>{item.category.name}</span></>
            )}
          </p>
        </div>
        <Button
          size="sm" variant="ghost"
          className="text-muted-foreground hover:text-destructive shrink-0"
          onClick={(e) => {
            e.preventDefault()
            if (window.confirm(`Excluir "${item.name}"?`)) deleteItem.mutate(item.id)
          }}
        >
          <Trash2 className="w-4 h-4" />
        </Button>
      </div>
    </Link>
  )
}

function CreateForm({ onClose }: { onClose: () => void }) {
  const create = useCreateWishlistItem()
  const { data: categories } = useCategories({})
  const [name, setName] = useState('')
  const [targetPrice, setTargetPrice] = useState('')
  const [referenceUrl, setReferenceUrl] = useState('')
  const [priority, setPriority] = useState('3')
  const [quarantineDays, setQuarantineDays] = useState('30')
  const [categoryId, setCategoryId] = useState('')

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    if (!name || !targetPrice) return
    await create.mutateAsync({
      name,
      target_price: targetPrice,
      reference_url: referenceUrl || null,
      priority: Number(priority),
      quarantine_days: Number(quarantineDays),
      category_id: categoryId ? Number(categoryId) : null,
    })
    onClose()
  }

  return (
    <form onSubmit={handleSubmit} className="rounded-lg border p-4 space-y-3 bg-card">
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div className="space-y-1 sm:col-span-2">
          <Label>Nome do desejo</Label>
          <Input value={name} onChange={(e) => setName(e.target.value)} placeholder="Ex: Relógio Casio modelo X" />
        </div>
        <div className="space-y-1">
          <Label>Preço alvo (R$)</Label>
          <Input type="number" step="0.01" min="0" value={targetPrice} onChange={(e) => setTargetPrice(e.target.value)} />
        </div>
        <div className="space-y-1">
          <Label>Quarentena (dias)</Label>
          <Select value={quarantineDays} onValueChange={setQuarantineDays}>
            <SelectTrigger><SelectValue /></SelectTrigger>
            <SelectContent>
              <SelectItem value="15">15 dias</SelectItem>
              <SelectItem value="30">30 dias</SelectItem>
              <SelectItem value="60">60 dias</SelectItem>
              <SelectItem value="90">90 dias</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div className="space-y-1">
          <Label>Prioridade (1-5)</Label>
          <Select value={priority} onValueChange={setPriority}>
            <SelectTrigger><SelectValue /></SelectTrigger>
            <SelectContent>
              {[1, 2, 3, 4, 5].map((n) => <SelectItem key={n} value={String(n)}>{n}</SelectItem>)}
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
        <div className="space-y-1 sm:col-span-2">
          <Label>Link de referência (opcional)</Label>
          <Input type="url" value={referenceUrl} onChange={(e) => setReferenceUrl(e.target.value)} placeholder="https://..." />
        </div>
      </div>
      <div className="flex justify-end gap-2 pt-2">
        <Button type="button" variant="ghost" onClick={onClose}>Cancelar</Button>
        <Button type="submit" disabled={create.isPending}>{create.isPending ? 'Salvando...' : 'Adicionar'}</Button>
      </div>
    </form>
  )
}

export default function WishlistPage() {
  const [statusFilter, setStatusFilter] = useState<WishlistStatus | 'all'>('all')
  const { data: items, isLoading } = useWishlist(
    statusFilter === 'all' ? undefined : { status: statusFilter },
  )
  const { data: summary } = useWishlistSummary()
  const [creating, setCreating] = useState(false)

  return (
    <div className="max-w-3xl mx-auto py-10 px-4 space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-semibold">Wishlist</h1>
          <p className="text-sm text-muted-foreground mt-1">
            Anti-impulso por design. 5 checkpoints liberam compras conscientes.
          </p>
        </div>
        {!creating && (
          <Button onClick={() => setCreating(true)}>
            <Plus className="w-4 h-4 mr-1" /> Novo desejo
          </Button>
        )}
      </div>

      {/* Summary */}
      {summary && summary.count_active > 0 && (
        <div className="rounded-lg border bg-muted/20 p-3 text-sm grid grid-cols-3 gap-3">
          <div>
            <p className="text-xs text-muted-foreground">Itens ativos</p>
            <p className="font-semibold tabular-nums">{summary.count_active}</p>
          </div>
          <div>
            <p className="text-xs text-muted-foreground">Total alvo</p>
            <p className="font-semibold tabular-nums"><Money value={summary.total_target_amount} /></p>
          </div>
          <div>
            <p className="text-xs text-muted-foreground">Liberados</p>
            <p className="font-semibold tabular-nums text-green-600">{summary.ready_to_buy_count}</p>
          </div>
        </div>
      )}

      {creating && <CreateForm onClose={() => setCreating(false)} />}

      {/* Filter */}
      <div className="flex gap-1.5 flex-wrap">
        {(['all', 'waiting', 'ready_to_buy', 'purchased', 'abandoned'] as const).map((s) => (
          <Button
            key={s} size="sm"
            variant={statusFilter === s ? 'default' : 'outline'}
            onClick={() => setStatusFilter(s)}
          >
            {s === 'all' ? 'todos' : STATUS_LABELS[s as WishlistStatus]}
          </Button>
        ))}
      </div>

      {/* List */}
      {isLoading ? (
        <div className="text-center py-12 text-muted-foreground"><Loader2 className="w-5 h-5 mx-auto animate-spin" /></div>
      ) : (items ?? []).length === 0 ? (
        <div className="text-center py-12 text-muted-foreground space-y-2">
          <ShoppingCart className="w-8 h-8 mx-auto opacity-40" />
          <p className="text-sm">
            {statusFilter === 'all' ? 'Nenhum desejo cadastrado.' : `Nenhum item ${STATUS_LABELS[statusFilter as WishlistStatus]}.`}
          </p>
        </div>
      ) : (
        <div className="space-y-2">
          {(items ?? []).map((item) => <ItemCard key={item.id} item={item} />)}
        </div>
      )}
    </div>
  )
}
