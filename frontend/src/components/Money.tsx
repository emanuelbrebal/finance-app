import { cn } from '@/lib/utils'

interface MoneyProps {
  value: string | number | null | undefined
  currency?: string
  className?: string
  signed?: boolean
}

const BRL_FORMATTER = new Intl.NumberFormat('pt-BR', {
  style: 'currency',
  currency: 'BRL',
})

export function Money({ value, currency = 'BRL', className, signed = false }: MoneyProps) {
  if (value === null || value === undefined || value === '') {
    return <span className={cn('tabular-nums text-muted-foreground', className)}>—</span>
  }

  const numeric = typeof value === 'string' ? Number(value) : value
  if (Number.isNaN(numeric)) {
    return <span className={cn('tabular-nums text-muted-foreground', className)}>—</span>
  }

  const formatted =
    currency === 'BRL'
      ? BRL_FORMATTER.format(numeric)
      : new Intl.NumberFormat('pt-BR', { style: 'currency', currency }).format(numeric)

  const prefix = signed && numeric > 0 ? '+' : ''

  return <span className={cn('tabular-nums', className)}>{`${prefix}${formatted}`}</span>
}
