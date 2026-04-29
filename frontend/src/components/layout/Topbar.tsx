import { useNavigate } from 'react-router-dom'
import { LogOut, Moon, Sun } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { useLogout, useMe } from '@/hooks/useAuth'
import { useThemeContext } from '@/contexts/ThemeContext'

interface TopbarProps {
  title: string
}

export function Topbar({ title }: TopbarProps) {
  const { data: user } = useMe()
  const logoutMutation = useLogout()
  const navigate = useNavigate()
  const { theme, toggle } = useThemeContext()

  function handleLogout() {
    logoutMutation.mutate(undefined, {
      onSuccess: () => navigate('/login', { replace: true }),
    })
  }

  return (
    <header className="flex h-14 shrink-0 items-center justify-between border-b border-border bg-card px-6">
      <h1 className="text-sm font-semibold">{title}</h1>

      <div className="flex items-center gap-1">
        {user && (
          <span className="hidden text-xs text-muted-foreground sm:block mr-2">
            {user.name}
          </span>
        )}

        <Button
          variant="ghost"
          size="sm"
          onClick={toggle}
          aria-label={theme === 'dark' ? 'mudar para modo claro' : 'mudar para modo escuro'}
        >
          {theme === 'dark' ? (
            <Sun className="h-4 w-4" />
          ) : (
            <Moon className="h-4 w-4" />
          )}
        </Button>

        <Button
          variant="ghost"
          size="sm"
          onClick={handleLogout}
          disabled={logoutMutation.isPending}
          className="gap-1.5"
        >
          <LogOut className="h-4 w-4" />
          <span className="hidden sm:inline">sair</span>
        </Button>
      </div>
    </header>
  )
}
