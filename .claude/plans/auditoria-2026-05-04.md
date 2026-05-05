# Auditoria técnica — finance-app

**Data**: 2026-05-04
**Persona**: Startup CTO
**Escopo**: MVP completo (Fatias 3a–3o), branch `main` + worktree `claude/kind-bhabha-93be1f`
**Status do projeto**: MVP entregue (Auth + Accounts + Categories + Transactions + Dashboard + Profile). v1 não iniciada.

---

## TL;DR

O MVP está **estruturalmente sólido**: camadas separadas como manda o CLAUDE.md (Controllers magros → Form Requests → Services/Domain → Resources), multi-tenancy aplicado corretamente em três pontos (validation, scoping por relationship, policies), testes de boundary cobrindo cross-tenant em accounts/transactions/categories. **Nada de hack acumulado.**

Mas há **6 pontos P0** que valem 1–2 dias de endurecimento **antes** de começar v1 — porque v1 dobra a superfície de ataque (Importers, Insights, Wishlist, Milestones) e qualquer dívida hoje vira bola de neve. Mais importante: o princípio #10 (snapshots mensais de patrimônio) já está sendo violado e ficará caro de corrigir depois que houver volume de transações.

**Recomendação**: bloco de endurecimento (~2 dias) → começar v1.

---

## 1. Arquitetura & estrutura de pastas

### O que está certo

| Item | Status |
|---|---|
| Service Layer real (`AuthService`, `DefaultCategoriesService`) | ✅ |
| Controllers magros (validação + chamada + resource) | ✅ |
| Form Requests separados em `Http/Requests/V1/` | ✅ |
| API Resources em `Http/Resources/V1/` | ✅ |
| API versionada (`/api/v1`) via `apiPrefix` em [bootstrap/app.php](backend/bootstrap/app.php:15) | ✅ |
| `App\Domain\Calculators` separado, regras puras | ✅ |
| Policies registradas em [AppServiceProvider.php](backend/app/Providers/AppServiceProvider.php:23) | ✅ |
| Sanctum SPA cookie via `EnsureFrontendRequestsAreStateful` | ✅ |
| Frontend com camadas claras: `pages/` orquestra, `components/` burros, `hooks/queries/`, `hooks/mutations/`, `api/endpoints/`, `lib/validators/` | ✅ |
| Vite proxy resolve same-origin pra Sanctum em dev | ✅ |

### Gaps vs documentação (esperados — features futuras)

São pastas/arquivos planejados para v1 que ainda não existem. **Não é dívida**, é roadmap:

- `App\Domain\Importers/` (planejado v1)
- `App\Domain\InsightRules/` (v1)
- `App\Domain\Milestones/` (v1)
- `App\Domain\Wishlist/` (v1)
- `config/insights.php`, `config/importers.php`, `config/milestones.php` (v1)
- Tagged services no container (v1, junto com as classes acima)
- Queue workers no `docker-compose.yml` (precisa antes de v1)
- Scheduler em [routes/console.php](backend/routes/console.php) — atualmente só tem `inspire` (precisa antes de v1)

---

## 2. Schema do banco

### O que está certo

- `user_id` em **todas** as tabelas tenant-scoped (accounts, categories, transactions) ✅
- FK com `cascadeOnDelete` em accounts/transactions, `nullOnDelete` em `transactions.category_id` ✅
- `numeric(14,2)` para dinheiro, `timestamptz` para timestamps, `date` para datas puras ✅
- Soft delete em transactions ([create_transactions_table.php:25](backend/database/migrations/2026_04_27_235640_create_transactions_table.php:25)) ✅
- "Soft archive" em accounts/categories via `archived_at` ✅
- Índices compostos pensando em queries multi-tenant: `(user_id, occurred_on)`, `(user_id, category_id, occurred_on)`, `(user_id, kind)` ✅
- `unique(user_id, dedup_hash)` em transactions ✅
- `unique(user_id, name)` em categories ✅
- `direction` separado de `amount` (sempre positivo) — princípio #7 respeitado ✅

### Lacunas

| Lacuna | Gravidade | Quando |
|---|---|---|
| Tabela `net_worth_snapshots` não criada — viola princípio #10 explícito ("não calcular via SUM em runtime no dashboard") | **Alta** | Antes de escalar usuários |
| Sem índice GIN em `transactions.tags` — busca por tag via `whereJsonContains` faz seq scan ([TransactionController.php:47](backend/app/Http/Controllers/Api/V1/TransactionController.php:47)) | Média | Quando volume crescer |
| Sem índice `(user_id, deleted_at)` em transactions — queries com soft-deleted rodam scan | Baixa | Quando volume crescer |
| 11 tabelas planejadas em [docs/02-schema.md](docs/02-schema.md) ainda não criadas (recurring_transactions, goals, import_batches, categorization_rules, insights, budget_rules, wishlist_items, price_checks, milestones, streaks, net_worth_snapshots) | n/a | Roadmap v1 |

---

## 3. Endpoints da API

### O que está certo

- Versionamento `/api/v1` via `apiPrefix` ✅
- `apiResource` para CRUD padrão; rotas custom (`/transactions/summary`, `/bulk-categorize`, `/categories/seed`) registradas **antes** do apiResource para evitar conflito com `/{id}` ✅
- Pagination com `per_page` capado em 100 ([TransactionController.php:53](backend/app/Http/Controllers/Api/V1/TransactionController.php:53)) ✅
- Filtros via query string (`from`, `to`, `account_id`, `category_id`, `direction`, `search`, `tag`, `out_of_scope`) ✅
- Throttle em login/register (`throttle:10,1`) ([routes/api.php:30](backend/routes/api.php:30)) ✅
- `POST /transactions` é **idempotente**: se `dedup_hash` colide, retorna 200 com a transaction existente em vez de 409/duplicate ([TransactionController.php:77-82](backend/app/Http/Controllers/Api/V1/TransactionController.php:77)) ✅
- Health check com checagem real de DB e Redis ✅

### Issues

| # | Issue | Onde | Impacto |
|---|---|---|---|
| E1 | `dedup_hash` exposto no payload — leak de internals, atrapalha caching no client e expõe estratégia de dedup | [TransactionResource.php:24](backend/app/Http/Resources/V1/TransactionResource.php:24) | Baixo, fácil corrigir |
| E2 | Eager-loaded `account` e `category` **não chegam** ao client — `TransactionResource` não usa `whenLoaded()`, então o frontend faz lookup separado e perde contexto | [TransactionResource.php](backend/app/Http/Resources/V1/TransactionResource.php) | Médio (UX + bandwidth) |
| E3 | `DashboardController` é um "kitchen-sink" de ~80 linhas inline, mistura 5+ chunks de cálculo no controller | [DashboardController.php:20-82](backend/app/Http/Controllers/Api/V1/DashboardController.php:20) | Baixo agora, vira God Object quando entrar insights/marcos |
| E4 | **Sem rate limit em rotas autenticadas** — só auth tem `throttle:10,1`. Atacante autenticado pode criar 100k transações em segundos | [routes/api.php:39](backend/routes/api.php:39) | Alto |
| E5 | Comportamento idempotente do `POST /transactions` é silencioso — nem 200 vs 201 nem header (`X-Duplicate: true`) sinalizam ao client que foi dedup | [TransactionController.php:77](backend/app/Http/Controllers/Api/V1/TransactionController.php:77) | Baixo (UX) |

---

## 4. Segurança & autenticação

### O que está certo

- **Sanctum SPA cookie-based** com `statefulApi()` ([bootstrap/app.php:18](backend/bootstrap/app.php:18)) ✅
- `Auth::attempt()` + `session->regenerate()` no login ([AuthService.php:30-38](backend/app/Services/AuthService.php:30)) ✅
- `session->invalidate()` + `regenerateToken()` no logout ✅
- Password com cast `'hashed'` no User model — nunca grava plaintext ✅
- Form Requests **filtram** atributos: `user_id` e `archived_at` não chegam ao `Model::create()` via API ✅
- Policies fazem checagem `user_id === user_id` em show/update/delete dos 3 modelos ✅
- `Rule::exists('accounts', 'id')->where('user_id', $userId)` em StoreTransactionRequest **previne IDOR** de account_id e category_id ([StoreTransactionRequest.php:23-28](backend/app/Http/Requests/V1/StoreTransactionRequest.php:23)) ✅
- Tests cobrem cross-tenant: `test_cannot_show_others_transaction`, `test_user_cannot_show_others_account`, `test_user_cannot_update_others_account` ✅

### Issues críticos

| # | Issue | Onde | Impacto |
|---|---|---|---|
| S1 | **Mass-assignment defesa em profundidade**: `user_id`, `archived_at` (e `deleted_at` implícito via SoftDeletes) estão em `$fillable` de Account/Category/Transaction. Hoje só está seguro porque **todo write passa por Form Request validado**. No dia em que alguém usar `Model::create($request->all())` em outro lugar (job, comando artisan, segundo controller), abre buraco — transferência de ownership ou unarchive arbitrário ficam possíveis | [Account.php:13](backend/app/Models/Account.php:13), [Category.php:17](backend/app/Models/Category.php:17), [Transaction.php:18](backend/app/Models/Transaction.php:18) | **Alto** (latente) |
| S2 | **`bulk-categorize` não tem teste de cross-tenant**. O código provavelmente está seguro (`$request->user()->transactions()->whereIn('id', ...)` filtra implicitamente), mas é uma operação de mutação em massa sem prova | [TransactionControllerTest.php:203](backend/tests/Feature/TransactionControllerTest.php:203) | Médio |
| S3 | **Sem rate limit em rotas autenticadas** (ver E4) — também é um issue de segurança (DoS por exhaustion) | [routes/api.php:39](backend/routes/api.php:39) | **Alto** |
| S4 | **`SESSION_DRIVER=cookie` em [.env.example](backend/.env.example:30)** mas o config default em [config/session.php:21](backend/config/session.php:21) é `database`. Cookie session encripta no client mas dá pra fazer replay e cresce o cookie. Para SPA Sanctum, `database` ou `redis` é o correto | [.env.example:30](backend/.env.example:30) | Médio (config drift) |
| S5 | **`APP_DEBUG=true` em [.env.example](backend/.env.example:4)** — OK pra dev, mas **não há `.env.production.example`** ou guidance para produção (deve ser `APP_DEBUG=false`, `APP_ENV=production`, `SESSION_SECURE_COOKIE=true`, `SESSION_SAME_SITE=strict`) | [.env.example](backend/.env.example) | Médio (deploy) |
| S6 | **`Password::min(8)`** sem complexidade. Para finance app, considere `->letters()->numbers()->uncompromised()` ou pelo menos `mixedCase()` | [RegisterRequest.php:20](backend/app/Http/Requests/V1/Auth/RegisterRequest.php:20), [UpdateProfileRequest.php:21](backend/app/Http/Requests/V1/UpdateProfileRequest.php:21) | Médio |
| S7 | **`csrfFetched` cached em memória do client**: se a sessão expirar, próxima mutação falha com 419 (CSRF token mismatch) e **não há retry**. Frontend não tem interceptor de 419 para refetch o CSRF cookie | [client.ts:12](frontend/src/api/client.ts:12) | Médio (UX) |
| S8 | **Sem interceptor de 401 no axios client**: usuário com sessão expirada vê erros aleatórios, não é redirecionado pra `/login` automaticamente | [client.ts](frontend/src/api/client.ts) | Médio (UX) |

### Issues menores

- `TransactionResource` exposes `user_id` (já é o user logado, redundante)
- `/health` retorna `database` e `redis` por nome — fingerprinting menor; ok pra dev, considere reduzir em prod
- Sem `config/cors.php` explícito — vai morder quando o frontend rodar em domínio diferente do backend em prod

---

## 5. Dívida técnica

### Princípios do CLAUDE.md violados

| Princípio | Estado atual | Custo de corrigir agora vs depois |
|---|---|---|
| #10 — "Snapshots mensais de patrimônio em tabela própria (não calcular via SUM em runtime no dashboard)" | `NetWorthCalculator` faz `SUM` por conta no dashboard a cada request ([NetWorthCalculator.php:35-45](backend/app/Domain/Calculators/NetWorthCalculator.php:35)) | Agora: 2–4h. Depois (com 100k+ transactions/user): também precisa migration retroativa de snapshots históricos |

### Antipatterns no código

| # | Item | Onde | Risco |
|---|---|---|---|
| D1 | **Float arithmetic em valores monetários** — `(float) $amount` em todos os 3 calculators. Acumula erros em contas longas | [NetWorthCalculator.php:52-55](backend/app/Domain/Calculators/NetWorthCalculator.php:52), [BurnRateCalculator.php:40](backend/app/Domain/Calculators/BurnRateCalculator.php:40), [SavingsRateCalculator.php:34-37](backend/app/Domain/Calculators/SavingsRateCalculator.php:34) | Centavos divergindo do somatório real |
| D2 | **`now()` ignora `users.timezone`** — "mês atual" é calculado em server TZ. Para um usuário em America/Sao_Paulo num servidor UTC, dia 1 às 00h-02h cai no mês anterior | [DashboardController.php:23](backend/app/Http/Controllers/Api/V1/DashboardController.php:23), [BurnRateCalculator.php:17-18](backend/app/Domain/Calculators/BurnRateCalculator.php:17) | Cálculos errados em borda de mês |
| D3 | `DashboardController` lógica inline em vez de em `DashboardService` (viola princípio "Service Layer explícito") | [DashboardController.php:20-82](backend/app/Http/Controllers/Api/V1/DashboardController.php:20) | Manutenção |
| D4 | `TransactionResource` sem `whenLoaded()` para nested relations (ver E2) | [TransactionResource.php](backend/app/Http/Resources/V1/TransactionResource.php) | Bandwidth + UX |

### Inconsistências de configuração

- **PHP version**: `composer.json` exige `^8.2`, `Dockerfile` instala `php:8.4`, CLAUDE.md diz "8.3+". Padronizar em uma versão e refletir nos 3 lugares.
- **SESSION_DRIVER**: `.env.example` diz `cookie`, `config/session.php` default é `database` (ver S4).

### Falta de cobertura de testes

- **Calculators (Unit)** — princípio #5 do CLAUDE.md ("cobertura de Domain é") não cumprido para `NetWorthCalculator`, `BurnRateCalculator`, `SavingsRateCalculator`
- **`bulk-categorize` cross-tenant** (ver S2)
- **`DefaultCategoriesService::seedFor`** — idempotência, casos com nomes existentes parciais
- **Dashboard** — `DashboardTest.php` existe mas não auditei; vale revisar

### Falta infra

- Docker compose sem **queue worker** — vai precisar antes do primeiro job (importação OFX, geração de insights)
- Docker compose sem **scheduler** (`php artisan schedule:work`) — vai precisar antes de snapshots/recurring/insights
- Sem `config/cors.php` explícito (ver issue menor de segurança)

---

## 6. Priorização

### P0 — Crítico (corrigir antes de v1) — ~1 dia

| # | Item | Esforço | Por quê agora |
|---|---|---|---|
| P0.1 | Trocar `$fillable` por `$guarded = ['user_id']` (ou remover `user_id` do `$fillable`) em Account/Category/Transaction. Setar via `$user->relation()->create($validated)` que já é o padrão usado | 15 min | v1 vai introduzir jobs e novos services que escrevem nas mesmas tabelas. Defesa em profundidade agora, antes de virar buraco |
| P0.2 | Adicionar `throttle:60,1` (ou similar) ao group `auth:sanctum` em `routes/api.php` | 5 min | Hoje não há limite — DoS por usuário autenticado é trivial |
| P0.3 | Remover `dedup_hash` do `TransactionResource` | 2 min | Leak de internals, fácil de remover |
| P0.4 | Padronizar versão PHP entre `composer.json` (`^8.3`), Dockerfile (`8.3-fpm-alpine`) e CLAUDE.md | 10 min | 8.4 é muito novo, ecossistema (extensões PECL) ainda imaturo. Pin em 8.3 |
| P0.5 | Adicionar teste de cross-tenant em `bulk-categorize` (user A não consegue categorizar transactions do user B) | 10 min | Mutação em massa sem prova de isolamento é o tipo de coisa que vira CVE |
| P0.6 | Resolver `SESSION_DRIVER=cookie` no `.env.example` (mudar pra `database` ou `redis`) e adicionar `SESSION_SECURE_COOKIE` + `SESSION_SAME_SITE` comentados com guidance pra prod | 5 min | Drift de config entre dev e prod é fonte clássica de bug |

**Total P0**: ~50 min de código + ~5 min de testes/checks. **Trivial de fazer numa sessão.**

### P1 — Importante (antes de escalar / nas primeiras semanas de v1) — ~1–2 dias

| # | Item | Esforço |
|---|---|---|
| P1.1 | Implementar tabela `net_worth_snapshots` + job mensal + atualizar `NetWorthCalculator` pra ler snapshot + delta do mês corrente. Backfill retroativo a partir das transactions existentes | 2–4h |
| P1.2 | Substituir float por `bcmath` (`bcadd`, `bcsub`, `bcdiv`) nos 3 Calculators. Manter retorno como string formatada (já é) | 1h |
| P1.3 | Aplicar `$user->timezone` nos cálculos de "mês atual" e "últimos N meses" (Carbon `setTimezone`) | 30 min |
| P1.4 | Adicionar interceptors no axios client: 401 → redirect `/login` + clear queries; 419 → reset `csrfFetched` + retry uma vez | 30 min |
| P1.5 | Tests unit dos 3 Calculators cobrindo: empty user, transações soft-deleted, out_of_scope, divisão por zero | 1h |
| P1.6 | `TransactionResource` usar `whenLoaded()` para `account` e `category` (e expor opcional via `?include=`) | 15 min |
| P1.7 | Mover lógica do `DashboardController` para `DashboardService`. Deixar controller só chamando o service e devolvendo array | 30 min |
| P1.8 | Adicionar `worker` e `scheduler` services no `docker-compose.yml` (mesmo que ainda sem jobs, deixa estrutura pronta) | 15 min |

### P2 — Pode esperar (qualidade contínua, antes de produção real)

| # | Item | Esforço |
|---|---|---|
| P2.1 | Endurecer `Password` rule: `->letters()->numbers()->uncompromised()` | 5 min |
| P2.2 | Criar `config/cors.php` explícito quando expor frontend em domínio separado | 15 min |
| P2.3 | Health endpoint: simplificar payload em `APP_ENV=production` (só `ok`/`degraded`, sem nomes de serviço) | 10 min |
| P2.4 | Adicionar índices `(user_id, deleted_at)` e GIN em `transactions.tags` quando volume justificar (>10k transactions/user) | 20 min |
| P2.5 | Criar `.env.production.example` com guidance | 15 min |
| P2.6 | Documentar comportamento idempotente do `POST /transactions` no `docs/03-endpoints.md` (header `X-Duplicate` opcional) | 10 min |
| P2.7 | Cobertura test do `DefaultCategoriesService` (idempotência) e revisão geral do `DashboardTest` | 30 min |

---

## 7. Recomendação como CTO

1. **Bloco de endurecimento de 1–2 dias** focado em P0 + P1.1 (snapshots). Por quê:
   - Antes de v1 = janela mais barata pra mexer em fundamentos. Ainda só tem 1 usuário (você).
   - v1 vai mais que dobrar a superfície (Importers parseando arquivos externos, Insights iterando todos users, Wishlist com integrações futuras de price tracking). Snapshots de patrimônio precisam existir antes do gráfico de evolução virar feature.
   - Calculators sem teste vão virar fonte oculta de bugs em insights e marcos (que dependem deles).

2. **Não tente "consertar tudo agora".** P2 é genuinamente "depois". Não atrase v1 por isso.

3. **Confirme antes de mexer no schema (P1.1)**: a migration de `net_worth_snapshots` é a única que tem backfill — quer fazer junto com a primeira migration de v1 ou separado? Sugestão: **separado, como último commit do bloco de endurecimento**, antes de abrir a v1. Isolar.

4. **O que NÃO recomendo investir agora**: refatoração de Repository pattern, abstrações de query builder, separação de read/write models. O CLAUDE.md já trava isso explicitamente, e o tamanho do projeto não justifica.

---

## 8. Apêndice — checklist de execução

Marque conforme avança. Cada item é um commit atômico.

### Endurecimento (sprint dedicada)

- [ ] P0.1 — `$guarded` em Account/Category/Transaction
- [ ] P0.2 — `throttle:60,1` no group autenticado
- [ ] P0.3 — Remover `dedup_hash` do TransactionResource
- [ ] P0.4 — Padronizar PHP 8.3 (composer + Dockerfile + CLAUDE.md)
- [ ] P0.5 — Test cross-tenant `bulk-categorize`
- [ ] P0.6 — `.env.example` com `SESSION_DRIVER=database` + comentários prod
- [ ] P1.2 — bcmath nos Calculators
- [ ] P1.3 — Timezone do user nos cálculos
- [ ] P1.5 — Tests dos Calculators (unit)
- [ ] P1.6 — `whenLoaded()` no TransactionResource
- [ ] P1.7 — `DashboardService`
- [ ] P1.4 — Interceptors 401/419 no axios
- [ ] P1.8 — Worker + scheduler no docker-compose
- [ ] **P1.1 — Snapshots de patrimônio** (último, isolado, com backfill)

### Conforme tocar áreas relacionadas

- [ ] P2.1 — Endurecer Password
- [ ] P2.2 — CORS explícito (quando subir staging)
- [ ] P2.3 — Health reduzido em prod
- [ ] P2.4 — Índices (user_id, deleted_at) e GIN tags (quando volume justificar)
- [ ] P2.5 — `.env.production.example`
- [ ] P2.6 — Documentar idempotência do POST transactions
- [ ] P2.7 — Cobertura DefaultCategoriesService + Dashboard
