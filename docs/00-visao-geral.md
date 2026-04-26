# 00 — Visão Geral

## Propósito

Plataforma de gestão financeira pessoal focada em **acúmulo de capital** (juntar dinheiro) e **consciência de consumo** (entender pra onde o dinheiro está indo, sem culpa).

## Perfil do usuário inicial

- Dev jr brasileiro, estudante
- ~R$ 10.000 guardados
- Meta: chegar aos R$ 100.000
- Vai usar o app pra: (1) controlar finanças de verdade, (2) ter projeto de portfólio, (3) potencialmente transformar em produto vendável

## Os três objetivos simultâneos do projeto

1. **Uso pessoal real** — precisa ser bom o suficiente pra você usar todo dia
2. **Portfólio** — código limpo, decisões justificadas, demonstra capacidade técnica
3. **SaaS futuro** — multi-tenant desde o início, arquitetura plugável, decisões de negócio defensáveis

## Posicionamento

**É**: app de acúmulo de capital + consciência de consumo
**Não é**: app de investimento, cotação de ativos, recomendação de produtos financeiros

Cada feature deve responder: *"isso ajuda a juntar mais dinheiro ou ter mais consciência?"* Se não, fora.

## Módulos do produto

### Core (MVP)
1. **Auth + perfil** com meta de patrimônio, data-alvo, renda mensal estimada
2. **Contas/carteiras** — Nubank, Conta Corrente, Dinheiro, Investimentos
3. **Categorias customizáveis** com seed inicial brasileiro (Alimentação, Moradia, Transporte, etc.)
4. **Transações** — CRUD com data, descrição, valor, tipo, categoria, conta, tags, flag "fora do escopo"
5. **Importação de extratos** — OFX, CSV Nubank (conta + cartão), CSV genérico
6. **Regras de categorização que aprendem** com correções manuais
7. **Dashboard com KPIs** — patrimônio, progresso da meta, taxa de poupança, burn rate, runway, crescimento MoM
8. **Gráficos** — evolução patrimonial, entradas vs saídas, distribuição por categoria, heatmap dia da semana, top 5 gastos

### Diferenciais (v1)
9. **Objetivos múltiplos** com cálculo automático de "quanto/mês pra bater"
10. **Reserva de emergência** como conceito de primeira classe (não só mais um goal)
11. **Wishlist com 5 checkpoints** objetivos de liberação (quarentena, reserva intacta, etc.)
12. **Insights automáticos** (job diário) — categoria que disparou, melhor mês de poupança, projeção de meta, assinaturas detectadas, dinheiro parado
13. **Lembretes de consciência** não-punitivos baseados em regras de orçamento
14. **Notificações estratégicas** — domingo à noite, primeiro do mês, pós-salário, sexta à noite, marcos
15. **Snapshots mensais de patrimônio** (performance + histórico imutável)
16. **Gamificação** — marcos, streaks (semanas registrando + meses positivos), níveis da jornada náutica
17. **Recorrentes** — aluguel, assinaturas, geração automática mensal

## Decisões fundadoras (travadas em discussão)

### 1. Importação de extratos em vez de scraping bancário
**Decisão**: usar OFX e CSV exportados pelo banco. Sem pynubank, sem credenciais armazenadas, sem microserviço Python.

**Por quê**: APIs não-oficiais quebram a qualquer momento. OFX é padrão estável que funciona com qualquer banco brasileiro relevante. Extrato manual é 30 segundos por mês — trade-off vale a pena.

**Implicação**: arquitetura de "Importer" plugável, não "Bank Provider". Permite GenericCsvImporter pra qualquer fonte de dados tabular.

### 2. Sem sistema de XP/pontos abstratos
**Decisão**: gamificação baseada em **marcos honestos** (R$ 1k, 5k, 10k... primeira reserva, primeira meta batida) e **checkpoints objetivos** na wishlist.

**Por quê**: XP genérico treina o cérebro errado. Recompensar comportamento financeiro saudável com "permissão de gastar" cria loop perverso. A "pontuação" do app é o patrimônio real.

### 3. Reforço positivo por design
**Decisão**: 6 princípios não-negociáveis de gamificação saudável (ver `docs/05-gamificacao.md`).

**Por quê**: apps financeiros tradicionais soam como pai bravo, matam adesão. Consciência sem culpa é diferencial real.

### 4. Reserva de emergência como entidade especial
**Decisão**: campo `is_emergency_fund` no `goals`, com cálculo automático de target = 6× burn rate.

**Por quê**: reserva é categorialmente diferente de outros objetivos. Tem fórmula própria, status binário crítico ("você tem ou não tem reserva"), e merece widget dedicado no dashboard.

### 5. Snapshots mensais de patrimônio
**Decisão**: tabela `net_worth_snapshots` capturada por job mensal.

**Por quê**: calcular `SUM(transactions)` em runtime degrada com volume. Snapshot dá performance + histórico imutável (ver patrimônio em março/2025 mesmo se editar transação antiga).

### 6. Wishlist faseada
**Decisão**:
- **MVP**: registro manual + quarentena + 5 checkpoints + cálculo de impacto na meta. Zero scraping.
- **v1**: busca de preços sob demanda via SerpAPI (custo controlado).
- **v2**: monitoramento contínuo de preços (se virar produto).

**Por quê**: scraping de Mercado Livre/Amazon/Shopee é frágil e exige API paga pra ser confiável. Quarentena + impacto na meta já entregam o valor central (anti-impulso).

### 7. Multi-tenant desde o início
**Decisão**: toda tabela tem `user_id` desde a primeira migration.

**Por quê**: custo zero agora, evita refactor enorme se virar SaaS.

### 8. Versionamento de API desde o início
**Decisão**: rotas sob `/api/v1/`, controllers em `App\Http\Controllers\Api\V1\`.

**Por quê**: barato agora, caro depois. Permite breaking changes futuras sem quebrar clientes antigos.

## Tom do produto

- **Não-punitivo sempre.** Sem "VOCÊ ESTOUROU O ORÇAMENTO!" Sim "Notei uma compra em Lazer de R$150. Esse mês você já gastou R$420/R$400 em Lazer. Tudo bem, só pra ter consciência 👀"
- **Autoridade técnica sem vaidade.** Termos como "Capitão de Longo Curso" passam competência adquirida, não status social
- **Honestidade sobre limitações.** Sem prometer otimização mágica, sem garantir resultado
