# 10 — Landing Page

Página pública de aquisição. Pré-requisito: estudo de concorrência (`09-concorrencia.md`) completo.

**Objetivo**: converter visitantes em usuários, comunicando com clareza o valor único do finance-app para o público brasileiro que quer acumular capital.

---

## Princípio orientador

A landing page **não é o produto**. É promessa, posicionamento e prova. Toda copy e visual deve ser construída a partir dos diferenciais validados no estudo de concorrência — não a partir de chutes de marketing.

---

## Estrutura da página

### 1. Hero
- Headline (1 frase) capturando a proposta única — vem do parágrafo de posicionamento do `09-concorrencia.md`
- Subheadline expandindo a promessa
- CTA primário: "Comece grátis"
- Visual: screenshot real do dashboard ou animação sutil mostrando o produto

### 2. Problema
A dor do brasileiro que quer guardar mas não sabe pra onde vai o dinheiro. Linguagem específica, não genérica. Evitar clichês de "controle financeiro" — isso é o que todos dizem.

### 3. Solução — 3 pilares
Cards visuais em grid:
- **Consciência** — "saiba pra onde vai cada real, sem culpa"
- **Acúmulo** — "métricas pensadas pra quem quer juntar, não investir"
- **Gamificação saudável** — "celebrações reais, sem ansiedade"

### 4. Features em destaque (5–6 cards)
Pós-concorrência. As features que **só nós temos** ou **fazemos melhor**:
- Wishlist com 5 checkpoints objetivos
- Reserva de emergência como conceito de primeira classe
- Importação OFX/CSV sem credenciais
- Insights automáticos diários
- Marcos de jornada náutica
- Categorização que aprende

### 5. Dashboard preview
Screenshot real do produto OU mockup interativo. Mostra densidade de informação e qualidade visual. Vale mais que 3 parágrafos.

### 6. Prova social
Depoimentos reais (pós-beta) ou placeholders honestos durante a fase inicial. Nunca depoimentos falsos.

### 7. Comparativo
Tabela "finance-app vs alternativas" com diferenciais **honestos** — incluindo onde os concorrentes ganham (se ganham). Honestidade gera confiança.

### 8. Gamificação explicada
Seção dedicada explicando que NÃO é dark pattern. Mostra:
- Sem XP / sem leaderboard / sem streak punitiva
- Opt-out total
- Celebra patrimônio real, não engajamento com app

Essa seção é importante porque "gamificação" em finanças tem fama ruim. Inverter a percepção é diferencial.

### 9. Preço
Seção clara, mesmo que seja "grátis na beta". Sem letras miúdas. Sem trial enganoso.

### 10. FAQ (5–7 perguntas)
Perguntas reais que o público alvo provavelmente terá:
- "É seguro? Vocês acessam minha conta bancária?"
- "Posso exportar meus dados?"
- "É open source?"
- "Funciona com qual banco?"
- "Como vocês ganham dinheiro?"
- "Quem está por trás?"

### 11. CTA final
"Comece a controlar seu dinheiro hoje" + formulário de e-mail. CTA mais direto que o do hero — quem chegou aqui já está convencido.

### 12. Rodapé
Links legais (Termos, Privacidade, LGPD), redes sociais, link pro repositório GitHub (open source = confiança), versão da plataforma.

---

## Stack — decisão a tomar

| Critério | Opção A: React + Vite (mesmo repo) | Opção B: Astro standalone |
|---|---|---|
| SEO | Médio (SPA com SSR opcional) | Excelente (SSG nativo) |
| Performance | Boa | Excelente (HTML estático) |
| Reaproveitamento de componentes | Alto | Médio (precisa portar) |
| Velocidade de entrega | Mais rápido | Mais lento (setup novo) |
| Manutenção | Único repo | Repo separado |

**Regra de decisão**:
- Se SEO orgânico for canal principal de aquisição → **Astro**
- Se aquisição for via mídia paga / redes sociais → **React + Vite** mesmo

Decidir após estudo de concorrência (entender se concorrentes ranqueiam bem no Google em palavras-chave do nicho).

---

## SEO e performance — checklist

- [ ] Meta tags completas: title, description, Open Graph, Twitter Card, canonical
- [ ] Schema.org markup como `SoftwareApplication`
- [ ] Core Web Vitals: LCP < 2.5s, CLS < 0.1, INP < 200ms
- [ ] Sitemap.xml + robots.txt
- [ ] Imagens otimizadas (WebP/AVIF, lazy loading, srcset)
- [ ] Fontes auto-hospedadas (sem flash + sem dependência de Google Fonts)
- [ ] HTTPS obrigatório
- [ ] Acessibilidade (Lighthouse a11y > 95)

---

## Distribuição de CTAs na página

CTA não pode estar só no fim. Distribuir:

1. Hero
2. Após features
3. Após comparativo
4. Após gamificação
5. Antes do FAQ
6. Rodapé fixo no mobile (sticky)

Variar a copy entre eles — não repetir "Comece grátis" 6 vezes. Exemplos:
- "Comece grátis"
- "Quero começar a juntar"
- "Ver demo"
- "Criar minha conta"

---

## Métricas a acompanhar

Configurar desde o lançamento:

- **Taxa de conversão** visitante → cadastro
- **Bounce rate** por seção (onde as pessoas saem)
- **Heatmap** de cliques (Hotjar, Microsoft Clarity, ou alternativa privacy-friendly)
- **Origem de tráfego** (orgânico, direto, redes, paid)
- **Tempo médio na página**

Sem trackers invasivos. Plausible ou Umami no lugar de GA4 — alinhamento com posicionamento de respeito a dados.

---

## Saídas esperadas

- Landing page no ar em domínio próprio (`finance-app.com.br` ou nome final do produto)
- Stack escolhida e justificada (Astro vs React)
- Métricas configuradas
- A/B test do hero pronto (versão A vs versão B do headline)
