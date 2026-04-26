# 07 — Wishlist

## Filosofia

Lista de desejos com **anti-impulso por design**. Antes de comprar, registra o desejo, espera, compara, e só compra quando vale a pena.

Substitui o módulo "consumo consciente" do prompt original e responde a pergunta "como sei se posso comprar isso?" sem cair em sistema de XP que recompensaria gastar.

---

## Decisão fundadora: SEM XP, COM checkpoints objetivos

A pergunta original era: "100 de XP me permite escolher um item da wishlist pra comprar?"

**Por que isso é ruim**: o app passa a recompensar comportamento financeiro saudável com **gasto**. O incentivo final vira "junte XP pra poder gastar". Treina o cérebro errado.

**A solução**: a "permissão" pra comprar vem de **realidade financeira**, não de pontuação abstrata. Quando os 5 checkpoints estão verdes, o item entra em `ready_to_buy` com mensagem do tipo:

> *"Você esperou 47 dias, sua reserva está sólida, e essa compra mantém você no rumo da meta. Vai com tranquilidade."*

Não é proibição, é clareza. Você decide; o app te dá os dados.

---

## Os 5 checkpoints

Cada item da wishlist tem 5 condições objetivas. Quando todas estão verdes, status muda automaticamente de `waiting` → `ready_to_buy`.

### Checkpoint 1: Quarentena cumprida

```
✅ Item está na wishlist há ≥ {quarantine_days}
```

`quarantine_days` é definido pelo usuário ao cadastrar (default 30, opções comuns 30/60/90).

Implementação: `created_at + quarantine_days <= now()`.

### Checkpoint 2: Reserva de emergência intacta

```
✅ Comprar este item NÃO derruba a reserva abaixo do threshold
```

Threshold padrão: 6 meses de burn rate. Configurável.

Implementação: `(emergency_fund.current_amount - target_price) >= (burn_rate × min_months)`.

Se user não tem reserva configurada como `is_emergency_fund`, esse checkpoint passa automaticamente (com aviso "configure sua reserva pra ativar essa proteção").

### Checkpoint 3: Taxa de poupança do mês positiva

```
✅ No mês corrente, entradas > saídas
```

Implementação: `monthly_income(currentMonth) > monthly_expenses(currentMonth)`.

Lógica: se você está num mês ruim (gastando mais do que ganha), não é hora de gastar mais ainda. Espere virar o mês ou recuperar.

### Checkpoint 4: Impacto na meta aceitável

```
✅ A compra não atrasa a meta principal mais que X dias
```

X default = 30 dias. Configurável.

Implementação:
1. Calcula projeção atual de quando atinge `target_net_worth`
2. Recalcula projeção subtraindo `target_price` do patrimônio atual
3. Diferença em dias deve ser ≤ X

Se user não tem meta principal definida, checkpoint passa automaticamente.

### Checkpoint 5: Você ainda quer

```
✅ Resposta positiva ao "ainda quer?" no fim da quarentena
```

Quando quarentena vence, sistema pergunta via notificação:
- **Sim, ainda quero** → checkpoint passa
- **Não, abandonar** → status='abandoned', dispara marco de resistência se >60 dias
- **Mais 30 dias** → estende quarentena (volta ao checkpoint 1)

Se usuário não respondeu, checkpoint fica pendente (não passa nem falha).

---

## Estados do item

```
[criado] ──→ waiting ──→ ready_to_buy ──→ purchased
                │              │
                ↓              ↓
            abandoned      abandoned
```

- `waiting` — aguardando 1+ checkpoints
- `ready_to_buy` — todos checkpoints verdes, pode comprar com tranquilidade
- `purchased` — comprado, vinculado a uma transação
- `abandoned` — desistiu do desejo

---

## Resposta do GET `/wishlist/{id}`

```json
{
  "data": {
    "id": 42,
    "name": "Relógio Casio modelo X",
    "target_price": "350.00",
    "current_price": null,
    "reference_url": "https://...",
    "photo_path": "wishlist/42.jpg",
    "priority": 4,
    "category": {"id": 5, "name": "Lazer"},
    "quarantine_days": 30,
    "status": "waiting",
    "days_in_wishlist": 18,
    "checkpoints": [
      {
        "id": "quarantine",
        "label": "Quarentena cumprida",
        "passed": false,
        "reason": "Faltam 12 dias de quarentena",
        "progress_pct": 60.0
      },
      {
        "id": "emergency_fund",
        "label": "Reserva de emergência intacta",
        "passed": true,
        "reason": "Sua reserva continua cobrindo 7.2 meses após a compra",
        "progress_pct": 100
      },
      {
        "id": "positive_savings_rate",
        "label": "Mês com poupança positiva",
        "passed": true,
        "reason": "Esse mês: +R$ 850 de saldo",
        "progress_pct": 100
      },
      {
        "id": "goal_impact",
        "label": "Impacto aceitável na meta",
        "passed": true,
        "reason": "Comprar atrasa sua meta em 8 dias (limite: 30 dias)",
        "progress_pct": 100
      },
      {
        "id": "still_wanted",
        "label": "Confirmação de desejo",
        "passed": null,
        "reason": "Será perguntado ao fim da quarentena",
        "progress_pct": 0
      }
    ],
    "created_at": "2026-04-08T..."
  }
}
```

---

## Implementação

### `CheckpointInterface`

```php
namespace App\Domain\Wishlist\Checkpoints\Contracts;

interface CheckpointInterface
{
    public function id(): string;
    public function label(): string;
    public function evaluate(WishlistItem $item, User $user): CheckpointResult;
}

class CheckpointResult
{
    public function __construct(
        public readonly ?bool $passed,         // null = pendente
        public readonly string $reason,
        public readonly float $progressPct,    // 0-100
    ) {}
}
```

### `CheckpointEvaluator`

```php
namespace App\Domain\Wishlist;

class CheckpointEvaluator
{
    /** @param array<CheckpointInterface> $checkpoints */
    public function __construct(private array $checkpoints) {}

    public function evaluate(WishlistItem $item, User $user): array
    {
        return array_map(
            fn($checkpoint) => [
                'id' => $checkpoint->id(),
                'label' => $checkpoint->label(),
                'result' => $checkpoint->evaluate($item, $user),
            ],
            $this->checkpoints
        );
    }

    public function allPassed(WishlistItem $item, User $user): bool
    {
        foreach ($this->checkpoints as $checkpoint) {
            $result = $checkpoint->evaluate($item, $user);
            if ($result->passed !== true) return false;
        }
        return true;
    }
}
```

### Atualização de status

Job diário verifica todos os itens em `waiting` e promove pra `ready_to_buy` se todos checkpoints passaram. Notification suave: *"O 'Relógio Casio modelo X' está liberado pra compra. Tudo nos conformes."*

---

## Marcos de resistência

Disparados pela `ResistanceDetector` (no `DetectMilestonesJob`):

- Item completou 30 dias sem ser comprado → marco `small`
- Item completou 60 dias → marco `medium`
- Item abandonado depois de 60 dias → marco `large` ("Você resistiu! Deixou de gastar R$ X em algo que não era essencial.")

---

## Fases (faseamento estratégico)

### MVP
- CRUD completo de items
- 5 checkpoints funcionais
- Quarentena com prompt "ainda quer?" via notification
- Marcos de resistência
- Endpoint `/wishlist/summary` com totais
- `/wishlist/{id}/check-prices` retorna 501 com mensagem amigável

### v1
- Integração SerpAPI (Google Shopping) sob demanda
- Endpoint `/wishlist/{id}/check-prices` retorna 3-5 resultados (preço, loja, link)
- User salva manualmente o melhor resultado como `current_price`
- Custo controlado: usuário dispara, não roda em background

### v2
- Job diário reconsulta preços de items ativos
- Histórico de preço (gráfico de variação) usando `price_checks`
- Alerta quando preço cai abaixo de `target_price`
- Considera diferencial de produto se virar SaaS

---

## Endpoints relevantes

(referência rápida — completo em `docs/03-endpoints.md`)

- `GET /wishlist` — lista
- `POST /wishlist` — cria item
- `GET /wishlist/{id}` — detalhe + checkpoints
- `POST /wishlist/{id}/extend-quarantine` — +30 dias
- `POST /wishlist/{id}/abandon` — abandona (pode disparar marco)
- `POST /wishlist/{id}/purchase` — registra compra (vincula a transação)
- `POST /wishlist/{id}/check-prices` — busca de preço (501 no MVP)
- `GET /wishlist/summary` — agregados

---

## Componentes frontend

- `WishlistPage` — lista com filtros por status e priority
- `WishlistItemCard` — card resumido com badge `ready_to_buy` se aplicável
- `WishlistItemPage` — detalhe + `CheckpointsPanel` visual com 5 indicadores
- `CheckpointsPanel` — cada checkpoint como card com cor (verde passou, cinza pendente, vermelho falhou) e `reason` legível
- `ReadyToBuyBadge` — selo destacado em items liberados
- `QuarantinePrompt` — modal disparado por notification "ainda quer?"
