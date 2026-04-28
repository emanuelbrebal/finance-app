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

## Estudo de Concorrência — antes de lançar qualquer coisa publicamente

**Objetivo**: mapear o campo de batalha com precisão, encontrar brechas reais e construir diferenciais competitivos sólidos antes de comunicar o produto ao mercado.

### O que fazer

- [ ] **Auditar plannerfin.com.br** — funcionalidades, UX, preço, proposta de valor, tom de voz, onboarding, limitações percebidas (reviews, redes sociais, App Store)
- [ ] **Auditar pierre.com.br** — idem. Pierre foca em orçamento + IA; mapear o quanto de IA é real vs marketing
- [ ] **Comparativo feature-by-feature** — tabela: feature / plannerfin / pierre / finance-app (✅ tem / ⏳ roadmap / ❌ fora do escopo)
- [ ] **Lacunas identificadas** — o que nenhum dos dois faz bem que você pode fazer melhor
- [ ] **Diferenciais competitivos** — lista priorizada de argumentos de venda exclusivos do finance-app
- [ ] **Posicionamento final** — 1 parágrafo de posicionamento de mercado (para usar em landing page, pitch, bio)

### Eixos de análise sugeridos

| Eixo | Perguntas-chave |
|---|---|
| Foco de produto | Orçamento? Patrimônio? Investimentos? Hábitos? |
| Gamificação | Tem? É saudável ou dark pattern? |
| Importação | OFX, CSV, Open Finance? Manual? |
| Ética de dados | LGPD claro? Self-hosted? Exportação? |
| Preço | Freemium? Assinatura? Quanto? |
| UX mobile | PWA? App nativo? Responsivo só? |
| Onboarding | Demora pra gerar valor? |
| Tom | Culpa? Restrição? Motivação? Neutro? |
| Público-alvo | BR focado? Gen Z? Familiar? Empresarial? |

### Saídas esperadas

- `docs/09-concorrencia.md` com análise completa
- Lista de 5–8 diferenciais competitivos que viram copy da landing page
- Decisão fundamentada sobre o que NÃO implementar (reforço do "fora de roadmap")

---

## Landing Page — após estudo de concorrência

**Objetivo**: página pública que converte visitantes em usuários, comunicando com clareza o valor único do finance-app para o público brasileiro que quer acumular capital.

### Estrutura da LP

- [ ] **Hero** — headline que captura a proposta em 1 frase + subheadline + CTA primário ("Comece grátis")
- [ ] **Problema** — seção empática: a dor do brasileiro que quer guardar mas não sabe onde vai o dinheiro
- [ ] **Solução** — 3 pilares visuais (consciência, acúmulo, gamificação saudável)
- [ ] **Features em destaque** — cards animados com as 5–6 features mais diferenciadas (baseado no estudo de concorrência)
- [ ] **Dashboard preview** — screenshot / mockup interativo do produto real
- [ ] **Prova social** — depoimentos (ou placeholder para quando tiver usuários)
- [ ] **Comparativo** — tabela "finance-app vs alternativas" com diferenciais honestos
- [ ] **Gamificação explicada** — seção mostrando que NÃO é dark pattern — marcos, jornada, sem pontos abstratos
- [ ] **Preço** — seção clara (mesmo que seja "grátis na beta")
- [ ] **FAQ** — 5–7 perguntas mais prováveis
- [ ] **CTA final** — "Comece a controlar seu dinheiro hoje" + formulário de e-mail
- [ ] **Rodapé** — links legais, LGPD, redes sociais

### Stack da LP

- [ ] Opção A: React + Vite (mesmo repo, rota `/`, separada do app)
- [ ] Opção B: Astro standalone (melhor SEO, mais rápido, separação clara)
- [ ] **Decisão**: avaliar no momento da implementação — se SEO for prioridade, Astro; se velocidade de entrega, React mesmo

### SEO e performance

- [ ] Meta tags completas (OG, Twitter Card, canonical)
- [ ] Schema.org markup (SoftwareApplication)
- [ ] Core Web Vitals: LCP < 2.5s, CLS < 0.1
- [ ] Sitemap.xml
- [ ] robots.txt

### CTAs em toda a página

- Hero (principal)
- Após seção de features
- Após comparativo
- Após gamificação
- Antes do FAQ
- Rodapé fixo mobile

---

## v3 — Aplicativo Móvel

**Objetivo**: experiência nativa mobile para registro rápido de gastos, notificações push e consulta de saldo — o app de bolso do acumulador.

### Tecnologia

- [ ] **React Native + Expo** — reaproveitamento máximo do código React existente (hooks, validators, tipos TS)
- [ ] **Expo Router** para navegação (file-based, análogo ao React Router)
- [ ] **TanStack Query** — mesma lib, mesma lógica de cache
- [ ] **NativeWind** para Tailwind em RN
- [ ] **Expo Notifications** para push (substituindo web push no mobile)
- [ ] **Expo SecureStore** para token/sessão

### Funcionalidades MVP mobile

- [ ] Login (Sanctum cookie → token para mobile)
- [ ] Dashboard resumido (patrimônio + mês atual)
- [ ] Registro rápido de gasto: valor + categoria + conta (3 taps)
- [ ] Listagem de transações recentes (últimas 20)
- [ ] Notificações push nativas (marcos, lembretes)
- [ ] Widget de patrimônio na home screen (iOS / Android)

### Diferenciais mobile vs web

- [ ] **Registro de gasto no momento** — abertura em < 2s, foco no fluxo de 3 taps
- [ ] **Câmera para recibo** — foto → OCR → preenchimento sugerido (v3.1)
- [ ] **Widget home screen** — saldo e taxa do mês visíveis sem abrir o app
- [ ] **Modo offline** — fila de transações sync quando voltar conexão
- [ ] **Biometria** — FaceID / TouchID para autenticação rápida

### Distribuição

- [ ] TestFlight (iOS beta) + APK direto (Android beta) para MVP
- [ ] App Store + Google Play quando estabilizar

### Pré-requisito

- Landing page no ar (usuários beta precisam saber o que é o produto)
- Backend estável (v1 completa) — mobile não pode ser mais avançado que o backend

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
