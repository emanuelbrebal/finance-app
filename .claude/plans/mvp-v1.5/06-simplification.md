# Plan: Simplification — Usability First

## The problem

finance-app has 13 planned modules (plan.md). Built naively, that's 13 menu items, 13 mental models, 13 places where things can go wrong. Most users will use 3 features 90% of the time. The rest will sit there, adding cognitive load.

**Usability is the #1 principle from here forward.** This plan audits every planned module through the question: *"Is this something a user does regularly, or does it just feel important to build?"*

---

## Usage frequency model

Classify every feature by how often a user touches it:

| Frequency | Label | Examples |
|---|---|---|
| Daily / every transaction | **Core** | Log transaction, check balance |
| Weekly | **Regular** | Review week's spending, check savings rate |
| Monthly | **Periodic** | Review charts, set goals progress |
| Occasional | **Optional** | Edit categories, change rules |
| Once | **Setup** | Onboarding, initial import |
| Rarely or never | **Cut** | Anything the user forgets exists |

**Core and Regular features must be perfect. Everything else must not get in the way.**

---

## Feature audit

### ✅ Keep as-is — Core flows
| Feature | Why it stays |
|---|---|
| Dashboard (net worth, month, burn rate) | First thing user opens — make it great |
| Add transaction | The fundamental loop — must be 3 taps max |
| Transaction list + filters | Main way to review spending |
| Accounts | Setup + occasional balance check |
| Categories | Setup + weekly review |

### ✅ Keep, but simplify UX — Regular flows
| Feature | Simplification needed |
|---|---|
| Import OFX/CSV | Step count: upload → confirm → done. No complex preview table by default |
| Insights feed | Max 3 visible at a time, no separate page needed (embed in dashboard) |
| Net worth evolution chart | One chart, not four. Replace MonthlyChart with NetWorthEvolution |
| Goals | Merge emergency fund and general goals into ONE interface |
| Savings boxes (caixinhas) | Optional widget — off by default, user enables per preference |

### ⚠️ Keep, but defer complexity — Periodic/Optional
| Feature | Deferral |
|---|---|
| Budget rules | Phase 2: basic version first (only category monthly cap) |
| Categorization rules | Auto-suggest only; manual rule management hidden under "Avançado" |
| Recurring transactions | Simplified: just name + amount + day_of_month, no end dates in v1 |
| Wishlist checkpoints | 3 checkpoints in v1 (quarantine, emergency fund, still wants) — add 4+5 in v1.5 |

### 🔴 Cut from v1 scope — build only if proven needed
| Feature | Reason for cut |
|---|---|
| Full chart suite (4 charts) | One good chart > four mediocre ones. Start with NetWorthEvolution only |
| DayOfWeekHeatmap | Interesting, not useful for accumulation goal |
| Strategic notifications (all 6 types) | Start with 2: MilestoneAchieved + WeeklyReview. Add others based on user feedback |
| Budget awareness nudge notification | Notification overload risk. Show in dashboard only, no push |
| Price search via SerpAPI | Non-essential for v1.5 — defer to v2 |
| Journey level badge | Implement journey logic (to calculate level) but don't surface the badge until v2 |

---

## Information architecture — simplified nav

Current planned nav (11 items):
```
Dashboard / Transações / Contas / Categorias / Importar / Recorrentes /
Objetivos / Wishlist / Jornada / Orçamento / Configurações
```

**Proposed nav (7 items max):**
```
Dashboard           — home, the default view
Transações          — list + add (merged)
Contas & Caixinhas  — accounts + savings boxes in one section
Importar            — import + history (replaces manual recurring entry)
Wishlist            — item list + checkpoints
Jornada             — milestones timeline + streaks (optional, below the fold)
⚙ Configurações     — categories, rules, recurring, preferences (grouped)
```

**Move to Settings (Configurações):**
- Categories management
- Categorization rules
- Recurring transactions
- Budget rules
- Notification preferences
- Gamification preferences

**Rationale:** These are important to set up once but don't need prime navigation real estate. Users who want them will find them. Users who don't won't feel overwhelmed.

---

## Dashboard simplification

Current dashboard: 5 widgets + 1 chart = 6 cards, lots of numbers.

**Simplified dashboard — 3 sections + 1 action:**

```
[Section 1 — One number, big]
  Net worth: R$ 12.450,00
  ↑ R$ 843 vs last month  (delta, small)

[Section 2 — This month, compact row]
  Entradas: R$ 3.200  |  Saídas: R$ 2.357  |  Taxa: 26%

[Section 3 — One chart]
  Net worth evolution (last 6 months, simple line)

[Section 4 — 3 insights or recent transactions (tabs)]
  Tab "Insights" shows max 3 cards
  Tab "Recentes" shows last 7 transactions

[Floating action button]
  "+ Transação" — always visible, opens transaction modal
```

What disappears from dashboard: burn rate card, runway card, top 5 expenses, monthly bar chart → these move to a "Detalhes do mês" page accessible by tapping the "This month" row.

---

## Transaction form simplification

Current fields: description, amount, direction, category, account, date, notes, tags, out_of_scope.

**Required fields only (5, not 9):**
1. Amount (numeric keypad, big)
2. Direction (toggle: entrada / saída — default saída)
3. Category (searchable list, most-used first)
4. Account (if user has >1 account; hidden if only 1)
5. Date (defaults to today, tap to change)

**Hidden behind "Detalhes":**
- Description (auto-generated from category if not provided: "Alimentação — 03/mai")
- Notes
- Tags
- Out of scope toggle

**Rationale:** 80% of transactions are: R$X, expense, category, today. Let those be 3 taps.

---

## Gamification simplification

Cut or defer:
- Journey level badge in Topbar → defer to v2 (adds complexity, minimal value early)
- StreakIndicator in Sidebar → keep, but only if streak > 3 (don't show "0 semanas")
- `small` milestone tier → replace with just a toast, no modal
- MilestoneCelebration for `medium` → modal only (no confetti in v1)
- Confetti + sound → v1.5 (after user feedback confirms it's wanted)

---

## Implementation rules from this plan

Apply these as code constraints when building:

1. **No module gets a nav item if it's in Settings.** Categorization rules, recurring, budget — they all live under `/configuracoes`
2. **Transaction form opens as a bottom sheet on mobile, not a new page.** Keep context visible.
3. **Dashboard renders in under 200ms.** If it needs a loading skeleton, the API is doing too much.
4. **No empty states with 3 paragraphs.** One line + one action. "Nenhuma transação. Adicionar?"
5. **Each new feature must answer:** *"Would a first-time user on day 3 understand this without reading docs?"* If no, simplify or defer.
