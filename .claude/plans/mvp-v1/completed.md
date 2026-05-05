# MVP — Completed (Fatias 3a–3o)

Status: ✅ Done. All core scaffolding, auth flow, and CRUD modules are live.

---

## Backend

### Infrastructure
- Laravel 11 + PHP 8.3 in Docker (laravel, postgres, redis, vite services)
- PostgreSQL 16 + Redis configured
- Sanctum cookie-based auth (same-origin SPA)
- Makefile with `make up`, `make logs`, `make artisan`, etc.

### Database — migrations in place
| Migration | Tables |
|---|---|
| extend_users_table | target_net_worth, target_date, estimated_monthly_income, timezone, journey_level, preferences |
| create_accounts_table | accounts |
| create_categories_table | categories |
| create_transactions_table | transactions (amount, direction, dedup_hash, tags, out_of_scope) |

### App layer
- Models: User, Account, Category, Transaction — with relationships and policies
- Service: AuthService, DefaultCategoriesService (seeds 11 default BR categories)
- Domain/Calculators: NetWorthCalculator, BurnRateCalculator, SavingsRateCalculator
- Controllers (Api/V1): Auth, Profile, Account, Category, Transaction, Dashboard
- Form Requests (V1): Login, Register, StoreAccount, UpdateAccount, StoreCategory, UpdateCategory, StoreTransaction, UpdateTransaction, UpdateProfile
- API Resources (V1): UserResource, AccountResource, CategoryResource, TransactionResource

### Routes
```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/logout
GET    /api/auth/me
GET    /api/profile          PATCH
GET    /api/dashboard
GET    /api/health
apiResource /api/accounts
POST   /api/categories/seed
apiResource /api/categories
GET    /api/transactions/summary
POST   /api/transactions/bulk-categorize
apiResource /api/transactions
```

### Tests
- Feature: AccountControllerTest, CategoryControllerTest, TransactionControllerTest, DashboardTest, ProfileTest

---

## Frontend

### Infrastructure
- Vite + React 18 + TypeScript + TailwindCSS + shadcn/ui
- React Query (TanStack Query) for server state
- React Router v6 with AuthGuard
- Axios client with CSRF + Sanctum interceptors

### API layer (`src/api/endpoints/`)
- auth.ts, accounts.ts, categories.ts, transactions.ts, dashboard.ts, profile.ts, health.ts

### Hooks
- Queries: useAccounts, useCategories, useTransactions, useDashboard, useHealth
- Mutations: useUpdateProfile
- useAuth (login, register, logout, me)

### Validators (`src/lib/validators/`)
- auth.ts, account.ts, category.ts, transaction.ts, profile.ts (Zod, mirrors Form Requests)

### Components
- `Money.tsx` — single BRL formatter component
- Layout: AppShell, Sidebar, Topbar, AuthGuard
- accounts/: AccountList, AccountForm
- categories/: CategoryList, CategoryForm
- transactions/: TransactionList, TransactionForm, TransactionFilters
- charts/: MonthlyChart (Recharts, income vs expenses bar chart)
- ThemeContext (light/dark toggle)

### Pages
| Page | Features |
|---|---|
| LoginPage | Email/password form, error feedback |
| RegisterPage | Register + auto-login |
| DashboardPage | NetWorth card, Month card (income/expenses/savings rate), Burn+Runway card, Top 5 expenses, MonthlyChart, Recent transactions |
| AccountsPage | List + create/edit/delete accounts |
| CategoriesPage | List + create/edit/delete categories, seed button |
| TransactionsPage | Paginated list, filters (period, category, account, direction), create/edit/delete |
| ProfilePage | Edit name/email, financial meta (target, income) |

---

## What MVP deliberately left out
These are intentional gaps, not bugs. Each one is the start of a v1 module:

- No OFX/CSV import
- No categorization rules (learning)
- No recurring transactions
- No goals / emergency fund
- No wishlist
- No insights engine
- No gamification (milestones, streaks, journey level)
- No strategic notifications
- No net_worth_snapshots table (charts use live transactions)
- No budget rules
- No missing v1 migrations (recurring_transactions, goals, import_batches, categorization_rules, wishlist_items, price_checks, milestones, streaks, net_worth_snapshots, insights, budget_rules)
