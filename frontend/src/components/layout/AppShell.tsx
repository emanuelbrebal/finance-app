import { Outlet, useLocation } from 'react-router-dom'
import { Sidebar } from './Sidebar'
import { Topbar } from './Topbar'

const PAGE_TITLES: Record<string, string> = {
  '/dashboard': 'dashboard',
  '/transactions': 'movimentações',
  '/accounts': 'contas',
  '/categories': 'categorias',
  '/profile': 'perfil',
}

export function AppShell() {
  const location = useLocation()
  const title = PAGE_TITLES[location.pathname] ?? 'finance-app'

  return (
    <div className="flex h-screen bg-background text-foreground">
      <Sidebar />

      <div className="flex min-w-0 flex-1 flex-col">
        <Topbar title={title} />

        <main className="flex-1 overflow-y-auto p-6">
          <div className="mx-auto max-w-3xl">
            <Outlet />
          </div>
        </main>
      </div>
    </div>
  )
}
