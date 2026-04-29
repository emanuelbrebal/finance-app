# 04 — Estrutura de Pastas

> **Estado:** reflete o código real do MVP (Fatias 1–3n). Itens marcados com `# → v1` ou `# → v2` ainda não existem.

---

## Backend Laravel

```
backend/
├── app/
│   ├── Domain/                              # regras de negócio puras, sem framework
│   │   ├── Calculators/                     # ✅ MVP
│   │   │   ├── BurnRateCalculator.php
│   │   │   ├── NetWorthCalculator.php
│   │   │   └── SavingsRateCalculator.php
│   │   ├── Importers/                       # → v1 (OFX, CSV Nubank)
│   │   ├── InsightRules/                    # → v1 (SavingsRateRecordRule, etc.)
│   │   ├── Milestones/                      # → v1 (NetWorthMilestoneDetector, etc.)
│   │   └── Wishlist/                        # → v1 (CheckpointEvaluator)
│   │
│   ├── Http/
│   │   ├── Controllers/Api/V1/              # ✅ MVP
│   │   │   ├── AuthController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── AccountController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── TransactionController.php
│   │   │   └── DashboardController.php
│   │   │   # → v1: ImportController, InsightController, GoalController
│   │   │   # → v1: WishlistController, MilestoneController, JourneyController
│   │   │
│   │   ├── Requests/V1/                     # ✅ MVP
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── RegisterRequest.php
│   │   │   ├── StoreAccountRequest.php
│   │   │   ├── UpdateAccountRequest.php
│   │   │   ├── StoreCategoryRequest.php
│   │   │   ├── UpdateCategoryRequest.php
│   │   │   ├── StoreTransactionRequest.php
│   │   │   ├── UpdateTransactionRequest.php
│   │   │   └── UpdateProfileRequest.php
│   │   │
│   │   └── Resources/V1/                   # ✅ MVP
│   │       ├── UserResource.php
│   │       ├── AccountResource.php
│   │       ├── CategoryResource.php
│   │       └── TransactionResource.php
│   │
│   ├── Models/                              # ✅ MVP
│   │   ├── User.php
│   │   ├── Account.php
│   │   ├── Category.php
│   │   └── Transaction.php
│   │   # → v1: Goal, WishlistItem, ImportBatch, Insight, Milestone, Streak
│   │   # → v1: RecurringTransaction, CategorizationRule, NetWorthSnapshot
│   │
│   ├── Policies/                            # ✅ MVP
│   │   ├── AccountPolicy.php
│   │   ├── CategoryPolicy.php
│   │   └── TransactionPolicy.php
│   │
│   ├── Services/                            # ✅ MVP (parcial)
│   │   ├── AuthService.php
│   │   └── DefaultCategoriesService.php
│   │   # → v1: ImportService, InsightService, MilestoneService, WishlistService
│   │
│   └── Providers/
│       └── AppServiceProvider.php
│       # → v1: DomainServiceProvider (registra Importers e Rules como tagged services)
│
├── database/
│   ├── migrations/                          # ✅ MVP
│   │   ├── ..._create_users_table.php
│   │   ├── ..._extend_users_table.php
│   │   ├── ..._create_accounts_table.php
│   │   ├── ..._create_categories_table.php
│   │   └── ..._create_transactions_table.php
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── AccountFactory.php
│   │   ├── CategoryFactory.php
│   │   └── TransactionFactory.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── TestUserSeeder.php
│
├── config/
│   # → v1: insights.php, importers.php, milestones.php, journey.php
│
├── routes/
│   └── api.php                              # /auth, /profile, /accounts,
│                                            # /categories, /transactions, /dashboard
│
└── tests/
    └── Feature/
        ├── Api/V1/AuthTest.php
        ├── AccountControllerTest.php
        ├── CategoryControllerTest.php
        ├── TransactionControllerTest.php
        ├── DashboardTest.php
        └── ProfileTest.php
```

---

## Frontend React

```
frontend/src/
├── api/
│   ├── client.ts                            # axios + CSRF interceptor
│   └── endpoints/                           # ✅ MVP
│       ├── auth.ts
│       ├── accounts.ts
│       ├── categories.ts
│       ├── transactions.ts
│       ├── dashboard.ts
│       ├── profile.ts
│       └── health.ts
│
├── hooks/
│   ├── useAuth.ts                           # login/register/logout/me
│   ├── queries/                             # ✅ MVP
│   │   ├── useAccounts.ts
│   │   ├── useCategories.ts
│   │   ├── useTransactions.ts               # inclui useMonthlyStats
│   │   ├── useDashboard.ts
│   │   └── useHealth.ts
│   └── mutations/
│       └── useUpdateProfile.ts
│
├── pages/                                   # ✅ MVP
│   ├── LoginPage.tsx
│   ├── RegisterPage.tsx
│   ├── DashboardPage.tsx
│   ├── TransactionsPage.tsx                 # inclui TxFilters state
│   ├── AccountsPage.tsx
│   ├── CategoriesPage.tsx
│   └── ProfilePage.tsx
│   # → v1: GoalsPage, WishlistPage, ImportPage, InsightsPage, JourneyPage
│
├── components/
│   ├── ui/                                  # shadcn/ui: button, input, label
│   ├── layout/                              # ✅ MVP
│   │   ├── AppShell.tsx
│   │   ├── AuthGuard.tsx
│   │   ├── Sidebar.tsx
│   │   └── Topbar.tsx
│   ├── transactions/                        # ✅ MVP
│   │   ├── TransactionForm.tsx              # create + inline edit
│   │   ├── TransactionList.tsx              # paginação + hover actions
│   │   └── TransactionFilters.tsx           # período, conta, categoria, busca
│   ├── accounts/                            # ✅ MVP
│   │   ├── AccountForm.tsx
│   │   └── AccountList.tsx
│   ├── categories/                          # ✅ MVP
│   │   ├── CategoryForm.tsx
│   │   └── CategoryList.tsx
│   ├── charts/                              # ✅ MVP
│   │   └── MonthlyChart.tsx                 # entradas vs saídas + taxa (Recharts)
│   │   # → v1: NetWorthEvolutionChart, CategoryDistributionChart
│   ├── Money.tsx                            # formatador BRL único
│   # → v1: gamification/ (MilestoneCelebration, ConfettiOverlay, StreakIndicator)
│   # → v1: insights/ (InsightCard, InsightsFeed)
│   # → v1: wishlist/ (WishlistItemCard, CheckpointsPanel)
│
├── lib/
│   ├── utils.ts                             # cn()
│   └── validators/                          # ✅ MVP — espelham Form Requests
│       ├── auth.ts
│       ├── account.ts
│       ├── category.ts
│       ├── transaction.ts
│       └── profile.ts
│
├── contexts/
│   └── ThemeContext.tsx                     # light/dark toggle persistido
│
└── router.tsx                               # /login, /register, /dashboard,
                                             # /transactions, /accounts,
                                             # /categories, /profile
```

---

## Notas sobre a estrutura

- **`Domain/Calculators`** já existe e é injetado direto no `DashboardController` via container do Laravel — sem necessidade de `DomainServiceProvider` ainda
- **`Services/`** no MVP tem apenas `AuthService` e `DefaultCategoriesService`; crescerá quando Importers e InsightRules entrarem em v1
- **`pages/`** são containers que orquestram hooks + components; **`components/`** são burros (recebem props, renderizam)
- **`Money.tsx`** é o único ponto de formatação BRL — nunca formatar inline
- **`validators/`** com Zod espelham as Form Requests do Laravel; validação client-first, erro do servidor como fallback
