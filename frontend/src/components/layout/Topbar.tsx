import { useNavigate } from 'react-router-dom'
import { LogOut } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { useLogout, useMe } from '@/hooks/useAuth'

interface TopbarProps {
  title: string
}

export function Topbar({ title }: TopbarProps) {
  const { data: user } = useMe()
  const logoutMutation = useLogout()
  const navigate = useNavigate()

  function handleLogout() {
    logoutMutation.mutate(undefined, {
      onSuccess: () => navigate('/login', { replace: true }),
    })
  }

  return (
    <header className="flex h-14 shrink-0 items-center justify-between border-b border-border bg-card px-6">
      <h1 className="text-sm font-semibold">{title}</h1>

      <div className="flex items-center gap-3">
        {user && (
          <span className="hidden text-xs text-muted-foreground sm:block">
            {user.name}
          </span>
        )}
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
