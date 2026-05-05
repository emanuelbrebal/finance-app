# finance-app

> Plataforma pessoal de gestão financeira focada em **acúmulo de capital** e **consciência de consumo**, com gamificação saudável por design.

Este arquivo é lido automaticamente pelo Claude Code em toda conversa neste repositório. Contém contexto permanente: identidade, stack, princípios e padrões. Para detalhes específicos, consulte `docs/`.

---

## Identidade do projeto

- **Usuário-alvo inicial**: dev jr brasileiro, ~R$10.000 guardados, meta de R$100.000
- **Três objetivos simultâneos**: (1) controle financeiro pessoal real, (2) projeto de portfólio, (3) potencial produto SaaS futuro
- **Foco**: app de **acúmulo** e **consciência**, NÃO de investimento. Resistir ativamente a features de cotação de ativos, recomendação de produtos, etc.
- **Multi-tenant desde o início**: mesmo com 1 usuário hoje, modelar como SaaS-ready

---

## Stack obrigatória

### Backend
- **Laravel 11** (PHP 8.3+)
- **Sanctum** para auth (cookie-based, SPA same-origin)
- **PostgreSQL 16**
- **Redis** para filas e cache
- **Laravel Scheduler** para jobs agendados (insights, snapshots, recorrentes)
- **Laravel Notifications** para lembretes (database, email, web push)
- **asgrim/ofxparser** para importação OFX

### Frontend
- **React 18 + TypeScript + Vite**
- **TailwindCSS** + **shadcn/ui**
- **TanStack Query (React Query)** para estado de servidor
- **React Router** v6+
- **Recharts** para gráficos
- **Zod** para validação client-side (espelhando Form Requests do Laravel)
- **date-fns** com locale pt-BR
- **Zustand** apenas para UI ephemera (modal aberto, sidebar) — sem Redux, sem Context exagerado

### Infra
- **Docker Compose** com serviços separados: laravel, postgres, redis, vite (dev)

---

## Princípios não-negociáveis

### Princípios de produto
1. **Simplicidade > completude** — prefira funcionar bem o essencial a ter tudo meia-boca
2. **Cada feature responde**: "isso ajuda a juntar mais dinheiro ou ter mais consciência de consumo?" Se não, fora
3. **Reforço positivo > cobrança** — tom não-punitivo SEMPRE. Sem linguagem moralizante
4. **Consciência sem culpa** — o app dá dados pra decidir bem, não proíbe nem libera arbitrariamente

### Princípios de gamificação (não-negociáveis)
1. **Motivação intrínseca > extrínseca** — celebrar comportamento financeiro real, nunca engajamento com app
2. **Sem perda, só ganho** — streak nunca "quebra" punitivamente, só zera silenciosamente. Sem FOMO, sem dark patterns
3. **Celebração proporcional** — marco pequeno = feedback pequeno, marco grande = celebração grande
4. **Progresso visível, comparação social NUNCA** — sem leaderboard, sem ranking entre usuários
5. **Sem manipulação** — sem variable rewards estilo cassino, sem urgência artificial
6. **Opt-out total** — cada elemento de gamificação tem switch nas configurações

**SEM sistema de XP/pontos abstratos.** A "pontuação" do app é o patrimônio real. Wishlist tem **5 checkpoints objetivos** de liberação (quarentena, reserva intacta, taxa de poupança positiva, impacto na meta, ainda quer) — não pontos.

### Princípios técnicos
1. **Extensibilidade desde o início**: Importers plugáveis, InsightRules como classes separadas, MilestoneDetectors registráveis
2. **Service Layer explícito**: Controllers magros (validação + chamada + resource). Lógica em `app/Services/`. Regras puras em `app/Domain/`
3. **API Resources sempre**: nunca retornar Eloquent direto
4. **Form Requests sempre**: validação fora do controller
5. **Validação espelhada**: zod no frontend reflete Form Requests do backend (fonte única de verdade)
6. **Soft delete onde faz sentido** (transactions, accounts arquivadas)
7. **`amount` SEMPRE positivo** + campo `direction` ('in'/'out'). Evita bugs de sinal
8. **Valores monetários como string decimal** na API (`"1234.56"`) pra evitar float em JS
9. **Datas em ISO 8601**, timezone padrão `America/Sao_Paulo`
10. **Snapshots mensais de patrimônio** em tabela própria (não calcular via SUM em runtime no dashboard)

### O que NÃO fazer (anti-padrões)
- � Scraping bancário não-oficial (pynubank, etc.) — usar OFX/CSV
- � Sistema de XP/pontos genéricos — usar checkpoints objetivos e marcos honestos
- � Leaderboard ou comparação social
- � Notificações punitivas, FOMO, ou dark patterns
- � Microserviços, event sourcing, DDD tático, Kubernetes — monolito Laravel + SPA basta
- � Múltiplas moedas, cotação de ativos, IA conversacional, importação por email/NFe — fora do escopo
- � Bloquear gastos do usuário — apenas dar consciência

---

## Padrões de código

### Backend (Laravel)
- **Namespaces de domínio**: `App\Domain\Importers`, `App\Domain\InsightRules`, `App\Domain\Milestones`, `App\Domain\Wishlist`, `App\Domain\Calculators`
- **Service Layer**: `App\Services\*Service` orquestra; chama coisas do Domain, persiste, dispara jobs
- **Controllers em `App\Http\Controllers\Api\V1\`** — versionados desde o início
- **Form Requests em `App\Http\Requests\V1\`** — um por endpoint
- **API Resources em `App\Http\Resources\V1\`** — formato consistente: `{data: {...}}` ou `{data: [...], meta: {...}}`
- **Jobs assíncronos** para: importação, geração de insights, detecção de marcos, snapshots, recorrentes, notificações estratégicas
- **Configs separados** registram extensões plugáveis: `config/insights.php`, `config/importers.php`, `config/milestones.php`, `config/journey.php`
- **Tagged services** no container: `app()->tagged('insight_rules')` para iterar regras

### Frontend (React)
- **`src/pages/`** = containers que orquestram hooks + components
- **`src/components/`** = burros, recebem props, renderizam
- **`src/hooks/queries/`** e **`src/hooks/mutations/`** — React Query
- **`src/api/endpoints/`** = wrapper tipado sobre fetch, retorna Promise
- **`src/lib/validators/`** = schemas zod por feature
- **`src/components/gamification/`** isolado (celebrações, confete, badges, timeline)
- **Sem Redux**. Estado servidor = React Query. Estado UI = Zustand mínimo
- **`Money.tsx`** = componente único pra formatação BRL. Nunca formatar inline

### Convenções da API
- Base: `/api/v1`
- Auth: Sanctum cookie. Endpoints públicos explicitamente marcados
- Paginação: `?page=N&per_page=M` (default 25, max 100)
- Filtros: query string
- Erros: padrão Laravel `{message, errors?}`

---

## Mapa da documentação

Quando precisar de detalhe, leia o arquivo correspondente em `docs/`:

| Arquivo | Conteúdo |
|---|---|
| `docs/00-visao-geral.md` | Objetivo, perfil de usuário, decisões fundadoras, módulos |
| `docs/01-arquitetura.md` | Diagrama de serviços, decisões técnicas justificadas |
| `docs/02-schema.md` | Schema completo do PostgreSQL — todas as tabelas, colunas, índices |
| `docs/03-endpoints.md` | API REST completa, agrupada por módulo |
| `docs/04-estrutura-pastas.md` | Estrutura backend Laravel + frontend React |
| `docs/05-gamificacao.md` | Marcos, streaks, níveis da jornada, princípios |
| `docs/06-importacao.md` | OFX, CSV Nubank, regras de categorização que aprendem |
| `docs/07-wishlist.md` | 5 checkpoints, fases (MVP/v1/v2), price tracking |
| `docs/08-roadmap.md` | MVP / v1 / v1.5 / v2 — o que entra em cada fase |
| `docs/08b-roadmap-extras.md` | Estudo de concorrência, Landing Page, App Mobile (v3) |
| `DESIGN.md` (raiz) | Sistema de design inspirado em Linear — paleta, tipografia, componentes, do's & don'ts. Ponto de partida pro frontend. Customizar conforme o projeto evolui (ex.: trocar accent indigo-violet por verde pra reforço positivo de marcos). |

---

## Como trabalhar comigo (Claude Code) neste projeto

1. **Antes de codar nova feature**, sempre leia o doc relevante em `docs/`
2. **Pergunte antes de criar arquivos** se houver ambiguidade
3. **Respeite os princípios** acima — eles foram travados de propósito após várias rodadas de discussão. Se algo parece "bom mas conflita", levante a questão antes de fazer
4. **Code clean, sem comentários óbvios**. Comente só pontos críticos
5. **Testes**: feature tests para endpoints, unit tests para Domain (Importers, InsightRules, Calculators). TDD não obrigatório, mas cobertura de Domain é
6. **Commits atômicos** com mensagens descritivas **em inglês** — portfólio público, inglês é padrão

### Regra de idioma para Pull Requests

**PRs sempre em inglês** — título e corpo, sem exceção.

Este projeto é portfólio para o mercado internacional. Todo Pull Request deve:
- Ter **título em inglês** seguindo o padrão `type: short description`
- Ter **corpo em inglês**: Summary, What changed, How to test
- Usar termos técnicos corretos (não traduzir "endpoint", "middleware", "hook", etc.)

O que **não muda**: UI do app continua em português (produto para usuários BR), variáveis e comentários no código continuam flexíveis.

### Regra de tamanho dos arquivos de documentação

**Máximo de 200 linhas por arquivo `docs/*.md`.**

- Se ao editar um arquivo de docs ele ultrapassar 200 linhas, crie um novo arquivo para o conteúdo excedente (ex.: `docs/08b-roadmap-mobile.md`, `docs/09-concorrencia.md`)
- Atualize o **Mapa da documentação** neste CLAUDE.md para incluir o novo arquivo
- Anuncie ao usuário qual arquivo foi criado e por quê

---

## Status atual

- ✅ MVP completo (Fatias 3a–3o): Auth + Accounts + Categories + Transactions + Dashboard + Profile
- � Próximo passo: **v1** — Importação OFX/CSV, Wishlist, Gamificação, Insights, Notificações
