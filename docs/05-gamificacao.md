# 05 — Gamificação

## Os 6 princípios não-negociáveis

Gamificação mal feita vira Duolingo te aterrorizando às 23h. Vira ansiedade. Vira o oposto do objetivo.

1. **Motivação intrínseca > extrínseca** — celebrar o **comportamento real** (juntou dinheiro), nunca o engajamento com o app (abriu 7 dias seguidos). Streak de "abriu o app" é tóxico. Streak de "registrou suas transações da semana" é neutro. Marco de "primeiros R$ 5k guardados" é ouro

2. **Sem perda, só ganho** — usuário nunca **perde** progresso, nunca **quebra** streak punitivamente, nunca recebe notificação de culpa. Streak quebrada vira "começou um novo ciclo" (neutro), não "VOCÊ PERDEU". Diferencia gamificação saudável de cassino

3. **Celebração proporcional** — marco pequeno = feedback pequeno. Marco grande = celebração grande. Confete pra qualquer coisa banaliza. Confete pros R$ 10k primeiros emociona

4. **Progresso visível sempre, comparação social NUNCA** — leaderboard com outros usuários é veneno em app financeiro: gera inveja, ansiedade, decisões ruins. Você só compete contra **você mesmo no passado**

5. **Sem manipulação dark pattern** — sem "você está perdendo R$ X por não usar o app", sem FOMO artificial, sem variable rewards estilo slot machine. O app é honesto sobre o que faz

6. **Opt-out total** — cada elemento de gamificação tem switch nas configurações. Pessoa que odeia gamificação usa o app limpo. **Não é detalhe — é fundamental**

---

## Por que SEM sistema de XP/pontos

Tentação clássica: "1000 XP = pode comprar um item da wishlist". O problema:

> O app passa a recompensar comportamento financeiro saudável com **gasto**. O incentivo final vira "junte XP pra poder gastar". Treina o cérebro a ver o ato de comprar como prêmio. Exatamente o oposto do objetivo.

A "pontuação" do app é **o seu patrimônio real**. Mais limpo, mais honesto, mais alinhado.

A wishlist usa **5 checkpoints objetivos** (ver `docs/07-wishlist.md`), não pontos.

---

## Mecânica A: Marcos (Milestones)

Eventos automaticamente detectados que disparam celebração. Não dependem de configuração — sistema descobre sozinho.

### Categorias de marcos

#### Patrimônio
- Primeiros R$ 1k
- R$ 5k
- R$ 10k
- R$ 25k
- R$ 50k
- R$ 75k
- R$ 100k (meta — celebração épica)
- A cada R$ 25k acima de 100k

#### Comportamento
- Primeira transação registrada
- Primeira semana completa de registros
- Primeira importação concluída
- Primeira regra de categorização aprendida
- Primeira meta criada
- Primeira meta batida

#### Saúde financeira
- Reserva de emergência atingida (1 mês, 3 meses, 6 meses)
- Primeiro mês com taxa de poupança >20%
- Primeiro mês com taxa de poupança >30%
- Primeiro mês com taxa de poupança >50%
- Primeiro mês positivo (entrada > saída) depois de mês negativo

#### Resistência (anti-impulso)
- Item da wishlist completou 30 dias sem ser comprado
- Item da wishlist completou 60 dias
- Item abandonado depois de 60 dias (você resistiu!)
- 3 meses consecutivos batendo orçamento de categoria supérflua

#### Consistência
- Registrou transações em todas as semanas do mês
- Fechou um mês completo de dados
- 6 meses consecutivos de dados
- 1 ano completo de dados

### Tiers visuais

| Tier | Quando | UI |
|---|---|---|
| `small` | Marcos comportamentais simples | Toast bonito, 3s |
| `medium` | Marcos de progresso (R$ 1k, 5k) | Card animado, som suave |
| `large` | Marcos significativos (R$ 25k, 50k, 6m reserva) | Modal centralizado com animação |
| `epic` | Marcos transformadores (R$ 100k, meta batida, ano completo) | Tela cheia, confete sério, momento |

### Persistência

**Marcos não somem.** Viram histórico permanente em `/jornada` — uma "linha do tempo" que o usuário rola e vê tudo que conquistou. Poder emocional grande.

`celebrated_at` controla se já foi mostrado ao usuário. Marco existe assim que detectado; vira "uncelebrated" até user fechar a celebração.

### Implementação

```php
// app/Domain/Milestones/Contracts/MilestoneDetector.php
interface MilestoneDetector
{
    /** @return array<MilestoneDTO> */
    public function detect(User $user): array;
}
```

Cada detector verifica todos os tipos de marco da sua categoria e retorna os novos. `dedup_key` garante que o mesmo marco não é criado 2x.

Detectores registrados em `config/milestones.php` e disparados pelo `DetectMilestonesJob` (diário).

---

## Mecânica B: Níveis da jornada

Não é "level up" de RPG. São **fases nomeadas** com identidade visual própria, baseadas em patrimônio.

### Os 7 níveis (metáfora náutica de movimento)

| Nível | Faixa | Significado |
|---|---|---|
| 🚣 **Zarpando** | R$ 0 — R$ 1k | Saiu do porto, está em movimento |
| ⛵ **Em Cabotagem** | R$ 1k — R$ 5k | Navegação costeira, prudente |
| 🧭 **Velejador** | R$ 5k — R$ 15k | Sabe usar o vento a favor |
| 🌬️ **Navegador** | R$ 15k — R$ 35k | Domina a leitura de rotas |
| 🌊 **Capitão de Longo Curso** | R$ 35k — R$ 70k | Certificação técnica real |
| 🗺️ **Mestre da Rota** | R$ 70k — R$ 100k | Autoridade na própria jornada |
| 🏛️ **Patrimônio Soberano** | R$ 100k+ | Soberania financeira pessoal |

### Tom dos nomes

Termos que descrevem **competência adquirida**, não status social. "Capitão de Longo Curso" é certificação náutica real (autoridade técnica sem ostentação). "Patrimônio Soberano" evoca soberania pessoal — você não depende de ninguém — sem virar "sou rico".

**Todos no gerúndio ou em movimento.** "Atracado" foi rejeitado de propósito: passa a ideia errada no início, quem está começando precisa sentir que **já está navegando**, mesmo com pouco.

### Transições

Cada transição entre níveis = marco `epic`. Celebração séria. Aparece no perfil sutilmente.

### Implementação

`config/journey.php` define níveis e thresholds:

```php
return [
    'levels' => [
        'zarpando' => ['min' => 0, 'max' => 1000, 'label' => 'Zarpando', 'icon' => '🚣'],
        'cabotagem' => ['min' => 1000, 'max' => 5000, 'label' => 'Em Cabotagem', 'icon' => '⛵'],
        // ...
    ],
];
```

Cache em `users.journey_level`, recalculado mensalmente junto com snapshot.

---

## Mecânica C: Streaks

Apenas **dois** streaks, ambos honestos:

### `weekly_logging`
Você incluiu ou importou pelo menos uma transação na semana.

### `positive_months`
Entrada > saída no mês fechado.

### Regras (não-negociáveis)

1. Streak quebrada **não notifica negativamente**, só zera silenciosamente
2. Streak tem `best_count` guardado — você vê seu recorde pessoal mesmo após quebrar
3. **Sem notificação às 23h de "vai perder a streak". NUNCA**

### Implementação

`UpdateStreaksJob` roda no fechamento de semana (domingo 23:59) e mês (último dia 23:59).

```php
// pseudo-código
foreach (User::active() as $user) {
    foreach (['weekly_logging', 'positive_months'] as $kind) {
        $extended = $this->checkExtension($user, $kind);
        $streak = Streak::firstOrCreate(['user_id' => $user->id, 'kind' => $kind]);

        if ($extended) {
            $streak->current_count++;
            $streak->last_extended_on = today();
            $streak->best_count = max($streak->best_count, $streak->current_count);
        } else {
            $streak->current_count = 0; // silencioso
        }
        $streak->save();
    }
}
```

---

## Notificações estratégicas

Reforço positivo em **momentos de decisão e fechamento**, não momentos aleatórios.

### Os 6 triggers

| Quando | Notification | Tom |
|---|---|---|
| Domingo 20:00 | `WeeklyReview` | Balanço positivo se semana boa, neutro se ruim ("Bora ver como foi?") |
| Dia 1 do mês 09:00 | `MonthlyOpening` | Resumo do mês anterior + projeção otimista pro novo |
| Detecção de salário | `PostSalaryPrompt` | "Quanto separa pra meta antes de gastar?" — princípio "pague-se primeiro" |
| Sexta 18:00 | `FridayEveningAnchor` | Mostra progresso da meta como âncora positiva, sem proibir nada |
| Marco detectado | `MilestoneAchieved` | Imediato, escalado por tier |
| 3 dias sem registro | (nudge suave) | "Tudo bem por aí? Tem alguns dias sem registro" — nunca culpa |

### O que NÃO fazer

- ❌ Notificação aleatória ou genérica diária
- ❌ Push toda hora
- ❌ "Você está perdendo X por não usar o app"
- ❌ Mensagens de culpa ou urgência artificial

### Detecção de salário

Heurística: transação de entrada com valor > 80% da `estimated_monthly_income` do user, em conta `checking`. Se detectado, dispara `PostSalaryPrompt` com 5min de delay (não no mesmo segundo).

---

## Configurações (preferences)

Toda mecânica tem toggle:

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

Frontend: página `/settings/preferences` com toggles claros e descrição do que cada um faz.

---

## Métricas de saúde do sistema

Indicadores de que a gamificação está funcionando bem (vs criando ansiedade):

- **Bom**: % de marcos celebrados nos primeiros 7 dias > 80%
- **Bom**: usuário com streak alta também tem patrimônio crescendo
- **Ruim**: usuário desliga celebrações no settings
- **Ruim**: usuário com streak alta mas patrimônio estagnado (registrando lixo só pra manter streak — sinal de que streak virou objetivo, não meio)

Adicionar telemetria pra observar essas métricas em v1.5+.
