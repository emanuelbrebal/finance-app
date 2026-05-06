# HANDOFF — finance-app

> Documento de continuidade. Lido por qualquer sessão nova que entre no projeto sem contexto anterior. Atualizar a cada marco relevante.

**Última atualização**: 2026-05-05
**Modelo que escreveu**: claude-sonnet-4-6

---

## Estado atual do projeto

MVP completo. v1 ainda não iniciada. Branch `hardening/p0-p1` concluída — PR pendente de merge.

---

## O que foi feito nesta sessão

Bloco de endurecimento completo (P0.1 → P1.8) na branch `hardening/p0-p1`, com 7 commits atômicos:

| Commit | Items | O que fez |
|---|---|---|
| `497f58e` | P0.1 | `user_id` adicionado ao `$guarded` em Account, Category, Transaction |
| `9d93883` | P1.6 | `whenLoaded('account')` e `whenLoaded('category')` no TransactionResource |
| `d902ee4` | P1.1+P1.2+P1.3 | NetWorthCalculator usa snapshot+delta; bcmath em 3 calculators; timezone do user em `now()` |
| `ba0cba5` | P1.7 | DashboardService extraído; controller agora é 3 linhas |
| `d0d8af6` | P1.5 | 14 testes unitários dos 3 Calculators |
| `6e6326d` | P1.4 | Interceptors 401/419 no axios; queryClient movido para singleton |
| `c28fbf4` | P1.8 | Serviços `worker` e `scheduler` no docker-compose |

**Itens já estavam OK antes desta sessão** (não precisaram de código):
- P0.2 — throttle:60,1 já estava no grupo auth:sanctum
- P0.3 — dedup_hash nunca estava exposto no TransactionResource
- P0.4 — PHP 8.3 já nos 3 lugares (composer.json, Dockerfile, CLAUDE.md)
- P0.5 — teste cross-tenant de bulk-categorize já existia
- P0.6 — SESSION_DRIVER=database e comentários de prod já no .env.example
- Infra de snapshots: migration, model, job, service, scheduler já existiam

---

## Próximos passos

### Imediato: mergar a PR

A branch `hardening/p0-p1` está pronta para review e merge em `main`.

### Depois do merge: começar v1

Ordem recomendada (cada item é uma branch separada):

1. **Importação OFX/CSV** — `App\Domain\Importers\` (OFXImporter, NubankCSVImporter)
   - ProcessImportJob já existe, OFXParser já instalado (asgrim/ofxparser)
   - Ver `docs/06-importacao.md`

2. **Wishlist** — 5 checkpoints de liberação anti-impulso
   - WishlistItem, PriceCheck já têm migration e model
   - WishlistController já tem endpoints básicos
   - Ver `docs/07-wishlist.md`

3. **Gamificação: marcos + streaks**
   - Milestones, Streaks já têm migration e model
   - DetectMilestonesJob e UpdateStreaksJob já existem (stubs?)
   - Ver `docs/05-gamificacao.md`

4. **Insights** — `App\Domain\InsightRules\`
   - InsightRule tagged services, config/insights.php
   - Ver `docs/03-endpoints.md` (seção insights)

5. **Notificações** — database + email + web push
   - Laravel Notifications + Subscribers

---

## Contexto crítico (preservar)

- **`amount` SEMPRE positivo** — direção em campo `direction` ('in'/'out').
- **Valores monetários retornam como string decimal** na API (`"1234.56"`).
- **Snapshots mensais de patrimônio** — `NetWorthCalculator` agora usa snapshot + delta do mês corrente. Snapshots gerados pelo `CaptureNetWorthSnapshotJob` na última noite de cada mês.
- **bcmath** em todos os calculators — não usar `(float)` em operações monetárias.
- **Timezone** — `now($user->timezone)` em todos os cálculos de "mês atual".
- **Multi-tenant** — toda query de resource scoped via `$request->user()->relation()`. Nunca `Model::find($id)` diretamente.
- **`$guarded = ['id', 'user_id']`** — em Account, Category, Transaction.

---

## Arquivos-chave da arquitetura atual

| Camada | Arquivo |
|---|---|
| Calculator | `backend/app/Domain/Calculators/NetWorthCalculator.php` |
| Calculator | `backend/app/Domain/Calculators/BurnRateCalculator.php` |
| Calculator | `backend/app/Domain/Calculators/SavingsRateCalculator.php` |
| Service | `backend/app/Services/DashboardService.php` |
| Service | `backend/app/Services/NetWorthSnapshotService.php` |
| Job | `backend/app/Jobs/CaptureNetWorthSnapshotJob.php` |
| Scheduler | `backend/routes/console.php` |
| Frontend client | `frontend/src/api/client.ts` |
| QueryClient | `frontend/src/lib/queryClient.ts` |

---

## Decisões travadas (não reabrir)

| Decisão | Racional |
|---|---|
| Monolito Laravel + SPA React | Explicitamente travado no CLAUDE.md |
| Sem XP/pontos abstratos | Gamificação usa checkpoints objetivos |
| `amount` sempre positivo + `direction` | Evita bugs de sinal em SUM |
| Multi-tenant por `user_id` | SaaS-ready desde o início |
| Sem scraping bancário | Apenas OFX/CSV |
| PHP 8.3 (não 8.4) | 8.4 muito novo; PECL imaturo |
| bcmath em operações monetárias | Float acumula erro em somas longas |

---

## Referências

- `.claude/plans/auditoria-2026-05-04.md` — auditoria técnica original com todos os issues detalhados
- `CLAUDE.md` — identidade, stack, princípios (leitura obrigatória)
- `docs/` — documentação detalhada por módulo
- `DESIGN.md` — sistema de design (frontend)
