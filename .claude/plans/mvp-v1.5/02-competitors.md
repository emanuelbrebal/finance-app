# Plan: Competitor Research — Granazen, Contabilizzy, Lumin Finanças

Extends the existing analysis in `docs/09-concorrencia.md` (which covered Plannerfin and Pierre).

---

## Context from the existing analysis

Already researched:
- **Plannerfin** — budget-focused, BR market, audit pending
- **Pierre** — AI-first, Open Finance official, R$ 39–199/month, Cloudwalk-backed

New competitors to research:

---

## Granazen

**URL**: granazen.com.br  
**First impression**: budget + expense tracking app, BR market

### Research tasks
- [ ] Register or browse landing page — map all features communicated
- [ ] Check pricing model (free? freemium? subscription?)
- [ ] Test onboarding flow (time to first value)
- [ ] Look for: import options, categorization, goals, gamification
- [ ] Search for reviews: App Store / Google Play, Reclame Aqui, Twitter/X
- [ ] Map tone: does it use guilt language? motivational? neutral?
- [ ] Check if they have wishlist or "impulse control" features
- [ ] Identify the 1-2 things they do better than competition

### Fill after research
| Item | Finding |
|---|---|
| Primary focus | ? |
| Import options | ? |
| Gamification | ? |
| Pricing | ? |
| Tone | ? |
| Main differentiator | ? |
| Main weakness | ? |

---

## Contabilizzy

**URL**: contabilizzy.com.br  
**First impression**: may be more business/accounting focused than personal finance

### Research tasks
- [ ] Confirm if this is personal finance or business accounting (if business, lower priority)
- [ ] If personal: map all features on landing page
- [ ] Check if they serve the "dev jr saving money" audience or SMBs
- [ ] Pricing model
- [ ] Identify import format support
- [ ] Check UX: desktop-first or mobile?
- [ ] Reviews and community presence

### Fill after research
| Item | Finding |
|---|---|
| Personal vs business | ? |
| Primary focus | ? |
| Import options | ? |
| Pricing | ? |
| Relevant to our audience? | ? |

---

## Lumin Finanças

**Instagram**: @luminfinancas  
**Distribution**: Google Play (Android app)  
**Visual identity**: dark purple/violet, premium feel

### Known from ad (May 2026)
- Dashboard showing monthly balance "Saldo do Mês" with ACUMULATIVO badge
- Shows "Receitas" and "Despesas" side by side
- "Lumin Insights" section — AI-powered suggestions to save
- **Financial Score** displayed prominently: score 0–1000, currently 920 "Excepcional"
- 4 feature pillars: Controle total, Insights com IA, Segurança garantida, Rápido e intuitivo
- Headline: "Sua vida financeira, no controle."

### Immediate strategic observations
- Financial Score = gamification via scoring (0-1000, credit-score style). **Risk**: this is points-based gamification — potentially the pattern we want to differentiate against
- "Insights com IA" is a direct overlap with our planned Insights engine — need to understand depth
- Android-only (Google Play) = no iOS and no web app → **our web-first approach is a differentiator**
- Patrocinado (paid ads on Instagram) = they have funding → price competition likely

### Research tasks
- [ ] Find their landing page / website (Instagram → link in bio)
- [ ] Check if there's a web version or only mobile
- [ ] Sign up or browse to understand the Financial Score logic (0-1000 — what drives it? spending less? saving more? logging every day?)
- [ ] Is the "Score" actually a dark pattern (variable reward, anxiety-inducing)?
- [ ] Understand "Insights com IA" — is it truly AI or rule-based insights with AI branding?
- [ ] Pricing model — free? subscription?
- [ ] Check import options (OFX? Open Finance?)
- [ ] Screenshots / reviews on Google Play

### Fill after research
| Item | Finding |
|---|---|
| Web version available? | ? |
| Financial Score mechanic | ? |
| Score = dark pattern? | ? |
| Import options | ? |
| AI insights depth | ? |
| Pricing | ? |
| Main weakness | ? |

---

## Comparison table (to fill after all research)

Update `docs/09-concorrencia.md` with this expanded table after research:

```
| Feature                   | Plannerfin | Pierre | Granazen | Contabilizzy | Lumin | finance-app |
|---------------------------|------------|--------|----------|--------------|-------|-------------|
| Importação OFX            | ?          | ❌     | ?        | ?            | ?     | ✅          |
| Open Finance oficial      | ?          | ✅     | ?        | ?            | ?     | ⏳ v2       |
| Wishlist anti-impulso     | ?          | ❌     | ?        | ?            | ?     | ✅          |
| Gamificação saudável      | ?          | ❌     | ?        | ?            | ⚠️    | ✅          |
| Savings boxes (caixinhas) | ?          | ?      | ?        | ?            | ?     | ✅          |
| Categoria investimentos   | ?          | ?      | ?        | ?            | ?     | ✅          |
| Insights automáticos      | ?          | ✅ AI  | ?        | ?            | ✅ AI | ✅ rule-based |
| Categorização que aprende | ?          | ✅ AI  | ?        | ?            | ?     | ✅          |
| Web app                   | ?          | ✅     | ?        | ?            | ❌    | ✅          |
| App nativo iOS            | ?          | ✅     | ?        | ?            | ❌    | ⏳ v3       |
| Tom não-punitivo          | ?          | ?      | ?        | ?            | ?     | ✅          |
| Preço transparente        | ?          | ✅     | ?        | ?            | ?     | ⏳          |
```

---

## What to do with the findings

1. Update `docs/09-concorrencia.md` — fill the table with real data
2. Identify which Lumin features overlap with our roadmap → prioritize differentiation
3. If Lumin's Financial Score is a dark pattern: document it explicitly as what we DON'T do (reinforces our gamification principles)
4. Find the 2-3 features none of them have → these become our marketing hooks
