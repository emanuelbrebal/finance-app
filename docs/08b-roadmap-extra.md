# 08b — Roadmap Extra: Concorrência, Landing Page e App Mobile

Planos de posicionamento de mercado e expansão de plataforma. Pré-requisito para todos: v1 completa.

Este documento consolida visão de alto nível das 3 frentes pós-v1. Detalhamento completo de cada uma:
- `docs/09-concorrencia.md`
- `docs/10-landing-page.md`
- `docs/11-app-mobile.md`

---

## Sequência recomendada

```
v1 completa
    ↓
[Concorrência] → estudo de mercado, diferenciais validados
    ↓
[Landing Page] → aquisição pública, primeiros usuários reais
    ↓
[App Mobile]   → expansão de plataforma após validação de produto
```

A ordem importa. Lançar landing sem estudo de concorrência = copy genérica. Lançar app mobile sem usuários reais no web = sem feedback pra direcionar prioridades.

---

## 1. Estudo de Concorrência

**Objetivo**: mapear o campo de batalha com precisão antes de comunicar o produto ao mercado.

### Concorrentes prioritários
- **Plannerfin** (plannerfin.com.br)
- **Pierre** (pierre.finance) — atenção especial à promessa de IA

### Eixos de análise
Foco de produto, gamificação, importação, ética de dados, preço, UX mobile, onboarding, tom, público-alvo.

### Saídas
- Tabela comparativa feature-by-feature
- Lista de 5–8 diferenciais competitivos do finance-app
- Posicionamento final (1 parágrafo) para landing, pitch e bio
- Reforço fundamentado do que NÃO implementar

**Princípio**: concorrência informa, não dita. Diferenciação saudável > paridade tóxica.

📄 Detalhamento: `docs/09-concorrencia.md`

---

## 2. Landing Page

**Objetivo**: converter visitantes em usuários, comunicando o valor único do finance-app para brasileiros que querem acumular capital.

### Estrutura
Hero → Problema → Solução (3 pilares) → Features → Dashboard preview → Prova social → Comparativo → Gamificação explicada → Preço → FAQ → CTA final → Rodapé

### Stack — decisão
- **Astro** se SEO orgânico for canal principal
- **React + Vite** se aquisição for via mídia paga / redes
- Decidir após estudo de concorrência (entender se concorrentes ranqueiam no Google)

### Princípios
- Copy construída a partir de diferenciais validados, não chutes
- Gamificação saudável tem seção dedicada (inverter percepção negativa do termo)
- Comparativo honesto incluindo onde concorrentes ganham
- Sem trackers invasivos (Plausible/Umami no lugar de GA4)
- Performance e SEO obrigatórios (Core Web Vitals dentro do verde)

### Saídas
- Landing no ar em domínio próprio
- Métricas de conversão configuradas
- A/B test do hero rodando

📄 Detalhamento: `docs/10-landing-page.md`

---

## 3. App Mobile (v3)

**Objetivo**: registro rápido de gastos no momento da decisão e notificações push nativas.

### Por que só agora
Web cobre análise e revisão. Mobile é essencial pra **registro no momento** e **consciência antes da compra**. PWA cobre 80% inicialmente; nativo só faz sentido com base de usuários validada.

### Stack
React Native + Expo + Expo Router + TanStack Query + NativeWind. Máximo reaproveitamento do código React web — lógica de negócio em pacote shared.

### MVP mobile
- Login com biometria
- Dashboard resumido
- Registro rápido (3 taps, < 2s)
- Lista de transações recentes
- Wishlist com checkpoints
- Push notifications nativas
- Widget de home screen (iOS WidgetKit + Android Glance)

### Diferenciais mobile
- Velocidade extrema no registro
- Modo offline com sync
- Câmera + OCR para recibo (v3.1)
- Atalhos do sistema (Siri Shortcuts, Android Shortcuts)

### Ajustes no backend
- Sanctum em modo token (não cookie)
- Endpoint de registro de device para push
- Versionamento de cliente (header)
- Endpoint slim de dashboard otimizado

### Distribuição
Beta via TestFlight e APK direto → produção em App Store e Google Play após estabilizar.

### O que NÃO fazer
Replicar complexidade do web, gráficos densos, importação de extratos no mobile, notificações agressivas.

📄 Detalhamento: `docs/11-app-mobile.md`

---

## Princípio orientador (vale para as 3 frentes)

A pergunta central permanece: *"isso ajuda o usuário a juntar mais dinheiro ou ter mais consciência de consumo?"*

- **Concorrência** ajuda porque permite comunicar diferenciais reais (não vaporware)
- **Landing page** ajuda porque traz mais pessoas pro produto que entrega valor real
- **App mobile** ajuda porque o registro no momento é onde a consciência financeira realmente acontece

Se em algum momento alguma dessas frentes virar "feature pra parecer maior" sem servir o usuário, despriorizar.
