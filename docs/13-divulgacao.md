# 13 — Divulgação e Aquisição

Como divulgar o finance-app e fazer ele ser visto pelo público certo. Pré-requisito: posicionamento validado em `09-concorrencia.md` + `14-validacao.md`.

**Princípio orientador**: divulgação alinhada com o produto. Não adianta fazer marketing de growth hack agressivo num produto que prega consciência financeira sem culpa. O canal e a mensagem precisam ser coerentes com a alma do produto.

---

## Público-alvo definido

### Persona primária: "O Construtor"
- 22-32 anos
- Renda R$ 3-8k/mês
- Início de carreira (júnior/pleno) ou universitário trabalhando
- Tem alguma reserva (R$ 5-30k) e quer multiplicar
- Frustrado com apps tradicionais (cartão de crédito, banco) que não focam em acumular
- Usa Reddit, Twitter/X, YouTube, Discord
- Gosta de produtividade, finanças e tecnologia
- **NÃO é o público que paga R$ 199/mês de Pierre Premium**

### Persona secundária: "A Pragmática"
- 28-40 anos
- Renda R$ 5-15k/mês
- Profissional estabelecido querendo organizar finanças
- Casal pensando em casa própria, filhos, MEI
- Menos tempo pra aprender ferramentas — quer algo que funcione
- Usa Instagram, LinkedIn, podcasts

---

## Estratégia em 3 fases

### Fase 1: Validação e aquecimento (AGORA — pré-MVP)
**Objetivo**: validar produto + criar lista de espera com pessoas reais.
- Formulário de validação (`14-validacao.md`) divulgado em redes pessoais e grupos
- Construir em público no Twitter/X e LinkedIn — posts semanais com progresso
- Criar repositório público no GitHub com README impecável
- Newsletter de pré-lançamento (Substack ou Buttondown) com atualizações

### Fase 2: Beta fechado (MVP até v1)
**Objetivo**: entregar produto pra primeiros usuários, gerar histórias reais.
- Convidar 50-100 pessoas da lista de espera
- Sessões 1:1 de feedback (15min cada, em vídeo) com primeiros 20
- Documentar transformações reais (R$ X economizados, hábitos formados)
- Iterar rápido baseado em feedback
- Discord ou WhatsApp dos beta testers (canal direto)

### Fase 3: Lançamento público (v1+)
**Objetivo**: aquisição em escala.
- Landing page no ar
- Product Hunt launch
- Posts de lançamento com depoimentos do beta
- Estratégia de canais (próximo bloco)

---

## Canais de aquisição priorizados

### 🏆 Canais primários (foco principal)

#### Conteúdo no Twitter/X
- 1-2 posts/dia sobre construção do produto, lições, números
- Threads sobre finanças pessoais com viés de acúmulo
- Engajar com comunidade brasileira de finanças (#FinTwit BR)
- Custo: tempo. Conversão: alta entre quem te segue

#### Conteúdo no LinkedIn
- 2-3 posts/semana sobre jornada de construir o produto
- Boas pra alcançar profissionais (persona secundária)
- Posts longos de aprendizado, decisões técnicas, dilemas
- Custo: tempo. Conversão: profissional, qualificada

#### YouTube de nicho
- 1 vídeo/mês: "Como decidi as features do meu app", "Análise honesta de Pierre vs minha alternativa"
- Demoras de produção, mas conteúdo evergreen ranqueia no Google
- Vídeo de "construindo em público" gera identificação

### 🎯 Canais secundários (apoio)

#### Reddit BR
- `r/farialimabets`, `r/financaspessoais`, `r/investimentos` (com cuidado — não spam)
- Comentar com valor antes de mencionar produto
- AMA "sou dev e construí app de finanças, perguntem"

#### Comunidades de devs
- Tabnews, dev.to, Hashnode
- Posts técnicos sobre decisões (Laravel, gamificação, importação OFX)
- Atrai persona primária (devs jr) que pode usar o produto

#### Newsletter de finanças BR
- Pitch pra newsletters como Investnews, Suno, Stockpickers
- Difícil entrar mas alta conversão

### 🌱 Canais orgânicos (longo prazo)

#### SEO / Blog
- Posts de busca alta intenção: "como guardar dinheiro 2026", "melhor app de finanças BR open source"
- Posts comparativos honestos (vs Pierre, vs Plannerfin, vs Mobills)
- Demora 6-12 meses pra dar resultado, mas é evergreen

#### GitHub trending
- Repositório bem documentado, com stars do beta tester
- README impecável, screenshots, demo
- Comunidade de devs descobrindo via GitHub explorer

### ❌ Canais que NÃO recomendo

- **Tráfego pago no Google/Meta cedo demais**: caro, e pra produto pré-validado é torrar dinheiro
- **Influencers de finanças famosos**: pacote caro, conversão duvidosa, posicionamento conflita
- **TikTok**: público jovem demais pra app que precisa de conta bancária e disciplina
- **Compra de listas de e-mail**: ético e legalmente problemático

---

## Construindo em público (build in public)

Estratégia central pra fase 1 e 2. **Por quê funciona**:
- Cria narrativa que as pessoas seguem (reality show de produto)
- Demonstra competência técnica organicamente (recrutamento + portfólio)
- Constrói confiança antes do produto existir
- Acessível mesmo sem orçamento de marketing

### O que postar

**Numéricos** (sempre que possível):
- "Hoje passei de 50 inscritos na lista de espera"
- "Decidi não cobrar pelas features X, Y, Z. Aqui está o porquê"
- "Custo mensal de infra do MVP: R$ 145"
- "Primeira venda: R$ 14,90. Mais emocionante que parece"

**Decisões e dilemas**:
- "Por que escolhi importação manual em vez de Open Finance"
- "Por que não vou ter sistema de XP"
- "Por que estou cobrando 5x menos que o concorrente"

**Bastidores**:
- Screenshots de telas em construção
- Vídeos curtos do produto em uso
- Discussões com beta testers (anonimizadas)

### Frequência ideal
- Twitter/X: diário
- LinkedIn: 2-3x por semana
- YouTube: 1x por mês
- Newsletter: 1x por semana ou quinzenal

---

## Métricas de divulgação

Acompanhar desde o início, sem obsessão:

| Métrica | Onde medir | Meta inicial |
|---|---|---|
| Inscritos lista de espera | Form/Newsletter | 100 em 30 dias |
| Seguidores Twitter/X | Twitter | 200 em 60 dias |
| Stars no GitHub | GitHub | 50 em 90 dias |
| NPS dos beta testers | Form pós-uso | > 50 |
| Conversão lista → beta | Email | > 30% |
| Conversão beta → pago (futuro) | Stripe | > 5% |

---

## Calendário de lançamento (template)

### Mês 1-2 (validação)
- Semana 1-2: lançar formulário, postar em redes pessoais
- Semana 3-4: divulgar em grupos de WhatsApp e Discord
- Semana 5-8: análise de respostas, ajuste de produto, build in public

### Mês 3-4 (MVP)
- Construir MVP enquanto posta progresso
- Convidar 20 primeiros beta testers da lista
- Sessões 1:1 de feedback

### Mês 5-6 (v1)
- Expandir beta pra 100 pessoas
- Documentar histórias de uso
- Preparar landing page

### Mês 7+ (lançamento público)
- Product Hunt launch
- Push em todos os canais simultâneos
- Início de cobrança em paralelo

---

## Saídas esperadas

- Lista de espera com 100+ pessoas qualificadas antes do MVP
- Comunidade orgânica de 200+ seguidores nas redes
- Repositório público com 50+ stars
- 20+ depoimentos reais de beta testers
- Landing page convertendo > 5% de visitantes
