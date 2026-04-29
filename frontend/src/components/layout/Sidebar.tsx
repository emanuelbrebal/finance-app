import { NavLink } from 'react-router-dom'
import { LayoutDashboard, ArrowLeftRight, Wallet, Tag, UserCircle, Upload } from 'lucide-react'
import { cn } from '@/lib/utils'
import { useHealth } from '@/hooks/queries/useHealth'

interface NavItem {
  to: string
  icon: React.ComponentType<{ className?: string }>
  label: string
}

const NAV_ITEMS: NavItem[] = [
  { to: '/dashboard', icon: LayoutDashboard, label: 'dashboard' },
  { to: '/transactions', icon: ArrowLeftRight, label: 'movimentações' },
  { to: '/imports', icon: Upload, label: 'importar' },
  { to: '/accounts', icon: Wallet, label: 'contas' },
  { to: '/categories', icon: Tag, label: 'categorias' },
]

export function Sidebar() {
  const { data, isError } = useHealth()
  const healthy = !isError && data?.data.status === 'ok'

  return (
    <aside className="flex h-full w-56 flex-col border-r border-border bg-card">
      {/* Logo */}
      <div className="flex h-14 items-center gap-2 border-b border-border px-4">
        <LayoutDashboard className="h-5 w-5 text-primary" />
        <span className="text-sm font-semibold tracking-tight">finance-app</span>
      </div>

      {/* Nav */}
      <nav className="flex-1 space-y-0.5 p-2">
        {NAV_ITEMS.map(({ to, icon: Icon, label }) => (
          <NavLink
            key={to}
            to={to}
            className={({ isActive }) =>
              cn(
                'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
                isActive
                  ? 'bg-primary/10 text-primary font-medium'
                  : 'text-muted-foreground hover:bg-accent hover:text-foreground',
              )
            }
          >
            <Icon className="h-4 w-4 shrink-0" />
            {label}
          </NavLink>
        ))}
      </nav>

      {/* Footer: profile link + health */}
      <div className="border-t border-border p-2 space-y-1">
        <NavLink
          to="/profile"
          className={({ isActive }) =>
            cn(
              'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
              isActive
                ? 'bg-primary/10 text-primary font-medium'
                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
            )
          }
        >
          <UserCircle className="h-4 w-4 shrink-0" />
          perfil
        </NavLink>

        <div className="flex items-center gap-2 px-3 py-1.5 text-xs text-muted-foreground">
          <span
            className={cn('h-2 w-2 rounded-full', healthy ? 'bg-emerald-500' : 'bg-rose-500')}
            aria-hidden
          />
          {healthy ? 'online' : 'degradado'}
        </div>
      </div>
    </aside>
  )
}
