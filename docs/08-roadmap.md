# 08 — Roadmap

Faseamento por valor entregue ao usuário, não por tamanho técnico. Cada fase deve ser **utilizável de ponta a ponta** antes de avançar.

---

## MVP — ~1 semana de trabalho focado

**Objetivo**: você consegue usar pra controlar suas finanças de verdade.

### Backend

- [ ] Setup Laravel 11 + Postgres 16 + Redis no Docker
- [ ] Sanctum configurado (cookie-based auth)
- [ ] Migrations: users, accounts, categories, transactions, recurring_transactions, goals, import_batches, categorization_rules, wishlist_items, price_checks, milestones, streaks, net_worth_snapshots, insights, budget_rules
- [ ] Models com relacionamentos
- [ ] Seeder de categorias padrão BR
- [ ] Endpoints:
  - [ ] `/auth/*` — register, login, logout, me
  - [ ] `/profile` (GET, PATCH)
  - [ ] `/accounts` (CRUD)
  - [ ] `/categories` (CRUD + seed)
  - [ ] `/transactions` (CRUD + summary)
  - [ ] `/dashboard` (versão básica: net worth, savings rate, top expenses, recent transactions)
- [ ] Form Requests + API Resources pra todos
- [ ] Calculators básicos: NetWorth, BurnRate, SavingsRate

### Frontend

- [ ] Setup Vite + React 18 + TS + Tailwind + shadcn/ui
- [ ] React Query + React Router configurados
- [ ] axios client com CSRF + interceptors
- [ ] Páginas: Login, Register, Dashboard, Transactions (list + form), Accounts, Categories
- [ ] Componente `Money.tsx` com formatação BRL
- [ ] Layout com Sidebar + Topbar
- [ ] Dashboard com 4-5 widgets básicos
- [ ] Listagem de transações com filtros básicos (period, category, account)

### Validação do MVP

Você consegue:
1. Criar conta, fazer login
2. Adicionar contas (Nubank, Conta Corrente, Dinheiro)
3. Adicionar transações manualmente
4. Categorizar transações
5. Ver dashboard com patrimônio total e taxa de poupança do mês

---

## v1 — ~3 semanas adicionais

**Objetivo**: o app vira diferenciado. Importação, gamificação, insights, wishlist, automações.

### Importação
- [ ] OfxImporter funcional
- [ ] NubankCsvImporter (conta + cartão)
- [ ] GenericCsvImporter com mapeamento manual de colunas
- [ ] Endpoints: `/imports`, preview, confirm, revert
- [ ] Tela `FileDropzone` + `ImportPreviewTable` editável
- [ ] Deduplicação por hash funcionando
- [ ] Histórico de importações

### Categorização que aprende
- [ ] CRUD `/categorization-rules`
- [ ] `apply-to-existing` retroativo
- [ ] Frontend: prompt "aplicar a todas com 'IFOOD'?" após categorização manual

### Recorrentes
- [ ] CRUD `/recurring-transactions`
- [ ] `GenerateRecurringTransactionsJob` rodando mensal
- [ ] Tela de gestão de recorrentes

### Objetivos + Reserva de emergência
- [ ] CRUD `/goals` com `is_emergency_fund`
- [ ] `/goals/emergency-fund` shortcut
- [ ] `/goals/emergency-fund/auto-target` (6× burn rate)
- [ ] Cálculo "quanto/mês pra bater"
- [ ] Widget `EmergencyFundWidget` no dashboard

### Wishlist
- [ ] CRUD `/wishlist` completo
- [ ] 5 checkpoints implementados como classes
- [ ] Status automático `waiting` → `ready_to_buy`
- [ ] Marcos de resistência
- [ ] Tela com `CheckpointsPanel` visual
- [ ] `/wishlist/{id}/check-prices` retornando 501 amigável

### Insights
- [ ] Interface `InsightRule` + 6 regras iniciais (SavingsRateRecord, CategorySpike, SubscriptionDetection, GoalProjection, IdleCash, WishlistResistance)
- [ ] `RunInsightsJob` diário
- [ ] CRUD `/insights` com mark-read e dismiss
- [ ] `InsightsFeed` no dashboard

### Gamificação
- [ ] Marcos: `MilestoneDetector` interface + 5 detectores (NetWorth, Behavior, FinancialHealth, Resistance, JourneyTransition)
- [ ] `DetectMilestonesJob` diário
- [ ] Streaks: `UpdateStreaksJob` semanal e mensal
- [ ] Níveis da jornada: `config/journey.php` + `JourneyController`
- [ ] Frontend: `MilestoneCelebration` modal com 4 tiers (small/medium/large/epic)
- [ ] `MilestonesTimeline` em `/jornada`
- [ ] `JourneyLevelBadge` no dashboard
- [ ] `StreakIndicator`

### Notificações estratégicas
- [ ] Web Push: service worker + subscription endpoint
- [ ] Notifications: WeeklyReview (dom 20h), MonthlyOpening (dia 1, 9h), PostSalaryPrompt (detecção), FridayEveningAnchor (sex 18h), MilestoneAchieved (imediato), idle nudge (3 dias)
- [ ] Tela `/settings/preferences` com toggles

### Snapshots
- [ ] Migration `net_worth_snapshots`
- [ ] `CaptureNetWorthSnapshotJob` no último dia do mês
- [ ] Endpoint `/charts/net-worth-evolution` lendo da tabela
- [ ] Recalcula `journey_level` no mesmo job

### Budget rules
- [ ] CRUD `/budget-rules`
- [ ] `/budget-rules/status` com % consumido
- [ ] Notification `BudgetAwarenessNudge` quando ultrapassa (não-punitivo)

### Gráficos completos
- [ ] NetWorthEvolutionChart (linha com projeção pontilhada)
- [ ] IncomeVsExpensesChart (barras agrupadas)
- [ ] CategoryDistributionChart (donut)
- [ ] DayOfWeekHeatmap

### Validação da v1

Você consegue:
1. Importar extrato Nubank OFX e ter tudo categorizado automaticamente após algumas correções
2. Ver evolução patrimonial dos últimos 12 meses + projeção da meta
3. Ter sua reserva de emergência calculada e monitorada
4. Cadastrar items na wishlist e ver os 5 checkpoints
5. Receber notificação de marco quando bater R$ 15k
6. Ver insights automáticos toda manhã
7. Configurar regras de orçamento por categoria

---

## v1.5 — ~2 semanas adicionais

**Objetivo**: refinamentos baseados no uso real + features que pedem por dados acumulados.

- [ ] **Modo co-piloto**: PWA com botão "vou gastar agora" — você digita valor + categoria, sistema mostra impacto antes de comprar
- [ ] **Busca de preços sob demanda**: integração SerpAPI para `/wishlist/{id}/check-prices`
- [ ] **Auto-categorização inteligente**: sugestão por similaridade de descrição (Levenshtein)
- [ ] **Detecção automática de assinaturas**: padrão de mesmo valor + descrição + frequência mensal vira insight + flag em transação
- [ ] **Telemetria de gamificação**: métricas pra entender se está saudável (taxa de opt-out, correlação streak × patrimônio)
- [ ] **Onboarding guiado**: tour em primeira sessão
- [ ] **Modo escuro**: tema dark
- [ ] **Mobile responsive completo**: hoje deve funcionar, mas refinar UX touch

---

## v2 — quando virar produto

**Objetivo**: SaaS multi-tenant maduro.

- [ ] **Modo desafio**: sprints de economia ("outubro sem delivery", "30 dias sem Lazer"), tabela `challenges`, lógica de avaliação automática
- [ ] **Monitoramento contínuo de preços**: job diário reconsulta items da wishlist, alerta quando cai
- [ ] **Histórico de preços**: gráfico de variação por item
- [ ] **Compartilhamento entre usuários**: contas conjuntas (casal/família), tabela `account_shares` ou `workspaces`
- [ ] **Open Finance oficial**: substituir importação manual quando virar produto, pré-requisito de homologação
- [ ] **Planos e billing**: integração Stripe / Pagar.me
- [ ] **Multi-currency**: suporte real (não só campo no schema)
- [ ] **Exportação de dados**: LGPD compliance — usuário baixa tudo
- [ ] **API pública**: tokens via Sanctum, documentação OpenAPI

---

## Fora de roadmap (decisões conscientes)

Itens que parecem boas ideias mas que decidimos NÃO implementar:

- ❌ Scraping bancário (pynubank etc.) — frágil, zona cinza
- ❌ Sistema de XP/pontos abstratos — treina cérebro errado
- ❌ Leaderboard / comparação social — gera ansiedade em app financeiro
- ❌ Integração com cotação de ativos — não é app de investimento
- ❌ Importação de e-mail / NFe — pesadelo de manutenção
- ❌ IA conversacional ("pergunte sobre seus gastos") — não move agulha pro problema real
- ❌ Microserviços, event sourcing, DDD tático — overengineering
- ❌ Múltiplas moedas (real, funcional) — adicionar quando necessário, não antes

---

## Princípio orientador

A cada item do roadmap, perguntar: **"isso ajuda a juntar mais dinheiro ou ter mais consciência de consumo?"**

Se a resposta não for óbvia, despriorizar.
