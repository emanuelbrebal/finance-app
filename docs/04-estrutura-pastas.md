# 04 — Estrutura de Pastas

## Backend Laravel

Estrutura padrão Laravel com adaptações pra acomodar Services, Importers e InsightRules como cidadãos de primeira classe.

```
backend/
├── app/
│   ├── Console/
│   │   ├── Commands/
│   │   │   └── RunInsightsCommand.php
│   │   └── Kernel.php                       # registra schedules
│   │
│   ├── Domain/                              # ★ regras de negócio puras
│   │   ├── Importers/
│   │   │   ├── Contracts/
│   │   │   │   └── ImporterInterface.php
│   │   │   ├── DTOs/
│   │   │   │   └── ParsedTransaction.php
│   │   │   ├── ImporterRegistry.php         # detecta formato e roteia
│   │   │   ├── OfxImporter.php
│   │   │   ├── NubankCsvImporter.php
│   │   │   ├── NubankCardCsvImporter.php
│   │   │   └── GenericCsvImporter.php
│   │   │
│   │   ├── InsightRules/
│   │   │   ├── Contracts/
│   │   │   │   └── InsightRule.php
│   │   │   ├── SavingsRateRecordRule.php
│   │   │   ├── CategorySpikeRule.php
│   │   │   ├── SubscriptionDetectionRule.php
│   │   │   ├── GoalProjectionRule.php
│   │   │   ├── IdleCashRule.php
│   │   │   └── WishlistResistanceRule.php
│   │   │
│   │   ├── Milestones/
│   │   │   ├── Contracts/
│   │   │   │   └── MilestoneDetector.php
│   │   │   ├── NetWorthMilestoneDetector.php
│   │   │   ├── BehaviorMilestoneDetector.php
│   │   │   ├── FinancialHealthDetector.php
│   │   │   ├── ResistanceDetector.php
│   │   │   └── JourneyLevelTransitionDetector.php
│   │   │
│   │   ├── Wishlist/
│   │   │   ├── CheckpointEvaluator.php      # avalia 5 checkpoints
│   │   │   └── Checkpoints/
│   │   │       ├── QuarantineCheckpoint.php
│   │   │       ├── EmergencyFundCheckpoint.php
│   │   │       ├── PositiveSavingsRateCheckpoint.php
│   │   │       ├── GoalImpactCheckpoint.php
│   │   │       └── StillWantedCheckpoint.php
│   │   │
│   │   └── Calculators/
│   │       ├── NetWorthCalculator.php
│   │       ├── BurnRateCalculator.php
│   │       ├── SavingsRateCalculator.php
│   │       ├── RunwayCalculator.php
│   │       └── GoalProjectionCalculator.php
│   │
│   ├── Services/                            # ★ orquestração / use cases
│   │   ├── ImportService.php
│   │   ├── TransactionService.php
│   │   ├── InsightService.php
│   │   ├── MilestoneService.php
│   │   ├── WishlistService.php
│   │   ├── GoalService.php
│   │   ├── NetWorthSnapshotService.php
│   │   └── DashboardService.php             # agrega tudo pro endpoint /dashboard
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── AuthController.php
│   │   │           ├── ProfileController.php
│   │   │           ├── AccountController.php
│   │   │           ├── CategoryController.php
│   │   │           ├── TransactionController.php
│   │   │           ├── RecurringTransactionController.php
│   │   │           ├── ImportController.php
│   │   │           ├── CategorizationRuleController.php
│   │   │           ├── GoalController.php
│   │   │           ├── WishlistController.php
│   │   │           ├── BudgetRuleController.php
│   │   │           ├── DashboardController.php
│   │   │           ├── ChartController.php
│   │   │           ├── InsightController.php
│   │   │           ├── JourneyController.php
│   │   │           ├── MilestoneController.php
│   │   │           ├── StreakController.php
│   │   │           └── NotificationController.php
│   │   ├── Requests/                        # FormRequest por endpoint
│   │   │   └── V1/...
│   │   ├── Resources/                       # API Resources
│   │   │   └── V1/...
│   │   └── Middleware/
│   │
│   ├── Jobs/
│   │   ├── ProcessImportJob.php
│   │   ├── RunInsightRulesJob.php
│   │   ├── DetectMilestonesJob.php
│   │   ├── UpdateStreaksJob.php
│   │   ├── GenerateRecurringTransactionsJob.php
│   │   ├── CaptureNetWorthSnapshotJob.php
│   │   └── SendStrategicNotificationJob.php
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Account.php
│   │   ├── Category.php
│   │   ├── Transaction.php
│   │   ├── RecurringTransaction.php
│   │   ├── Goal.php
│   │   ├── ImportBatch.php
│   │   ├── CategorizationRule.php
│   │   ├── Insight.php
│   │   ├── BudgetRule.php
│   │   ├── WishlistItem.php
│   │   ├── PriceCheck.php
│   │   ├── Milestone.php
│   │   ├── Streak.php
│   │   └── NetWorthSnapshot.php
│   │
│   ├── Notifications/
│   │   ├── MilestoneAchieved.php
│   │   ├── WeeklyReview.php
│   │   ├── MonthlyOpening.php
│   │   ├── PostSalaryPrompt.php
│   │   ├── FridayEveningAnchor.php
│   │   ├── BudgetAwarenessNudge.php
│   │   └── ImportCompleted.php
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── DomainServiceProvider.php        # ★ registra Importers e Rules
│
├── config/
│   ├── insights.php                         # array de InsightRules registradas
│   ├── importers.php                        # array de Importers registrados
│   ├── milestones.php                       # tiers e definições de marcos
│   ├── journey.php                          # níveis (Zarpando→Soberano) e thresholds
│   └── ...
│
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── ...create_accounts_table.php
│   │   ├── ...create_categories_table.php
│   │   ├── ...create_transactions_table.php
│   │   ├── ...create_recurring_transactions_table.php
│   │   ├── ...create_goals_table.php
│   │   ├── ...create_import_batches_table.php
│   │   ├── ...create_categorization_rules_table.php
│   │   ├── ...create_insights_table.php
│   │   ├── ...create_budget_rules_table.php
│   │   ├── ...create_wishlist_items_table.php
│   │   ├── ...create_price_checks_table.php
│   │   ├── ...create_milestones_table.php
│   │   ├── ...create_streaks_table.php
│   │   └── ...create_net_worth_snapshots_table.php
│   ├── factories/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── DefaultCategoriesSeeder.php
│
├── routes/
│   ├── api.php                              # tudo sob /api/v1
│   └── console.php                          # schedule definitions
│
├── tests/
│   ├── Feature/
│   │   └── Api/V1/...
│   └── Unit/
│       ├── Domain/
│       │   ├── Importers/
│       │   ├── InsightRules/
│       │   └── Calculators/
│       └── Services/
│
├── docker-compose.yml
├── Dockerfile
├── composer.json
└── .env.example
```

### Notas sobre a estrutura

- **`Domain/`** existe como pasta de primeira classe pra deixar claro o que é regra de negócio pura (testável sem framework). Não é DDD tático, é só separação prática
- **`Services/`** orquestra: recebe request, chama coisas do Domain, persiste, dispara jobs/notifications. É onde Controllers chegam pra "fazer"
- **Configs separados** (`insights.php`, `importers.php`, etc.) tornam plugar regra/importador novo trivial: cria a classe, registra no array, fim
- **`DomainServiceProvider`** registra Importers e Rules como tagged services do container Laravel — assim o job que roda insights faz `app()->tagged('insight_rules')` e itera tudo

---

## Frontend React

```
frontend/
├── public/
│   ├── service-worker.js
│   └── manifest.json                        # PWA básico
│
├── src/
│   ├── main.tsx
│   ├── App.tsx
│   ├── routes.tsx                           # config de rotas centralizada
│   │
│   ├── api/
│   │   ├── client.ts                        # axios/fetch + CSRF + interceptors
│   │   ├── endpoints/
│   │   │   ├── auth.ts
│   │   │   ├── accounts.ts
│   │   │   ├── transactions.ts
│   │   │   ├── categories.ts
│   │   │   ├── goals.ts
│   │   │   ├── wishlist.ts
│   │   │   ├── imports.ts
│   │   │   ├── insights.ts
│   │   │   ├── dashboard.ts
│   │   │   ├── charts.ts
│   │   │   ├── milestones.ts
│   │   │   ├── streaks.ts
│   │   │   └── journey.ts
│   │   └── types.ts                         # tipos TS espelhando responses
│   │
│   ├── hooks/
│   │   ├── queries/                         # React Query hooks
│   │   │   ├── useDashboard.ts
│   │   │   ├── useTransactions.ts
│   │   │   ├── useAccounts.ts
│   │   │   ├── useGoals.ts
│   │   │   ├── useWishlist.ts
│   │   │   ├── useMilestones.ts
│   │   │   └── ...
│   │   ├── mutations/
│   │   │   ├── useCreateTransaction.ts
│   │   │   ├── useImportFile.ts
│   │   │   └── ...
│   │   └── useAuth.ts
│   │
│   ├── pages/
│   │   ├── auth/
│   │   │   ├── LoginPage.tsx
│   │   │   └── RegisterPage.tsx
│   │   ├── DashboardPage.tsx
│   │   ├── transactions/
│   │   │   ├── TransactionsListPage.tsx
│   │   │   └── TransactionFormPage.tsx
│   │   ├── accounts/
│   │   ├── goals/
│   │   ├── wishlist/
│   │   │   ├── WishlistPage.tsx
│   │   │   └── WishlistItemPage.tsx
│   │   ├── imports/
│   │   │   ├── ImportUploadPage.tsx
│   │   │   └── ImportPreviewPage.tsx
│   │   ├── insights/
│   │   ├── journey/
│   │   │   └── JourneyPage.tsx              # timeline de marcos
│   │   ├── settings/
│   │   │   ├── ProfilePage.tsx
│   │   │   ├── PreferencesPage.tsx          # toggles de gamificação
│   │   │   ├── CategoriesPage.tsx
│   │   │   ├── BudgetRulesPage.tsx
│   │   │   └── CategorizationRulesPage.tsx
│   │   └── NotFoundPage.tsx
│   │
│   ├── components/
│   │   ├── ui/                              # shadcn/ui (button, card, dialog, etc)
│   │   ├── layout/
│   │   │   ├── AppShell.tsx                 # sidebar + topbar
│   │   │   ├── Sidebar.tsx
│   │   │   └── Topbar.tsx
│   │   ├── dashboard/
│   │   │   ├── NetWorthWidget.tsx
│   │   │   ├── GoalProgressWidget.tsx
│   │   │   ├── SavingsRateWidget.tsx
│   │   │   ├── BurnRateWidget.tsx
│   │   │   ├── RunwayWidget.tsx
│   │   │   ├── EmergencyFundWidget.tsx
│   │   │   ├── JourneyLevelWidget.tsx
│   │   │   ├── TopExpensesWidget.tsx
│   │   │   └── RecentTransactionsWidget.tsx
│   │   ├── charts/
│   │   │   ├── NetWorthEvolutionChart.tsx
│   │   │   ├── IncomeVsExpensesChart.tsx
│   │   │   ├── CategoryDistributionChart.tsx
│   │   │   └── DayOfWeekHeatmap.tsx
│   │   ├── transactions/
│   │   │   ├── TransactionForm.tsx
│   │   │   ├── TransactionList.tsx
│   │   │   └── TransactionFilters.tsx
│   │   ├── wishlist/
│   │   │   ├── WishlistItemCard.tsx
│   │   │   ├── CheckpointsPanel.tsx         # 5 checkpoints visuais
│   │   │   └── ReadyToBuyBadge.tsx
│   │   ├── gamification/
│   │   │   ├── MilestoneCelebration.tsx     # modal/overlay escalado por tier
│   │   │   ├── ConfettiOverlay.tsx
│   │   │   ├── JourneyLevelBadge.tsx
│   │   │   ├── StreakIndicator.tsx
│   │   │   └── MilestonesTimeline.tsx
│   │   ├── insights/
│   │   │   ├── InsightCard.tsx
│   │   │   └── InsightsFeed.tsx
│   │   ├── imports/
│   │   │   ├── FileDropzone.tsx
│   │   │   └── ImportPreviewTable.tsx
│   │   └── shared/
│   │       ├── Money.tsx                    # formatador BRL
│   │       ├── DateLabel.tsx
│   │       ├── EmptyState.tsx
│   │       └── ErrorBoundary.tsx
│   │
│   ├── lib/
│   │   ├── format.ts                        # formatadores BRL, datas
│   │   ├── validators/                      # schemas zod por feature
│   │   │   ├── transaction.ts
│   │   │   ├── goal.ts
│   │   │   └── ...
│   │   └── push-notifications.ts
│   │
│   ├── stores/                              # Zustand pra estado UI global leve
│   │   ├── authStore.ts
│   │   └── uiStore.ts                       # modais abertos, etc
│   │
│   └── styles/
│       ├── globals.css
│       └── tailwind.css
│
├── index.html
├── vite.config.ts
├── tsconfig.json
├── tailwind.config.ts
├── components.json                          # shadcn config
└── package.json
```

### Notas sobre a estrutura

- **React Query é o estado de servidor**, Zustand só pra UI ephemera (modal aberto, sidebar aberta). Sem Redux. Sem Context exagerado
- **`pages/`** são containers que orquestram hooks + components. **`components/`** são burros (recebem props, renderizam)
- **`api/endpoints/`** é fina camada de wrapper sobre fetch — funções tipadas que retornam Promise. Hooks de query consomem essas funções
- **`validators/`** com zod espelha as Form Requests do Laravel. Mesmo schema dos dois lados, validação cliente antes de enviar, erro do servidor como fallback
- **`gamification/`** é pasta isolada porque vai ter componentes muito específicos (confete, animações, transições) e ajuda achar tudo junto quando for refinar UX
