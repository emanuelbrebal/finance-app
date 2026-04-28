# 08b — Roadmap: Concorrência, Landing Page e App Mobile

Planos de posicionamento de mercado e expansão de plataforma. Pré-requisito: v1 completa.

---

## Estudo de Concorrência — antes de lançar publicamente

**Objetivo**: mapear o campo de batalha com precisão, encontrar brechas reais e construir diferenciais competitivos sólidos antes de comunicar o produto ao mercado.

### O que fazer

- [ ] **Auditar plannerfin.com.br** — funcionalidades, UX, preço, proposta de valor, tom de voz, onboarding, limitações percebidas (reviews, redes sociais, App Store)
- [ ] **Auditar pierre.com.br** — idem. Pierre foca em orçamento + IA; mapear o quanto de IA é real vs marketing
- [ ] **Comparativo feature-by-feature** — tabela: feature / plannerfin / pierre / finance-app (✅ tem / ⏳ roadmap / ❌ fora do escopo)
- [ ] **Lacunas identificadas** — o que nenhum dos dois faz bem que você pode fazer melhor
- [ ] **Diferenciais competitivos** — lista priorizada de argumentos de venda exclusivos do finance-app
- [ ] **Posicionamento final** — 1 parágrafo de posicionamento de mercado (para usar em landing page, pitch, bio)

### Eixos de análise

| Eixo | Perguntas-chave |
|---|---|
| Foco de produto | Orçamento? Patrimônio? Investimentos? Hábitos? |
| Gamificação | Tem? É saudável ou dark pattern? |
| Importação | OFX, CSV, Open Finance? Manual? |
| Ética de dados | LGPD claro? Self-hosted? Exportação? |
| Preço | Freemium? Assinatura? Quanto? |
| UX mobile | PWA? App nativo? Responsivo só? |
| Onboarding | Demora pra gerar valor? |
| Tom | Culpa? Restrição? Motivação? Neutro? |
| Público-alvo | BR focado? Gen Z? Familiar? Empresarial? |

### Saídas esperadas

- `docs/09-concorrencia.md` com análise completa
- Lista de 5–8 diferenciais competitivos que viram copy da landing page
- Decisão fundamentada sobre o que NÃO implementar (reforço do "fora de roadmap")

---

## Landing Page — após estudo de concorrência

**Objetivo**: página pública que converte visitantes em usuários, comunicando com clareza o valor único do finance-app para o público brasileiro que quer acumular capital.

### Estrutura da LP

- [ ] **Hero** — headline que captura a proposta em 1 frase + subheadline + CTA ("Comece grátis")
- [ ] **Problema** — a dor do brasileiro que quer guardar mas não sabe onde vai o dinheiro
- [ ] **Solução** — 3 pilares visuais (consciência, acúmulo, gamificação saudável)
- [ ] **Features em destaque** — 5–6 cards com as features mais diferenciadas (pós-concorrência)
- [ ] **Dashboard preview** — screenshot / mockup interativo do produto real
- [ ] **Prova social** — depoimentos (ou placeholder para quando tiver usuários)
- [ ] **Comparativo** — tabela "finance-app vs alternativas" com diferenciais honestos
- [ ] **Gamificação explicada** — seção mostrando que NÃO é dark pattern
- [ ] **Preço** — seção clara (mesmo que seja "grátis na beta")
- [ ] **FAQ** — 5–7 perguntas mais prováveis
- [ ] **CTA final** — "Comece a controlar seu dinheiro hoje" + formulário de e-mail
- [ ] **Rodapé** — links legais, LGPD, redes sociais

### Stack da LP

- Opção A: React + Vite (mesmo repo, rota `/`, separada do app)
- Opção B: Astro standalone (melhor SEO, mais rápido, separação clara)
- **Decisão**: se SEO for prioridade → Astro; se velocidade de entrega → React

### SEO e performance

- [ ] Meta tags completas (OG, Twitter Card, canonical)
- [ ] Schema.org markup (SoftwareApplication)
- [ ] Core Web Vitals: LCP < 2.5s, CLS < 0.1
- [ ] Sitemap.xml + robots.txt

### CTAs em toda a página

Hero → após features → após comparativo → após gamificação → antes do FAQ → rodapé fixo mobile

---

## v3 — Aplicativo Móvel

**Objetivo**: experiência nativa mobile para registro rápido de gastos, notificações push e consulta de saldo.

**Pré-requisito**: landing page no ar + backend v1 estável.

### Tecnologia

- [ ] **React Native + Expo** — reaproveitamento máximo do código React existente
- [ ] **Expo Router** — file-based, análogo ao React Router
- [ ] **TanStack Query** — mesma lib, mesma lógica de cache
- [ ] **NativeWind** — Tailwind em React Native
- [ ] **Expo Notifications** — push nativo (substitui web push no mobile)
- [ ] **Expo SecureStore** — token/sessão segura

### Funcionalidades MVP mobile

- [ ] Login (Sanctum token para mobile)
- [ ] Dashboard resumido (patrimônio + mês atual)
- [ ] Registro rápido: valor + categoria + conta (3 taps)
- [ ] Listagem de transações recentes (últimas 20)
- [ ] Notificações push nativas (marcos, lembretes)
- [ ] Widget de patrimônio na home screen (iOS / Android)

### Diferenciais mobile

- **Registro no momento** — abertura < 2s, fluxo de 3 taps
- **Câmera para recibo** — foto → OCR → preenchimento sugerido (v3.1)
- **Widget home screen** — saldo e taxa do mês sem abrir o app
- **Modo offline** — fila de sync quando voltar conexão
- **Biometria** — FaceID / TouchID para autenticação rápida

### Distribuição

- [ ] TestFlight (iOS beta) + APK direto (Android beta) para MVP
- [ ] App Store + Google Play quando estabilizar
