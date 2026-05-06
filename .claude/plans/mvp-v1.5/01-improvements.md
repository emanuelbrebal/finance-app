# Plan: Investments, Savings Boxes & Color Picker

Three UI/UX improvements that touch schema and daily-use flows.

---

## 1. Investment category kind

### Problem
Currently categories have `kind: 'income' | 'expense'`. When you transfer R$500 from checking to an investment account, you must record it as an expense — which inflates burn rate and deflates savings rate. Investment IS moving money, but it's not consumption.

### Solution
Add `kind: 'investment'` to the categories enum.

**Behavior rules:**
- Investment transactions are excluded from `monthly_expenses` in `SavingsRateCalculator` and `BurnRateCalculator`
- They still affect account balance (it's a real cash movement)
- On the Dashboard, show a separate "invested this month" line alongside income/expenses
- Investment transactions use `direction: 'out'` (money leaves the account) but are tagged as `kind: 'investment'` via their category

**Schema change:**
- `categories.kind`: `VARCHAR(10)` — add `'investment'` as valid value
- No migration needed beyond a CHECK constraint update
- Seed: add default category "Investimentos" with `kind='investment'`, color `#6366f1`, icon `trending-up`

**Backend:**
- [ ] Update `categories` CHECK constraint or ENUM handling
- [ ] Update `DefaultCategoriesService` to seed the Investimentos category
- [ ] Update `SavingsRateCalculator`: exclude `kind='investment'` categories from expenses
- [ ] Update `BurnRateCalculator`: same exclusion
- [ ] Update `DashboardController`: add `invested_this_month` to response
- [ ] Update `StoreCategoryRequest` + `UpdateCategoryRequest` to accept `'investment'`

**Frontend:**
- [ ] Dashboard MonthCard: add `invested_this_month` row (indigo color)
- [ ] Category form: add "Investimento" as option for kind
- [ ] Transaction form: no change needed (category drives behavior)

---

## 2. Savings boxes (caixinhas)

### What it is
Virtual envelopes inside your net worth. "I have R$10k. R$3k is for vacation, R$2k for car repair, R$5k is free." The money stays in the same bank account — boxes are mental allocations tracked by the app.

**Different from goals:** goals are forward-looking targets ("reach R$100k"). Boxes are present-state allocations ("right now, R$3k is reserved for vacation").  
**Different from accounts:** accounts are actual bank accounts with real transactions. Boxes live on top of them.

### Schema — 2 new tables

```sql
savings_boxes
  id              bigserial PK
  user_id         bigint FK → users
  name            varchar(80)
  color           varchar(7)        -- hex
  icon            varchar(40)
  target_amount   numeric(14,2) null  -- optional ceiling
  notes           text null
  created_at, updated_at timestamptz

savings_box_entries
  id              bigserial PK
  savings_box_id  bigint FK → savings_boxes  ON DELETE CASCADE
  user_id         bigint FK → users
  amount          numeric(14,2)     -- positive = deposited, negative = withdrawn
  description     varchar(180) null
  occurred_on     date
  created_at      timestamptz
```

**Balance** = `SUM(amount)` on `savings_box_entries` (can go negative — intentional).

### Backend
- [ ] Migrations for `savings_boxes` and `savings_box_entries`
- [ ] `SavingsBox` model + `SavingsBoxEntry` model + policies
- [ ] `App\Services\SavingsBoxService` — `getBalance(box)`, `deposit(box, amount, description)`, `withdraw(box, amount, description)`
- [ ] `apiResource /api/savings-boxes`
- [ ] `POST /api/savings-boxes/{id}/deposit`
- [ ] `POST /api/savings-boxes/{id}/withdraw`
- [ ] `GET /api/savings-boxes/summary` — total allocated vs total free (net_worth - sum of all box balances)
- [ ] `SavingsBoxResource` — includes `current_balance`, `progress_pct` (if target set)
- [ ] Form Requests: Store/Update/Deposit/Withdraw

### Frontend
- [ ] `SavingsBoxesPage` at `/caixinhas`
- [ ] `BoxCard` — name, color dot, current balance, progress bar if target set, deposit/withdraw buttons
- [ ] Quick deposit/withdraw modal (amount + description)
- [ ] `SavingsBoxWidget` in Dashboard — shows each box as a horizontal bar + "livre: R$X"
- [ ] `src/api/endpoints/savingsBoxes.ts`
- [ ] `src/hooks/queries/useSavingsBoxes.ts`

---

## 3. Color picker in category creator

### Problem
Categories currently use a predefined set of color swatches. Users can't pick exact colors, which makes personalizing the experience tedious.

### Solution
Replace the swatch grid with a real color picker. Use `react-colorful` (2.5KB, no dependencies) for a minimal popover picker.

**Backend:** no change — `categories.color` is already `VARCHAR(7)`.

**Frontend:**
- [ ] `npm install react-colorful` in frontend
- [ ] Create `ColorPickerField` component:
  - Shows current color as a circle button
  - Click opens a popover with `HexColorPicker` + hex input field
  - `onChange` updates the form field value
- [ ] Replace color swatches in `CategoryForm` with `ColorPickerField`
- [ ] Same change in `AccountForm` (accounts also have a `color` field)
- [ ] Update `src/lib/validators/category.ts`: keep existing `hex color` regex validation
