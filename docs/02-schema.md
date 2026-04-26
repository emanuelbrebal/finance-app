# 02 — Schema do PostgreSQL

Schema multi-tenant via `user_id` desde o início. Soft deletes onde faz sentido. Valores monetários em `numeric(14,2)` (suficiente pra patrimônios bilionários sem perda de precisão).

## Convenções

- **PKs**: `bigserial` (incremento automático)
- **FKs**: `bigint` referenciando o id correspondente
- **Timestamps**: `timestamptz` (com timezone)
- **Datas**: `date` (sem hora)
- **Dinheiro**: `numeric(14,2)` — sempre **positivo** em transações; direção indicada por campo separado
- **JSON**: `jsonb` (suporta índice GIN se preciso)
- **Hash**: `char(64)` (sha256 hex)

---

## `users`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| name | varchar(120) | |
| email | varchar(180) UNIQUE | |
| password | varchar(255) | bcrypt |
| email_verified_at | timestamptz null | |
| target_net_worth | numeric(14,2) null | meta (ex: 100000.00) |
| target_date | date null | data-alvo da meta |
| estimated_monthly_income | numeric(14,2) null | |
| timezone | varchar(64) default 'America/Sao_Paulo' | |
| journey_level | varchar(40) null | cache do nível atual (recalculado mensalmente) |
| preferences | jsonb default '{}' | toggles de gamificação/notificação |
| remember_token | varchar(100) null | Laravel padrão |
| created_at, updated_at | timestamptz | |

**Índices**: `email` (UNIQUE).

**`preferences` schema**:
```json
{
  "celebrations_enabled": true,
  "streaks_enabled": true,
  "notifications_enabled": true,
  "weekly_review_enabled": true,
  "monthly_opening_enabled": true,
  "post_salary_prompt_enabled": true,
  "friday_anchor_enabled": true,
  "idle_reminder_enabled": true
}
```

---

## `accounts`

Contas/carteiras. Saldo é **derivado** das transações + `initial_balance`, não armazenado (evita dessincronização).

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK → users | cascade on delete |
| name | varchar(100) | "Nubank", "Dinheiro" |
| type | varchar(30) | enum-like: checking, savings, credit_card, cash, investment |
| initial_balance | numeric(14,2) default 0 | saldo de abertura |
| currency | char(3) default 'BRL' | futuro-proofing |
| color | varchar(7) null | hex |
| icon | varchar(40) null | |
| archived_at | timestamptz null | soft archive |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id)`, `(user_id, archived_at)`.

---

## `categories`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK → users | |
| name | varchar(60) | |
| kind | varchar(10) | 'income' ou 'expense' |
| color | varchar(7) | hex |
| icon | varchar(40) | |
| is_essential | boolean default true | marca "supérfluo" pra regras de consciência |
| monthly_budget | numeric(14,2) null | orçamento opcional por categoria |
| archived_at | timestamptz null | |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id, kind)`, `UNIQUE(user_id, name)`.

**Seed inicial (DefaultCategoriesSeeder)**:

Despesas: Alimentação, Moradia, Transporte, Lazer, Assinaturas, Saúde, Educação, Investimento, Outros
Entradas: Renda Principal, Renda Extra, Outros

---

## `transactions`

Núcleo do sistema.

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK → users | redundante mas acelera queries multi-tenant |
| account_id | bigint FK → accounts | |
| category_id | bigint FK → categories null | null = não categorizada |
| occurred_on | date | data da transação |
| description | varchar(255) | |
| amount | numeric(14,2) | **sempre positivo**; direção vem de `direction` |
| direction | varchar(10) | 'in' ou 'out' |
| notes | text null | |
| tags | jsonb default '[]' | array de strings |
| out_of_scope | boolean default false | derivado ou manual |
| dedup_hash | char(64) | sha256(occurred_on + amount + direction + description + account_id) |
| import_batch_id | bigint FK → import_batches null | null se manual |
| recurring_transaction_id | bigint FK → recurring_transactions null | |
| created_at, updated_at | timestamptz | |
| deleted_at | timestamptz null | soft delete |

**Índices**:
- `(user_id, occurred_on DESC)` — listagem principal
- `(user_id, category_id, occurred_on)` — agregações por categoria
- `(account_id, occurred_on)` — cálculo de saldo
- `UNIQUE(user_id, dedup_hash)` — deduplicação forte
- `GIN (tags)` — busca por tag

**Por que `amount` positivo + `direction`**: evita bug clássico de somar sinal errado. Todas as queries de total ficam explícitas (`WHERE direction = 'out'`).

---

## `recurring_transactions`

Template que gera transações reais todo mês.

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| account_id | bigint FK | |
| category_id | bigint FK null | |
| description | varchar(255) | |
| amount | numeric(14,2) | |
| direction | varchar(10) | |
| day_of_month | smallint | 1-31; se 31 em fev, usa último dia |
| starts_on | date | |
| ends_on | date null | |
| last_generated_on | date null | idempotência |
| active | boolean default true | |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id, active)`.

---

## `goals`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| account_id | bigint FK null | conta vinculada opcional |
| name | varchar(100) | |
| target_amount | numeric(14,2) | |
| current_amount | numeric(14,2) default 0 | snapshot; atualizado manualmente ou via conta |
| target_date | date null | |
| is_emergency_fund | boolean default false | apenas 1 por user (constraint) |
| achieved_at | timestamptz null | |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id, achieved_at)`, `UNIQUE(user_id) WHERE is_emergency_fund = true` (partial unique).

---

## `import_batches`

Histórico de importações.

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| account_id | bigint FK | |
| importer | varchar(40) | 'ofx', 'nubank_csv', 'nubank_card_csv', 'generic_csv' |
| original_filename | varchar(255) | |
| file_hash | char(64) | sha256 do arquivo; evita re-upload idêntico |
| rows_total | int | |
| rows_imported | int | |
| rows_duplicated | int | |
| status | varchar(20) | 'pending', 'preview_ready', 'completed', 'failed', 'reverted' |
| preview_payload | jsonb null | transações parseadas pro user revisar |
| error_message | text null | |
| created_at, updated_at | timestamptz | |

**Índices**: `UNIQUE(user_id, file_hash)` — previne reimport do mesmo arquivo.

---

## `categorization_rules`

A parte que "aprende".

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| match_type | varchar(20) | 'contains', 'starts_with', 'regex', 'exact' |
| pattern | varchar(255) | |
| category_id | bigint FK → categories | |
| priority | smallint default 0 | maior ganha |
| auto_learned | boolean default false | diferencia regra manual de aprendida |
| hits | int default 0 | quantas vezes bateu (desempate) |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id, priority DESC)`.

**Fluxo de aprendizado**: quando usuário categoriza manualmente uma transação sem categoria, sistema oferece "aplicar a todas com descrição parecida?" e cria regra com `auto_learned = true`. Próxima importação já categoriza sozinha.

---

## `insights`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| type | varchar(40) | 'savings_rate_record', 'category_spike', etc. |
| severity | varchar(10) | 'positive', 'info', 'warning' |
| title | varchar(180) | |
| body | text | |
| payload | jsonb | dados estruturados pra UI renderizar detalhes |
| dedup_key | varchar(120) | evita gerar o mesmo insight 2x (ex: 'category_spike:2026-04:alimentacao') |
| read_at | timestamptz null | |
| dismissed_at | timestamptz null | |
| created_at | timestamptz | |

**Índices**: `(user_id, created_at DESC)`, `UNIQUE(user_id, dedup_key)`.

---

## `budget_rules`

Regras de consciência configuradas pelo usuário.

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| kind | varchar(30) | 'category_monthly_cap', 'daily_nonessential_cap' |
| category_id | bigint FK null | quando aplicável |
| amount | numeric(14,2) | |
| active | boolean default true | |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id, active)`.

---

## `wishlist_items`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| name | varchar(180) | |
| target_price | numeric(14,2) | quanto você acha justo pagar |
| current_price | numeric(14,2) null | última busca de preço (null se nunca buscado) |
| reference_url | varchar(500) null | link de referência |
| photo_path | varchar(255) null | |
| priority | smallint default 3 | 1-5 |
| category_id | bigint FK → categories null | |
| quarantine_days | smallint default 30 | |
| status | varchar(20) default 'waiting' | 'waiting', 'ready_to_buy', 'purchased', 'abandoned' |
| purchased_transaction_id | bigint FK → transactions null | |
| abandoned_at | timestamptz null | |
| last_review_prompt_at | timestamptz null | última vez que perguntou "ainda quer?" |
| created_at, updated_at | timestamptz | |

**Índices**: `(user_id, status)`, `(user_id, priority DESC)`.

---

## `price_checks`

Histórico de buscas de preço por item da wishlist.

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| wishlist_item_id | bigint FK → wishlist_items | |
| source | varchar(40) | 'manual', 'serpapi' |
| store_name | varchar(120) | |
| price | numeric(14,2) | |
| url | varchar(500) | |
| found_at | timestamptz | |

**Índices**: `(wishlist_item_id, found_at DESC)`.

---

## `milestones`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| type | varchar(60) | slug do marco: 'net_worth_10k', 'first_emergency_month', etc. |
| tier | varchar(10) | 'small', 'medium', 'large', 'epic' |
| title | varchar(180) | |
| body | text | |
| payload | jsonb | dados específicos do marco |
| dedup_key | varchar(120) | UNIQUE(user_id, dedup_key) |
| achieved_at | timestamptz | quando o marco foi atingido |
| celebrated_at | timestamptz null | quando o user fechou a celebração |
| created_at | timestamptz | |

**Índices**: `(user_id, achieved_at DESC)`, `UNIQUE(user_id, dedup_key)`, `(user_id) WHERE celebrated_at IS NULL`.

---

## `streaks`

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| kind | varchar(30) | 'weekly_logging', 'positive_months' |
| current_count | int default 0 | |
| best_count | int default 0 | recorde pessoal |
| current_started_on | date null | |
| last_extended_on | date null | |
| created_at, updated_at | timestamptz | |

**Índices**: `UNIQUE(user_id, kind)`.

**Comportamento**:
- Streak nunca "quebra" punitivamente; quando deixa de ser estendida, `current_count` zera silenciosamente
- `best_count` preserva recorde mesmo após zerar

---

## `net_worth_snapshots`

Foto mensal do patrimônio. Permite gráfico de evolução sem somar dezenas de milhares de transações em runtime.

| coluna | tipo | notas |
|---|---|---|
| id | bigserial PK | |
| user_id | bigint FK | |
| captured_on | date | último dia do mês |
| total_assets | numeric(14,2) | soma de saldos |
| total_by_account | jsonb | breakdown por conta |
| monthly_income | numeric(14,2) | entradas do mês |
| monthly_expenses | numeric(14,2) | saídas do mês |
| savings_rate | numeric(5,2) | (income-expenses)/income × 100 |
| created_at | timestamptz | |

**Índices**: `UNIQUE(user_id, captured_on)`, `(user_id, captured_on DESC)`.

---

## Tabelas Laravel padrão (não recriar)

- `password_reset_tokens` — Laravel padrão
- `sessions` — quando usar driver session=database
- `cache`, `cache_locks` — Laravel cache
- `jobs`, `job_batches`, `failed_jobs` — fila
- `personal_access_tokens` — Sanctum
- `notifications` — Laravel Notifications (UUID PK, morphable notifiable)

---

## Resumo de relacionamentos

```
users 1──* accounts
users 1──* categories
users 1──* transactions ──* account, ──? category, ──? import_batch, ──? recurring
users 1──* recurring_transactions
users 1──* goals ──? account
users 1──* import_batches
users 1──* categorization_rules ──* category
users 1──* insights
users 1──* budget_rules ──? category
users 1──* wishlist_items ──? category, ──? purchased_transaction
wishlist_items 1──* price_checks
users 1──* milestones
users 1──* streaks (1 por kind)
users 1──* net_worth_snapshots
```

---

## Considerações de migração futura

- **Multi-currency**: campo `currency` em accounts já existe; transactions vão precisar
- **Transferências entre contas**: futura tabela `transfers` (pendente decisão; ver `docs/00-visao-geral.md`)
- **Compartilhamento entre usuários (família/casal)**: futura tabela `account_shares` ou `workspaces`
