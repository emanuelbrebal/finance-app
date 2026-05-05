---
target: .claude/plans/mvp-v1.5/plan.md
type: deprecation
date: 2026-05-03
---

# Apply simplification cuts to v1.5 plan

## Motivação

Plano `06-simplification.md` define usabilidade como princípio #1 e identifica módulos que inflam o escopo sem servir o loop diário do usuário. Esta entrada formaliza os cortes/adiamentos aplicados a `plan.md` sem editá-lo (regra de imutabilidade).

## Conteúdo

### Cortes do escopo v1 — implementar apenas se demanda real surgir

| Item original em plan.md | Decisão | Razão |
|---|---|---|
| `M11` Full chart suite (4 gráficos: NetWorthEvolution + IncomeVsExpenses + CategoryDistribution + DayOfWeekHeatmap) | **Cortar 3, manter só NetWorthEvolution** | 1 gráfico bom > 4 medianos. Heatmap não serve ao objetivo de acúmulo |
| `M10` 6 tipos de notificação (Weekly, Monthly, PostSalary, FridayAnchor, MilestoneAchieved, IdleNudge) | **Iniciar com 2: MilestoneAchieved + WeeklyReview** | Risco de notification overload. Adicionar outras com base em feedback |
| `M9` BudgetAwarenessNudge notification | **Mostrar só no dashboard, sem push** | Notificação de "estourou" toca em tom punitivo |
| `M5` SerpAPI price search | **Adiar para v2** | Não-essencial; flow do `05-wishlist-link` já cobre cadastro inicial |
| `M7` Journey level badge no Topbar | **Implementar lógica, NÃO surfar a UI** | Complexidade extra sem valor early. Surfar em v2 |
| `M7` Confetti + sound em milestone celebration | **Adiar para v1.5 pós-feedback** | Risco de cringe. Toast simples primeiro |
| `M7` Tier `small` milestone com modal | **Substituir por toast** | Modal pra marco pequeno é desproporcional |

### Adiamentos / simplificações por módulo

| Módulo | Mudança |
|---|---|
| `M2` Categorization rules | Auto-suggest após categorização manual; CRUD avançado escondido em "Configurações" |
| `M3` Recurring transactions | v1: só nome + amount + day_of_month. Sem `ends_on`, sem complexidade |
| `M4` Goals | Merge "emergency fund" e goals genéricos em **uma interface** |
| `M5` Wishlist | v1: 3 checkpoints (quarentena, reserva, ainda quer). Adicionar 4+5 em v1.5 |
| `M9` Budget rules | v1: só `category_monthly_cap`. Sem `daily_nonessential_cap` no início |

### Reorganização de navegação

Nav de 11 itens → **7 itens** (informa decisões frontend dos próximos módulos):

```
Dashboard
Transações
Contas (& Caixinhas quando M01-improvements rolar)
Importar (M1)
Wishlist (M5)
Jornada (M7 — opcional, condicional a engajamento)
⚙ Configurações  ← agrupa: Categorias, Regras, Recorrentes, Orçamento, Preferências
```

**Já aplicado nesta sessão**:
- `frontend/src/components/layout/Sidebar.tsx` reorganizado com grupo "configurações" recolhendo Categorias

### Refactor do TransactionForm — aplicado nesta sessão

Princípio: 80% das transações são valor + categoria + hoje. Aplicado:

- Amount em destaque (h-14, text-3xl, autofocus em criação)
- Direction como toggle visual (botões segmentados verde/vermelho) em vez de `<select>`
- Account picker oculto quando user tem 1 só conta
- Description e Notes ocultos em "+ detalhes" colapsável
- Description vazio = autogerado a partir de `categoria — DD/mês` no submit (frontend-only; backend ainda exige description)

**Pendência implícita**: tornar `description` opcional no `StoreTransactionRequest`/`UpdateTransactionRequest` e gerar fallback server-side. Hoje o frontend supre antes de enviar — funcional, mas fragiliza importação OFX (M1) que pode chegar sem description. Resolver junto com M1.

## Impacto

- **plan.md** continua válido como referência completa do escopo total v1.
- **Esta entrada** sobrepõe `plan.md` para decisões de execução: na hora de codar M7, M9, M10, M11, consultar este arquivo para o que entra ou não.
- **Sidebar.tsx e TransactionForm.tsx** já refletem o novo padrão e devem ser usados como referência de estilo para os próximos formulários e listas.

## Patch sugerido

Nenhum — o `plan.md` permanece imutável. As mudanças concretas estão no código (`Sidebar.tsx`, `TransactionForm.tsx`) e nesta documentação.
