import { Navigate, Outlet } from 'react-router-dom'
import { useMe } from '@/hooks/useAuth'

/**
 * Protects routes that require authentication.
 * Shows a loading state while the session is being verified,
 * then either renders children or redirects to /login.
 */
export function AuthGuard() {
  const { data: user, isLoading } = useMe()

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center bg-background">
        <p className="text-xs text-muted-foreground">verificando sessão...</p>
      </div>
    )
  }

  if (!user) {
    return <Navigate to="/login" replace />
  }

  return <Outlet />
}
