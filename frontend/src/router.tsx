import { createBrowserRouter, Navigate } from 'react-router-dom'
import { AuthGuard } from '@/components/layout/AuthGuard'
import { AppShell } from '@/components/layout/AppShell'
import { LoginPage } from '@/pages/LoginPage'
import { TransactionsPage } from '@/pages/TransactionsPage'
import { AccountsPage } from '@/pages/AccountsPage'
import { CategoriesPage } from '@/pages/CategoriesPage'

export const router = createBrowserRouter([
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    element: <AuthGuard />,
    children: [
      {
        element: <AppShell />,
        children: [
          { index: true, element: <Navigate to="/transactions" replace /> },
          { path: 'transactions', element: <TransactionsPage /> },
          { path: 'accounts', element: <AccountsPage /> },
          { path: 'categories', element: <CategoriesPage /> },
        ],
      },
    ],
  },
])
