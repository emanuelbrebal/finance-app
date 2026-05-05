# Plans — mvp-v1.5 Index

All plans for the next development phase. Build order follows dependencies.

---

## Phase structure

| File | Scope | Priority |
|---|---|---|
| [plan.md](plan.md) | Core v1 modules (import, recurring, goals, wishlist, insights, gamification, snapshots, budget, charts, notifications) | 🔴 Must |
| [01-improvements.md](01-improvements.md) | Investment category, savings boxes (caixinhas), color picker | 🔴 Must |
| [04-onboarding.md](04-onboarding.md) | Guided first-access flow using OFX or manual entry | 🔴 Must |
| [05-wishlist-link.md](05-wishlist-link.md) | Wishlist auto-fill from product URL | 🟡 High |
| [06-simplification.md](06-simplification.md) | Usability audit — trim everything not essential | 🟡 High |
| [03-docker-unified.md](03-docker-unified.md) | Single docker-compose for all git worktrees | 🟢 Dev infra |
| [02-competitors.md](02-competitors.md) | Research: Granazen, Contabilizzy, Lumin Finanças + update comparison table | 🟢 Strategy |

---

## Recommended execution order

```
[06-simplification] → defines what NOT to build → informs all modules
[03-docker-unified] → fix dev infra first → unblocks parallel work
[plan.md M0]        → missing migrations → unblocks everything else
[01-improvements]   → investment + caixinhas → affects schema early
[04-onboarding]     → first-use flow → high UX value, low backend complexity
[plan.md M1-M4]     → import, categorization, recurring, goals
[05-wishlist-link]  → depends on wishlist (plan.md M5)
[plan.md M5-M12]    → wishlist, insights, gamification, notifications
[02-competitors]    → research after v1 is live, informs landing page
```
