# v1.5 — Development Plan

Starting point: MVP complete (auth, accounts, categories, transactions, dashboard).  
This plan covers everything not yet built — all of v1 plus v1.5 refinements.

Ordered by dependency. Each module lists backend tasks first, then frontend.

---

## MODULE 0 — Missing migrations (do first, unblocks everything)

These tables exist in the schema doc but have no migrations yet.

- [ ] `create_recurring_transactions_table`
- [ ] `create_goals_table` (with partial unique index on `is_emergency_fund`)
- [ ] `create_import_batches_table`
- [ ] `create_categorization_rules_table`
- [ ] `create_wishlist_items_table`
- [ ] `create_price_checks_table`
- [ ] `create_milestones_table`
- [ ] `create_streaks_table`
- [ ] `create_net_worth_snapshots_table`
- [ ] `create_insights_table`
- [ ] `create_budget_rules_table`

Also add missing config files:
- [ ] `config/insights.php` — registered InsightRule classes
- [ ] `config/importers.php` — registered Importer classes
- [ ] `config/milestones.php` — registered MilestoneDetector classes
- [ ] `config/journey.php` — journey level definitions

---

## MODULE 1 — Import (OFX + Nubank CSV)

### Backend
- [ ] `App\Domain\Importers\ImporterContract` interface (`parse(file): ImportPreview`)
- [ ] `App\Domain\Importers\OfxImporter` — uses `asgrim/ofxparser`
- [ ] `App\Domain\Importers\NubankCsvImporter` (conta corrente + cartão — distinct column layouts)
- [ ] `App\Domain\Importers\GenericCsvImporter` with column-mapping config
- [ ] Deduplication via `dedup_hash = sha256(occurred_on + amount + direction + description + account_id)`
- [ ] `ImportBatch` model + `ImportBatchResource`
- [ ] `App\Services\ImportService` — orchestrates parse → preview → confirm → revert
- [ ] `App\Jobs\ProcessImportBatchJob` — async confirm step
- [ ] Endpoints:
  - `POST /api/imports` — upload file, return preview (parsed rows, duplicates flagged)
  - `POST /api/imports/{batch}/confirm` — persist non-duplicate rows, run categorization rules
  - `DELETE /api/imports/{batch}` — revert (soft-delete imported transactions)
  - `GET /api/imports` — history list
- [ ] Form Requests: `StoreImportRequest`, validation for file type/size
- [ ] `ImportBatchResource` — status, counts, preview_payload (truncated)

### Frontend
- [ ] Route `/importar`
- [ ] `ImportPage` — orchestrates the 3-step flow (upload → review → done)
- [ ] `FileDropzone` component — drag & drop, shows file name + size, accepts .ofx/.csv
- [ ] `ImportPreviewTable` — editable table showing parsed rows; user can fix category/account before confirming
- [ ] `ImportHistory` — list of past batches with status badge and row counts
- [ ] `src/api/endpoints/imports.ts`
- [ ] `src/hooks/queries/useImports.ts`, `src/hooks/mutations/useConfirmImport.ts`, `useRevertImport.ts`
- [ ] `src/lib/validators/import.ts`
- [ ] Sidebar link

---

## MODULE 2 — Categorization rules (the learning layer)

### Backend
- [ ] `CategorizationRule` model + policy
- [ ] `App\Domain\Categorizers\RuleEngine` — applies rules ordered by priority+hits, returns category_id
- [ ] `App\Services\CategorizationService` — wraps RuleEngine, called during import confirm + manual update
- [ ] `POST /api/categorization-rules/apply-to-existing` — retroactive batch apply
- [ ] `apiResource /api/categorization-rules`
- [ ] Hook in `TransactionController@update` — when user sets category, offer to create rule
- [ ] Auto-learn: after manual categorize, create rule with `auto_learned=true` if similarity > threshold
- [ ] `Form Requests`: StoreCategorizationRuleRequest, UpdateCategorizationRuleRequest
- [ ] `CategorizationRuleResource`

### Frontend
- [ ] `CategorizationRulesPage` at `/categorias/regras` (or linked from CategoriesPage)
- [ ] After manual categorization in TransactionList: toast "Aplicar a todas com 'IFOOD'?" → mutation
- [ ] `src/api/endpoints/categorizationRules.ts`
- [ ] `src/hooks/queries/useCategorizationRules.ts`

---

## MODULE 3 — Recurring transactions

### Backend
- [ ] `RecurringTransaction` model + policy
- [ ] `App\Jobs\GenerateRecurringTransactionsJob` — runs monthly (1st of month), idempotent via `last_generated_on`
- [ ] Register in `routes/console.php` scheduler
- [ ] `apiResource /api/recurring-transactions`
- [ ] `RecurringTransactionResource`
- [ ] Form Requests: Store/Update

### Frontend
- [ ] `RecurringPage` at `/recorrentes`
- [ ] List + create/edit/delete recurring templates
- [ ] Show next generation date
- [ ] Sidebar link
- [ ] `src/api/endpoints/recurring.ts`

---

## MODULE 4 — Goals + Emergency fund

### Backend
- [ ] `Goal` model + policy
- [ ] `App\Services\GoalService` — `autoTargetEmergencyFund(user)` = 6× burn_rate_3m
- [ ] Endpoints:
  - `apiResource /api/goals`
  - `GET /api/goals/emergency-fund` — shortcut for the single emergency fund goal
  - `POST /api/goals/emergency-fund/auto-target` — sets target = 6× burn_rate
- [ ] `GoalResource`
- [ ] Form Requests: StoreGoalRequest, UpdateGoalRequest

### Frontend
- [ ] `GoalsPage` at `/objetivos`
- [ ] `EmergencyFundWidget` in DashboardPage — shows progress bar, months covered, auto-target button
- [ ] `GoalCard` component — target, current, % reached, months to go
- [ ] `src/api/endpoints/goals.ts`

---

## MODULE 5 — Wishlist (5 checkpoints)

### Backend
- [ ] `WishlistItem` model + `PriceCheck` model + policies
- [ ] `App\Domain\Wishlist\CheckpointEvaluator` — evaluates all 5 checkpoints for an item:
  1. `QuarantineCheckpoint` — enough days since added?
  2. `EmergencyFundCheckpoint` — emergency reserve intact?
  3. `SavingsRateCheckpoint` — savings rate positive this month?
  4. `GoalImpactCheckpoint` — purchase won't derail the goal?
  5. `StillWantsCheckpoint` — user confirmed they still want it?
- [ ] `App\Services\WishlistService` — `evaluateStatus(item)` → updates `status`
- [ ] `POST /api/wishlist/{id}/check-prices` → 501 (Not Implemented) with friendly message
- [ ] `POST /api/wishlist/{id}/confirm-still-wants` — updates `last_review_prompt_at` and evaluates
- [ ] `apiResource /api/wishlist`
- [ ] `WishlistItemResource` — includes checkpoint evaluation results
- [ ] Form Requests: Store/Update

### Frontend
- [ ] `WishlistPage` at `/wishlist`
- [ ] `WishlistCard` — item name, price, priority, status badge
- [ ] `CheckpointsPanel` — visual checklist of 5 checkpoints, each with pass/fail/pending state
- [ ] "Ainda quero!" confirmation button (triggers `confirm-still-wants`)
- [ ] Add/edit form with photo upload placeholder
- [ ] `src/api/endpoints/wishlist.ts`

---

## MODULE 6 — Insights engine

### Backend
- [ ] `Insight` model
- [ ] `App\Domain\InsightRules\InsightRuleContract` interface (`evaluate(user): ?InsightDTO[]`)
- [ ] 6 initial rules:
  - `SavingsRateRecordRule` — new personal best savings rate
  - `CategorySpikeRule` — category spend +50% vs 3m avg
  - `SubscriptionDetectionRule` — same amount + same description monthly
  - `GoalProjectionRule` — at current pace, reach goal by date?
  - `IdleCashRule` — large positive balance sitting in checking for 30+ days
  - `WishlistResistanceRule` — item in quarantine, user hasn't touched it in 7+ days
- [ ] `App\Jobs\RunInsightsJob` — daily, 6h AM; iterates `app()->tagged('insight_rules')`; dedup via `dedup_key`
- [ ] Register rules in `config/insights.php` + bind in AppServiceProvider
- [ ] `GET /api/insights` — unread first, paginated
- [ ] `PATCH /api/insights/{id}/read`
- [ ] `PATCH /api/insights/{id}/dismiss`
- [ ] `InsightResource`

### Frontend
- [ ] `InsightsFeed` component — card list with severity color (positive=green, info=blue, warning=amber)
- [ ] Embed InsightsFeed in DashboardPage (below recent transactions)
- [ ] Unread badge in Sidebar
- [ ] `src/api/endpoints/insights.ts`
- [ ] `src/hooks/queries/useInsights.ts`, `useMarkInsightRead.ts`

---

## MODULE 7 — Gamification (milestones + streaks + journey level)

### Backend
- [ ] `Milestone` model, `Streak` model
- [ ] `App\Domain\Milestones\MilestoneDetectorContract` interface
- [ ] 5 detectors:
  - `NetWorthMilestoneDetector` — 1k, 5k, 10k, 25k, 50k, 100k
  - `BehaviorMilestoneDetector` — first import, first recurring, first rule created
  - `FinancialHealthMilestoneDetector` — emergency fund reached, savings rate ≥ 20% for 3 months
  - `ResistanceMilestoneDetector` — wishlist item quarantine completed without buying
  - `JourneyTransitionDetector` — level up in journey
- [ ] `App\Jobs\DetectMilestonesJob` — daily, after insights job
- [ ] `App\Jobs\UpdateStreaksJob` — weekly (Monday) + monthly (1st); updates `weekly_logging` and `positive_months` streaks
- [ ] `App\Domain\Journey\JourneyCalculator` — maps metrics to level (using `config/journey.php`)
- [ ] `App\Http\Controllers\Api\V1\JourneyController` — `GET /api/journey` (current level, progress, next threshold)
- [ ] `GET /api/milestones` — achieved list, ordered by `achieved_at DESC`
- [ ] `PATCH /api/milestones/{id}/celebrate` — marks `celebrated_at`
- [ ] `GET /api/streaks` — current streaks by kind
- [ ] Resources: MilestoneResource, StreakResource, JourneyResource

### Frontend
- [ ] `MilestoneCelebration` modal — 4 tiers:
  - `small`: subtle toast (1s)
  - `medium`: modal with icon + message
  - `large`: modal with animation + share button
  - `epic`: full-screen confetti + sound optional
- [ ] Poll for uncelebrated milestones on app load (or websocket if available)
- [ ] `MilestonesTimeline` page at `/jornada` — chronological list of achievements
- [ ] `JourneyLevelBadge` in Topbar — shows current level name + mini progress bar
- [ ] `StreakIndicator` in Sidebar — shows weekly logging streak count
- [ ] `src/components/gamification/` — isolated folder for all gamification components
- [ ] `src/api/endpoints/milestones.ts`, `streaks.ts`, `journey.ts`

---

## MODULE 8 — Net worth snapshots

### Backend
- [ ] `NetWorthSnapshot` model
- [ ] `App\Jobs\CaptureNetWorthSnapshotJob` — runs on last day of month (scheduler); uses `NetWorthCalculator`; also recalculates `journey_level` in same job
- [ ] `GET /api/charts/net-worth-evolution` — reads from `net_worth_snapshots`, last 24 months
- [ ] `NetWorthSnapshotResource`

### Frontend
- [ ] `NetWorthEvolutionChart` (Recharts `LineChart`) — line with actual data, dotted projection to target
- [ ] Add to DashboardPage replacing or complementing MonthlyChart
- [ ] `src/api/endpoints/charts.ts`
- [ ] `src/hooks/queries/useNetWorthEvolution.ts`

---

## MODULE 9 — Budget rules

### Backend
- [ ] `BudgetRule` model
- [ ] `App\Services\BudgetService` — `getStatus(user, month)` returns per-rule % consumed
- [ ] `GET /api/budget-rules/status` — returns rules with consumed/limit amounts
- [ ] `apiResource /api/budget-rules`
- [ ] `BudgetRuleResource`
- [ ] `App\Notifications\BudgetAwarenessNudge` — fires when category exceeds rule (non-punitive tone)

### Frontend
- [ ] Budget status widget in DashboardPage — horizontal bars per rule
- [ ] `BudgetRulesPage` at `/orcamento`
- [ ] `src/api/endpoints/budgetRules.ts`

---

## MODULE 10 — Strategic notifications

### Backend
- [ ] Web Push: `PushSubscription` model + `POST /api/push/subscribe` + `DELETE /api/push/unsubscribe`
- [ ] Service worker template (`/public/sw.js`)
- [ ] Notification classes in `App\Notifications\`:
  - `WeeklyReviewNotification` — Sunday 8 PM, recap of the week
  - `MonthlyOpeningNotification` — 1st of month 9 AM, how last month closed
  - `PostSalaryPromptNotification` — detected income transaction ≥ 1k, prompt to review
  - `FridayAnchorNotification` — Friday 6 PM, "weekend is near — how are you doing?"
  - `MilestoneAchievedNotification` — immediate, triggered from `DetectMilestonesJob`
  - `IdleNudgeNotification` — 3 days without any transaction logged
- [ ] Register in scheduler via `notify()` or direct dispatch
- [ ] `GET/PATCH /api/settings/preferences` — user `preferences` JSONB (already in users table)

### Frontend
- [ ] `SettingsPage` at `/configuracoes`
- [ ] `NotificationPreferences` section — toggles per notification type (maps to `preferences` JSONB)
- [ ] `GamificationPreferences` section — toggles for celebrations, streaks
- [ ] Service worker registration in `main.tsx`
- [ ] Push permission request (on first meaningful interaction, not on load)
- [ ] `src/api/endpoints/settings.ts`

---

## MODULE 11 — Full charts

### Backend
- [ ] `GET /api/charts/income-vs-expenses` — monthly bars, last 12 months (from net_worth_snapshots)
- [ ] `GET /api/charts/category-distribution` — by direction, by month or range
- [ ] `GET /api/charts/day-of-week-heatmap` — avg spend by day-of-week

### Frontend
- [ ] `IncomeVsExpensesChart` — Recharts `BarChart` grouped bars (emerald + rose)
- [ ] `CategoryDistributionChart` — Recharts `PieChart` donut
- [ ] `DayOfWeekHeatmap` — CSS grid heatmap (no lib needed)
- [ ] Dedicated `/graficos` page or expandable sections in Dashboard

---

## MODULE 12 — v1.5 refinements (after v1 is live and in use)

- [ ] **Guided onboarding**: first-session tour using a simple step state machine (no lib); steps: create account → add first transaction → set goal → see dashboard
- [ ] **Dark mode**: `ThemeContext` already exists; wire up system preference auto-detect and persist choice in `preferences`
- [ ] **Mobile-responsive polish**: audit all pages on 375px viewport; fix Sidebar collapse on mobile; touch-friendly tap targets
- [ ] **Smart auto-categorization**: when importing, suggest category by Levenshtein distance to existing transactions with categories; show confidence %
- [ ] **Subscription auto-detection**: `SubscriptionDetectionRule` insight → also sets `subscription: true` flag on matched transactions (add column to transactions or use tags)
- [ ] **Gamification telemetry** (internal): track opt-out rate per element, streak avg length, milestone tier distribution — store in `insights` table with `type='telemetry'` or separate table; review monthly before changing anything

---

## What stays out of scope (confirmed non-goals)

- Scraping bancário (pynubank etc.)
- Sistema de XP/pontos abstratos
- Leaderboard / comparação social
- Cotação de ativos ou recomendação de investimentos
- IA conversacional
- Múltiplas moedas
- Open Finance oficial (v2+)
- Mobile app nativo (v3+)
- SaaS billing / multi-tenant (v2+)

---

## Suggested build order

```
M0 (migrations + configs)
  → M3 (recurring) — standalone, low deps
  → M4 (goals) — depends on BurnRate which already exists
  → M1 (import) — high value, unblocks M2
  → M2 (categorization rules) — depends on M1
  → M8 (snapshots) — needed for M11 charts
  → M11 (charts) — depends on M8
  → M6 (insights) — depends on M1, M4, M5
  → M5 (wishlist) — depends on M4 (emergency fund checkpoint)
  → M9 (budget rules) — depends on categories (already done)
  → M7 (gamification) — depends on M4, M5, M6
  → M10 (notifications) — depends on M7
  → M12 (v1.5 refinements) — depends on using the app for real
```
