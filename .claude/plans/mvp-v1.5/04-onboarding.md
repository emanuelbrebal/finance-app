# Plan: Onboarding — Guided First Access

## Goal

New user goes from "just registered" to "seeing real numbers on the dashboard" in under 3 minutes. The onboarding is a linear wizard that sets the minimum necessary to make the app useful immediately.

**Core principle**: the fastest path to real value is importing an OFX file. Manual entry is the fallback. The wizard offers both.

---

## User flow

```
Register → Welcome screen (30s) → Create first account → Import OFX OR add 3 transactions manually → Set financial goal (skippable) → Done → Dashboard with real data
```

Total: 4–5 steps, each under 30 seconds.

---

## Steps

### Step 1 — Welcome (informational, 10 seconds)
- Logo + headline: "Vamos configurar o básico em 3 minutos"
- 3 bullets: conta bancária, primeiros lançamentos, sua meta
- Single CTA: "Começar"
- No skip

### Step 2 — Create first account
- Minimal form: Account name (placeholder: "Nubank", "Conta Corrente"), Account type (select: checking/savings/credit_card/cash), Initial balance (optional, defaults to 0)
- Validation inline, no full-page errors
- CTA: "Criar conta"
- No skip (account is required for transactions to work)
- Note: calls the existing `POST /api/accounts` endpoint

### Step 3 — Add first transactions (the key moment)
Two options presented as cards side by side:

**Option A — Import OFX**
- "Já tenho um extrato do banco" (faster, more data)
- Shows `FileDropzone` for .ofx or .csv
- After upload: shows preview count ("47 transações encontradas") + confirm button
- Calls existing import endpoints
- Triggers `RunCategorizationJob` after confirm

**Option B — Add manually**
- "Vou digitar 3 transações" (no file needed)
- Shows compact inline form 3×: amount + category + date
- Quick mode: category as typeahead, date defaults to today
- After 1st entry auto-scrolls to 2nd row

Both options converge on: "Ótimo! X movimentações adicionadas."

CTA: "Ver no dashboard" OR "Continuar"
Skip: "Fazer isso depois" (skips to step 4)

### Step 4 — Set financial goal (skippable)
- Single field: "Qual seu patrimônio-alvo?" (placeholder: R$ 100.000)
- Optional: target date
- Helper text: "Você pode mudar isso quando quiser nas configurações"
- CTA: "Definir meta" or "Pular por agora"
- Calls `PATCH /api/profile` (updates `target_net_worth` + `target_date`)

### Step 5 — Done
- Green checkmark animation
- Summary: "Conta criada, X transações importadas, meta configurada"
- CTA: "Ver meu dashboard"
- Auto-redirect after 2 seconds if user doesn't click

---

## Backend

### Schema change
- [ ] Add `onboarding_completed_at TIMESTAMPTZ NULL` to `users` table (new migration)
- [ ] OR store in `preferences` JSONB as `onboarding_completed_at` — simpler, no migration

### Endpoints — reuse existing
- Create account: `POST /api/accounts` ✅ exists
- Import OFX: `POST /api/imports` + `POST /api/imports/{batch}/confirm` ✅ planned in plan.md M1
- Add transaction: `POST /api/transactions` ✅ exists
- Set goal: `PATCH /api/profile` ✅ exists

### New endpoint
- `POST /api/onboarding/complete` — marks `preferences.onboarding_completed_at = now()` + returns first dashboard data
- No other new endpoints needed

---

## Frontend

### Routing logic
- `AuthGuard` checks: if user is authenticated AND `preferences.onboarding_completed_at` is null → redirect to `/onboarding`
- Onboarding route is only accessible to authenticated users
- After completion: redirect to `/dashboard`

### Components
- [ ] `OnboardingPage` at `/onboarding` — step state machine
- [ ] `OnboardingProgress` — small dot indicator (●●○○) at top, not numbers
- [ ] `Step1Welcome` — simple card with bullets
- [ ] `Step2CreateAccount` — reuses `AccountForm` in simplified mode (fewer fields)
- [ ] `Step3ImportOrManual` — two option cards + conditional rendering
- [ ] `Step3ImportFlow` — `FileDropzone` + preview + confirm (from plan.md M1)
- [ ] `Step3ManualEntry` — 3 compact transaction rows, auto-focus
- [ ] `Step4SetGoal` — two fields + skip link
- [ ] `Step5Done` — completion animation + redirect
- [ ] `src/hooks/useOnboarding.ts` — manages step state, persists to localStorage (in case user refreshes mid-flow)

### UX rules
- Every step has a "fazer depois" link (except step 2 — account is required)
- No full-page loading spinners: inline skeleton or optimistic UI
- Errors show inline under the relevant field, not as toasts
- Progress dots at the top (4 dots, filled as user advances)
- Mobile-first: each step fits a 375px screen without scrolling

---

## What NOT to add to onboarding
- Notification permission request (intrusive on first access — do this on first milestone instead)
- Categories setup (seeded automatically, user can customize later)
- Profile photo, timezone, or preferences
- Tutorial tooltips overlaid on the main app (too complex, breaks trust)
