import { useNavigate, useParams, Link } from 'react-router-dom'
import { ArrowLeft, ExternalLink, X, Clock, ShoppingCart, Loader2, Sparkles } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Money } from '@/components/Money'
import { CheckpointsPanel } from '@/components/wishlist/CheckpointsPanel'
import {
  useWishlistItem,
  useExtendQuarantine,
  useAbandonWishlistItem,
  useDeleteWishlistItem,
} from '@/hooks/queries/useWishlist'

export default function WishlistItemPage() {
  const { id } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const { data: item, isLoading } = useWishlistItem(Number(id))
  const extend = useExtendQuarantine()
  const abandon = useAbandonWishlistItem()
  const remove = useDeleteWishlistItem()

  if (isLoading || !item) {
    return (
      <div className="flex items-center justify-center h-64 text-muted-foreground gap-2">
        <Loader2 className="animate-spin w-5 h-5" /> Carregando...
      </div>
    )
  }

  const isReady = item.status === 'ready_to_buy'
  const isActive = item.status === 'waiting' || item.status === 'ready_to_buy'

  return (
    <div className="max-w-2xl mx-auto py-10 px-4 space-y-6">
      <Link to="/wishlist" className="inline-flex items-center text-sm text-muted-foreground hover:text-foreground">
        <ArrowLeft className="w-4 h-4 mr-1" /> Voltar à wishlist
      </Link>

      {/* Header */}
      <div className="space-y-2">
        <div className="flex items-center gap-2">
          <h1 className="text-2xl font-semibold">{item.name}</h1>
          {isReady && <Sparkles className="w-5 h-5 text-green-500" />}
        </div>
        <div className="flex items-center gap-2 flex-wrap text-sm text-muted-foreground">
          <Money value={item.target_price} />
          <span>·</span>
          <span>prioridade {item.priority}</span>
          <span>·</span>
          <span>{item.days_in_wishlist}d na lista</span>
          {item.category && (
            <>
              <span>·</span>
              <Badge variant="outline" className="text-xs" style={{ borderColor: item.category.color, color: item.category.color }}>
                {item.category.name}
              </Badge>
            </>
          )}
          {item.reference_url && (
            <a href={item.reference_url} target="_blank" rel="noopener noreferrer" className="text-primary hover:underline inline-flex items-center gap-1">
              <ExternalLink className="w-3.5 h-3.5" /> referência
            </a>
          )}
        </div>
      </div>

      {/* Ready to buy banner */}
      {isReady && (
        <div className="rounded-lg border border-green-500/30 bg-green-500/10 p-4 space-y-1">
          <p className="font-medium text-green-700 dark:text-green-400 flex items-center gap-2">
            <Sparkles className="w-4 h-4" /> Liberado para compra
          </p>
          <p className="text-sm text-muted-foreground">
            Você esperou {item.days_in_wishlist} dias, sua reserva está sólida e essa compra mantém você no rumo. Vai com tranquilidade.
          </p>
        </div>
      )}

      {/* Checkpoints */}
      {item.checkpoints && (
        <div className="space-y-3">
          <h2 className="text-sm font-semibold text-muted-foreground uppercase tracking-wide">5 checkpoints</h2>
          <CheckpointsPanel checkpoints={item.checkpoints} />
        </div>
      )}

      {/* Actions */}
      {isActive && (
        <div className="flex flex-wrap gap-2 pt-2 border-t">
          <Button
            variant="outline" size="sm"
            disabled={extend.isPending}
            onClick={() => {
              if (window.confirm('Estender quarentena por mais 30 dias?')) extend.mutate(item.id)
            }}
          >
            <Clock className="w-4 h-4 mr-1" /> +30 dias
          </Button>
          <Button
            variant="outline" size="sm"
            className="text-rose-600 hover:bg-rose-50"
            disabled={abandon.isPending}
            onClick={() => {
              if (window.confirm(`Abandonar "${item.name}"?`)) abandon.mutate(item.id)
            }}
          >
            <X className="w-4 h-4 mr-1" /> Abandonar
          </Button>
        </div>
      )}

      {/* Status info */}
      {item.status === 'purchased' && (
        <div className="rounded-lg border bg-muted/30 p-3 text-sm flex items-center gap-2">
          <ShoppingCart className="w-4 h-4 text-muted-foreground" />
          Comprado e vinculado a uma transação.
        </div>
      )}
      {item.status === 'abandoned' && item.abandoned_at && (
        <div className="rounded-lg border bg-muted/30 p-3 text-sm">
          Abandonado em {new Date(item.abandoned_at).toLocaleDateString('pt-BR')}.
        </div>
      )}

      <div className="pt-6 border-t">
        <Button
          variant="ghost" size="sm"
          className="text-muted-foreground hover:text-destructive"
          onClick={() => {
            if (window.confirm(`Excluir permanentemente "${item.name}"?`)) {
              remove.mutate(item.id, { onSuccess: () => navigate('/wishlist') })
            }
          }}
        >
          Excluir item
        </Button>
      </div>
    </div>
  )
}
