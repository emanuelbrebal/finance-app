# 01 — Arquitetura

## Visão geral

Monolito Laravel servindo API REST + SPA React separada. Redis pra filas e cache. Postgres como único storage. Sem microserviços.

```
┌─────────────────────────────────────────────────────────┐
│                         Browser                          │
│  ┌────────────────────────────────────────────────────┐ │
│  │  React SPA (Vite)                                  │ │
│  │  - React Query (cache de servidor)                 │ │
│  │  - React Router                                    │ │
│  │  - Service Worker (push notifications)             │ │
│  └────────────────────────────────────────────────────┘ │
└──────────────────────────┬──────────────────────────────┘
                           │ HTTPS / JSON
                           │ Sanctum (cookie SPA auth)
┌──────────────────────────▼──────────────────────────────┐
│                    Laravel 11 (PHP 8.3)                  │
│                                                           │
│  HTTP Layer          Domain Layer         Infra Layer    │
│  ┌──────────┐       ┌──────────────┐    ┌────────────┐  │
│  │Controllers│──────▶│   Services   │───▶│Repositories│  │
│  │FormRequest│       │  (use cases) │    │  (Eloquent)│  │
│  │ Resources │       └──────┬───────┘    └────────────┘  │
│  └──────────┘              │                              │
│                      ┌─────▼──────┐                       │
│                      │  Importers │  OfxImporter          │
│                      │ (interface)│  NubankCsvImporter    │
│                      └────────────┘  GenericCsvImporter   │
│                                                           │
│                      ┌────────────┐                       │
│                      │InsightRules│  SavingsRateRule      │
│                      │ (interface)│  CategorySpikeRule    │
│                      └────────────┘  SubscriptionRule     │
│                                      ProjectionRule       │
│                                                           │
│  Scheduler                      Queue Workers             │
│  ┌────────────┐                ┌──────────────────┐      │
│  │Cron daily  │───dispatch────▶│RunInsightsJob    │      │
│  │Cron weekly │                │GenerateRecurring │      │
│  │            │                │SendNotifications │      │
│  └────────────┘                └──────────────────┘      │
└──────────────────────────┬───────────────────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
      ┌───────▼────────┐        ┌──────▼──────┐
      │  PostgreSQL 16 │        │    Redis    │
      │                │        │  (queue +   │
      │                │        │   cache)    │
      └────────────────┘        └─────────────┘
```

## Camadas

### HTTP Layer
- **Controllers** (`App\Http\Controllers\Api\V1\`) — magros, delegam pra Services
- **Form Requests** (`App\Http\Requests\V1\`) — validação fora do controller
- **API Resources** (`App\Http\Resources\V1\`) — formatação consistente da resposta
- **Middleware** — auth (Sanctum), rate limiting, CORS

### Service Layer
- **Services** (`App\Services\`) orquestram use cases
- Recebem dados validados, chamam Domain, persistem, disparam jobs/notifications
- Único ponto onde HTTP encontra negócio

### Domain Layer (`App\Domain\`)
Regras de negócio puras, testáveis sem framework:

- **`Importers/`** — `ImporterInterface`, implementações (Ofx, NubankCsv, NubankCardCsv, GenericCsv), `ImporterRegistry` (detecta formato)
- **`InsightRules/`** — `InsightRule` interface + classes registradas em `config/insights.php`
- **`Milestones/`** — `MilestoneDetector` interface + detectores (NetWorth, Behavior, FinancialHealth, Resistance, JourneyTransition)
- **`Wishlist/Checkpoints/`** — 5 checkpoints como classes (Quarantine, EmergencyFund, PositiveSavingsRate, GoalImpact, StillWanted)
- **`Calculators/`** — NetWorthCalculator, BurnRateCalculator, SavingsRateCalculator, RunwayCalculator, GoalProjectionCalculator

### Infra Layer
- **Eloquent Models** — repositórios implícitos. Sem padrão Repository explícito (overkill para esse projeto)
- **Jobs** assíncronos — Redis-backed
- **Scheduler** — `app/Console/Kernel.php` define agendamentos

## Decisões técnicas justificadas

### Sanctum em vez de Passport
SPA same-origin com cookie auth (stateful). Sanctum é o caminho moderno recomendado pela própria Laravel. Passport é OAuth2 completo — overkill, mais complexo, útil só com terceiros consumindo a API.

### Redis obrigatório desde o MVP
Filas em `database` driver degradam com volume e travam writes no Postgres. Redis custa ~zero em RAM local e dá filas + cache + rate limiting.

### Service Layer explícito
Controllers ficam magros (validação + chamada + resource), lógica vai pra Services. Viabiliza jobs reusarem a mesma lógica dos endpoints sem duplicar.

### Insights como classes com contrato
`InsightRule` interface, cada regra é classe. Job diário itera array em `config/insights.php`. Adicionar regra = criar classe + registrar. Sem if/else gigante.

### Insights em job diário, não real-time
Insights comparam agregados (média 3 meses, etc.). Rodar a cada transação seria desperdício e geraria spam. Diário é suficiente e barato.

### Sem event sourcing, sem CQRS
Você quer saber "quanto gastei" — `SELECT SUM`. Não precisa reconstruir estado a partir de eventos.

### Sem padrão Repository explícito
Eloquent Models já abstraem persistência o suficiente. Adicionar Repository seria cerimônia sem ganho real nesse tamanho de projeto.

### Domain Layer como pasta de primeira classe
Não é DDD tático. É só separação prática: o que é regra de negócio pura (testável sem framework) vs o que é orquestração (Services) vs HTTP (Controllers).

### `amount` positivo + `direction` em vez de signed amount
Evita bug clássico de somar sinal errado. Queries de total ficam explícitas (`WHERE direction = 'out'`). Mais legível que `SUM(amount)` com amounts negativos misturados.

### Snapshots mensais em vez de cálculo on-the-fly
- Performance: dashboard não precisa somar dezenas de milhares de transações
- Histórico imutável: ver patrimônio em data passada mesmo após editar transações antigas
- Custo: uma migration + um job mensal

### Tagged services no container
`app()->tagged('insight_rules')` retorna todas as regras registradas. `DomainServiceProvider` faz a tag. Sem ler array manualmente em runtime.

## Comunicação entre camadas

```
Request HTTP
    ↓
Controller (recebe FormRequest validado)
    ↓
Service (orquestra)
    ↓
Domain (regra pura) + Eloquent (persistência)
    ↓
Resource (formata)
    ↓
Response JSON
```

Para operações assíncronas:

```
Controller → Service → dispatch(Job)
                         ↓
                       Queue (Redis)
                         ↓
                       Worker → Service → Domain + Eloquent → Notification
```

## Fluxo: importação de extrato

```
POST /api/v1/imports
    ↓ (multipart upload)
ImportController::store
    ↓
ImportService::createBatch
    ↓ salva arquivo, cria ImportBatch (status=pending)
    ↓
dispatch(ProcessImportJob)
    ↓
ProcessImportJob::handle (worker)
    ↓
ImporterRegistry::detect → OfxImporter (ou outro)
    ↓
Importer::parse → array<ParsedTransaction>
    ↓
CategorizationRuleApplier::suggest (aplica regras)
    ↓
salva preview no batch
    ↓
notifica frontend (websocket OU polling em /imports/{id})
    ↓
usuário confirma via POST /imports/{id}/confirm
    ↓
ImportService::confirm → cria Transactions + dedup hash
```

## Fluxo: geração de insights

```
Schedule diário 06:00 dispara RunInsightsJob
    ↓
RunInsightsJob itera todos os users ativos
    ↓
para cada user, itera config('insights.rules')
    ↓
cada InsightRule::evaluate(user) → ?Insight
    ↓
se retornou Insight, salva (com dedup_key)
    ↓
se severity merece notificação, dispatch Notification
```

## Fluxo: detecção de marco

```
Schedule diário dispara DetectMilestonesJob
    ↓
itera config('milestones.detectors')
    ↓
cada MilestoneDetector::detect(user) → array<Milestone>
    ↓
salva com dedup_key (evita duplicar mesmo marco)
    ↓
frontend consulta /milestones/uncelebrated no próximo load
    ↓
modal de celebração escalada por tier
```

## Pontos de extensibilidade

| O que | Onde adicionar |
|---|---|
| Novo formato de extrato | Implementar `ImporterInterface` + registrar em `config/importers.php` |
| Nova regra de insight | Implementar `InsightRule` + registrar em `config/insights.php` |
| Novo tipo de marco | Implementar `MilestoneDetector` + registrar em `config/milestones.php` |
| Novo checkpoint da wishlist | Implementar interface de checkpoint + adicionar em `CheckpointEvaluator` |
| Novo canal de notificação | Implementar Notification channel (Web Push, WhatsApp, etc.) + via() do Notification |

## Fora de escopo (consciente)

- Microserviços
- Event sourcing / CQRS
- Domain-Driven Design tático (Aggregates, Bounded Contexts formais)
- Kubernetes / orquestração avançada
- gRPC / GraphQL
- Repositórios explícitos
- Múltiplas moedas
- Real-time via websockets (polling resolve)
