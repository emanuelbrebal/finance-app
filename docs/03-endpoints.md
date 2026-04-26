# 03 — Endpoints da API

## Convenções

- **Base URL**: `/api/v1`
- **Auth**: Sanctum cookie-based (SPA same-origin). Todos os endpoints exigem autenticação **exceto** marcados com 🌐
- **Padrão de resposta**:
  - Lista paginada: `{ data: [...], meta: { current_page, last_page, total } }`
  - Recurso único: `{ data: {...} }`
  - Erro: `{ message, errors? }` (padrão Laravel)
- **Datas**: ISO 8601 (`2026-04-25T14:30:00-03:00`)
- **Valores monetários**: string decimal (`"1234.56"`)
- **Filtros**: query string
- **Paginação**: `?page=N&per_page=M` (default 25, max 100)

---

## 🔐 Auth

| Método | Path | Descrição |
|---|---|---|
| 🌐 GET | `/sanctum/csrf-cookie` | obtém CSRF cookie antes do login (Laravel padrão) |
| 🌐 POST | `/auth/register` | cria conta. payload: `{name, email, password, password_confirmation}` |
| 🌐 POST | `/auth/login` | autentica. payload: `{email, password}`. Retorna user + cria sessão |
| POST | `/auth/logout` | encerra sessão |
| GET | `/auth/me` | retorna user logado |

---

## 👤 Perfil

| Método | Path | Descrição |
|---|---|---|
| GET | `/profile` | dados do usuário + meta + renda |
| PATCH | `/profile` | atualiza nome, meta, data-alvo, renda, timezone |
| PATCH | `/profile/password` | troca senha (exige `current_password`) |
| GET | `/profile/preferences` | preferências de gamificação/notificação |
| PATCH | `/profile/preferences` | toggles: `celebrations_enabled`, `streaks_enabled`, `notifications_enabled`, etc. |

---

## 💳 Contas

| Método | Path | Descrição |
|---|---|---|
| GET | `/accounts` | lista todas as contas do user (com saldo calculado) |
| POST | `/accounts` | cria. payload: `{name, type, initial_balance, color, icon}` |
| GET | `/accounts/{id}` | detalhe + saldo atual + últimas 10 transações |
| PATCH | `/accounts/{id}` | atualiza |
| DELETE | `/accounts/{id}` | arquiva (soft). Retorna 409 se tem transações; força com `?force=true` |
| GET | `/accounts/{id}/balance` | saldo atual + histórico mensal últimos 12 meses |

---

## 🏷️ Categorias

| Método | Path | Descrição |
|---|---|---|
| GET | `/categories` | lista. Filtros: `?kind=income\|expense`, `?archived=true` |
| POST | `/categories` | cria |
| PATCH | `/categories/{id}` | atualiza |
| DELETE | `/categories/{id}` | arquiva |
| POST | `/categories/seed` | dispara seed de categorias padrão BR (idempotente) |

---

## 💸 Transações

| Método | Path | Descrição |
|---|---|---|
| GET | `/transactions` | lista paginada. Filtros: `?from=&to=&account_id=&category_id=&direction=&search=&tag=&out_of_scope=` |
| POST | `/transactions` | cria. payload completo |
| GET | `/transactions/{id}` | detalhe |
| PATCH | `/transactions/{id}` | atualiza |
| DELETE | `/transactions/{id}` | soft delete |
| POST | `/transactions/bulk-categorize` | atualiza categoria de várias. payload: `{ids: [...], category_id, create_rule: bool}` |
| GET | `/transactions/summary` | agregados. Query: `?from=&to=&group_by=category\|account\|month\|day_of_week` |

---

## 🔁 Recorrentes

| Método | Path | Descrição |
|---|---|---|
| GET | `/recurring-transactions` | lista |
| POST | `/recurring-transactions` | cria template |
| PATCH | `/recurring-transactions/{id}` | atualiza (não afeta transações já geradas) |
| DELETE | `/recurring-transactions/{id}` | desativa (não deleta histórico) |
| POST | `/recurring-transactions/{id}/generate-now` | gera manualmente a do mês corrente |

---

## 📥 Importação

| Método | Path | Descrição |
|---|---|---|
| POST | `/imports` | upload de arquivo. Multipart: `file` + `account_id` + `importer` (auto se omitido). Retorna `import_batch_id` em status `pending` + `preview_url` |
| GET | `/imports/{id}/preview` | retorna transações parseadas com flag de duplicada e categoria sugerida (via regras) |
| POST | `/imports/{id}/confirm` | confirma importação. payload opcional: `{overrides: [{row_index, category_id}]}` |
| POST | `/imports/{id}/revert` | desfaz importação completa. soft-deleta todas as transações criadas no batch |
| GET | `/imports` | histórico paginado |
| GET | `/imports/{id}` | detalhe do batch |

---

## 🧠 Regras de categorização

| Método | Path | Descrição |
|---|---|---|
| GET | `/categorization-rules` | lista. Filtros: `?auto_learned=true\|false` |
| POST | `/categorization-rules` | cria manual |
| PATCH | `/categorization-rules/{id}` | atualiza |
| DELETE | `/categorization-rules/{id}` | remove |
| POST | `/categorization-rules/{id}/apply-to-existing` | aplica retroativamente em transações sem categoria que casam o padrão. Retorna `{matched_count}` |

---

## 🎯 Objetivos

| Método | Path | Descrição |
|---|---|---|
| GET | `/goals` | lista todos. Inclui `is_emergency_fund` flag |
| POST | `/goals` | cria. payload: `{name, target_amount, target_date, account_id?, is_emergency_fund?}` |
| GET | `/goals/{id}` | detalhe + cálculo de "quanto/mês pra bater" |
| PATCH | `/goals/{id}` | atualiza |
| DELETE | `/goals/{id}` | remove |
| POST | `/goals/{id}/deposit` | registra aporte manual no objetivo (incrementa `current_amount`) |
| GET | `/goals/emergency-fund` | atalho: retorna o objetivo flagged como reserva, com cobertura em meses calculada vs burn rate atual |
| POST | `/goals/emergency-fund/auto-target` | recalcula `target_amount` da reserva como 6× burn rate dos últimos 6 meses |

---

## 🛒 Wishlist

| Método | Path | Descrição |
|---|---|---|
| GET | `/wishlist` | lista. Filtros: `?status=waiting\|ready_to_buy\|purchased\|abandoned&priority=` |
| POST | `/wishlist` | cria item. payload: `{name, target_price, reference_url?, photo?, priority, quarantine_days, category_id?}` |
| GET | `/wishlist/{id}` | detalhe + status dos 5 checkpoints (cada um com `passed: bool, reason: string`) |
| PATCH | `/wishlist/{id}` | atualiza |
| DELETE | `/wishlist/{id}` | remove |
| POST | `/wishlist/{id}/extend-quarantine` | adiciona +30 dias (resposta ao "ainda quer?") |
| POST | `/wishlist/{id}/abandon` | marca como abandonado. **Dispara marco de resistência** se item estava há >60 dias |
| POST | `/wishlist/{id}/purchase` | marca como comprado, vincula a uma transação. payload: `{transaction_id}` ou `{create_transaction: {...}}` |
| POST | `/wishlist/{id}/check-prices` | MVP: retorna 501 com mensagem amigável. v1: dispara busca via SerpAPI |
| GET | `/wishlist/summary` | total em wishlist, equivalente em meses de poupança, mais antigo, etc. |

---

## 📋 Budget rules

| Método | Path | Descrição |
|---|---|---|
| GET | `/budget-rules` | lista |
| POST | `/budget-rules` | cria. payload: `{kind, category_id?, amount}` |
| PATCH | `/budget-rules/{id}` | atualiza |
| DELETE | `/budget-rules/{id}` | remove |
| GET | `/budget-rules/status` | retorna estado atual de cada regra: usado / limite / % consumido |

---

## 📊 Dashboard

Endpoint único agregado pra evitar N requests no load da home.

| Método | Path | Descrição |
|---|---|---|
| GET | `/dashboard` | retorna tudo: net worth, goal progress, savings rate (mês), burn rate (3m), runway, MoM growth, top 5 gastos, distribuição por categoria, últimas 5 transações, marcos não-celebrados, insights não-lidos, nível atual da jornada, próximo marco previsível, streaks atuais |

**Response shape**:
```json
{
  "data": {
    "net_worth": "12450.00",
    "net_worth_by_account": [{"account_id": 1, "balance": "8200.00"}, ...],
    "goal": {
      "target": "100000.00",
      "current": "12450.00",
      "progress_pct": 12.45,
      "projected_completion_date": "2028-03-15"
    },
    "savings_rate_month": 32.4,
    "burn_rate_3m": "2150.00",
    "runway_months": 5.8,
    "mom_growth_amount": "850.00",
    "mom_growth_pct": 7.3,
    "top_expenses": [...],
    "category_distribution": [...],
    "recent_transactions": [...],
    "uncelebrated_milestones": [...],
    "unread_insights_count": 3,
    "journey": {
      "current_level": "velejador",
      "current_level_label": "Velejador",
      "next_level": "navegador",
      "next_level_threshold": "15000.00",
      "remaining_to_next": "2550.00"
    },
    "streaks": [
      {"kind": "weekly_logging", "current": 8, "best": 12},
      {"kind": "positive_months", "current": 3, "best": 3}
    ]
  }
}
```

---

## 📈 Charts

| Método | Path | Descrição |
|---|---|---|
| GET | `/charts/net-worth-evolution` | últimos 12 meses + projeção até `target_date`. Lê de `net_worth_snapshots` |
| GET | `/charts/income-vs-expenses` | barras agrupadas por mês. Query: `?months=12` |
| GET | `/charts/category-distribution` | donut/barra. Query: `?period=current_month\|last_month\|last_3m` |
| GET | `/charts/day-of-week-heatmap` | gastos por dia da semana. Query: `?months=3` |

---

## 💡 Insights

| Método | Path | Descrição |
|---|---|---|
| GET | `/insights` | lista paginada. Filtros: `?unread=true&severity=` |
| POST | `/insights/{id}/mark-read` | marca como lido |
| POST | `/insights/{id}/dismiss` | dispensa |
| POST | `/insights/run-now` | dispara job de geração manualmente |

---

## 🏆 Gamificação

| Método | Path | Descrição |
|---|---|---|
| GET | `/journey` | nível atual + próximo + progresso + histórico de transições |
| GET | `/milestones` | timeline paginada de todos os marcos |
| GET | `/milestones/uncelebrated` | marcos pendentes de celebração (UI mostra modal) |
| POST | `/milestones/{id}/celebrate` | marca como celebrado |
| GET | `/streaks` | estado atual dos 2 streaks (current + best) |

---

## 🔔 Notificações

| Método | Path | Descrição |
|---|---|---|
| GET | `/notifications` | lista (Laravel padrão, tabela `notifications`) |
| POST | `/notifications/{id}/mark-read` | marca como lida |
| POST | `/notifications/mark-all-read` | marca todas |
| POST | `/notifications/push-subscription` | registra subscription do service worker. payload: `{endpoint, keys: {p256dh, auth}}` |
| DELETE | `/notifications/push-subscription` | remove subscription |

---

## Códigos de status

| Código | Quando |
|---|---|
| 200 | OK (GET, PATCH, POST com retorno) |
| 201 | Created (POST que cria recurso) |
| 204 | No Content (DELETE) |
| 401 | Não autenticado |
| 403 | Autenticado mas sem permissão |
| 404 | Recurso não encontrado |
| 409 | Conflito (ex: arquivar conta com transações) |
| 422 | Validação falhou (com `errors` objeto) |
| 501 | Não implementado (ex: check-prices no MVP) |

---

## Rate limiting

Padrão Laravel via `throttle:api` middleware:
- 60 requisições/minuto por usuário autenticado
- 30 requisições/minuto por IP em endpoints 🌐 (login, register)
