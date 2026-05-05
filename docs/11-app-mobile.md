# 11 — Aplicativo Móvel (v3)

Experiência nativa mobile complementar ao SPA web. Pré-requisito: landing page no ar + backend v1 estável.

**Objetivo**: registro rápido de gastos, notificações push nativas e consulta de saldo no momento — onde o usuário toma a decisão de gastar.

---

## Por que mobile (e por que só agora)

A maioria das transações acontece no mundo real, com celular na mão. A web é ótima pra **revisão e análise**; mobile é essencial pra **registro no momento** e **consciência antes da compra**.

Não fazer no MVP é decisão consciente: build mobile estável demanda esforço significativo, e PWA cobre 80% das necessidades inicialmente.

---

## Stack

| Componente | Escolha | Justificativa |
|---|---|---|
| Framework | **React Native + Expo** | Reaproveita lógica do React web; Expo simplifica build/distribuição |
| Roteamento | **Expo Router** | File-based, análogo ao React Router que já usamos |
| Estado de servidor | **TanStack Query** | Mesma lib do web — zero curva de aprendizado |
| Estilização | **NativeWind** | Tailwind em RN — paridade com design system existente |
| Push notifications | **Expo Notifications** | Substitui Web Push no mobile |
| Storage seguro | **Expo SecureStore** | Token Sanctum + dados sensíveis criptografados |
| Forms | **React Hook Form + zod** | Mesma stack do web; validators reaproveitáveis |

**Princípio**: máximo reaproveitamento do código React existente. Lógica de negócio, hooks de query, validators e tipos da API devem viver em pacote compartilhado entre web e mobile.

---

## Estrutura de monorepo (sugestão)

```
finance-app/
├── backend/         # Laravel API
├── frontend/        # React SPA web
├── mobile/          # React Native + Expo  ← novo
├── shared/          # tipos, validators, hooks ← novo
└── landing/         # landing page (Astro?)
```

Migrar para monorepo com pnpm workspaces ou Turborepo na transição para v3.

---

## Funcionalidades MVP mobile

### Autenticação
- [ ] Login com email/senha (Sanctum token, não cookie — diferente do web)
- [ ] Biometria (FaceID / TouchID) para reabertura rápida
- [ ] Persistência segura do token via SecureStore

### Telas principais
- [ ] **Dashboard resumido** — patrimônio + KPIs do mês + nível de jornada
- [ ] **Registro rápido** — fluxo de 3 taps: valor → categoria → conta → salvar
- [ ] **Lista recente** — últimas 20 transações, scroll infinito
- [ ] **Wishlist** — checkpoints visuais, prompt "ainda quer?"
- [ ] **Perfil/Settings** — preferências essenciais

### Notificações push nativas
- [ ] Marcos detectados
- [ ] Lembretes estratégicos (domingo, dia 1, pós-salário, sexta)
- [ ] Notificação de "ainda quer?" no fim da quarentena de wishlist
- [ ] Configuração granular no app (mesmas preferências do web)

### Widget de home screen
- [ ] iOS: WidgetKit via Expo Modules — patrimônio + taxa do mês
- [ ] Android: Glance — mesma informação
- [ ] Atualização: a cada 30 minutos ou pull manual

---

## Diferenciais mobile

### Registro no momento (< 2s, 3 taps)
Fluxo otimizado para velocidade extrema:
- Abertura do app já abre direto na tela de registro (configurável)
- Categorias mais usadas em primeiro lugar
- Conta default pré-selecionada
- Botão "Salvar" sempre visível, sem scroll

### Câmera para recibo (v3.1)
- [ ] Foto do cupom fiscal
- [ ] OCR via biblioteca nativa ou API
- [ ] Preenchimento automático sugerido (valor + descrição)
- [ ] Usuário valida antes de salvar

Adiar para v3.1: OCR é pesadelo de manutenção, melhor lançar mobile básico antes.

### Modo offline
- [ ] Fila local de transações criadas sem conexão
- [ ] Sync automático quando voltar internet
- [ ] Indicador visual de "X transações pendentes de sync"
- [ ] Conflict resolution: server wins por padrão, com prompt em casos críticos

### Atalhos do sistema
- iOS: Siri Shortcuts ("Registrar gasto", "Quanto tenho")
- Android: App Shortcuts no long-press do ícone

---

## API: ajustes para mobile

Backend Laravel já está pronto, mas alguns ajustes são necessários:

- [ ] **Sanctum em modo token** (não cookie) — endpoint `/auth/mobile-login` que retorna token Bearer
- [ ] **Endpoint de revogação de token** — logout real
- [ ] **Push registration endpoint** — `POST /devices` para registrar token Expo do device
- [ ] **Versionamento de cliente** — header `X-Client-Version` para forçar update se necessário
- [ ] **Endpoint slim de dashboard** — versão reduzida do `/dashboard` otimizada pra mobile (sem gráficos pesados)

---

## Distribuição

### Beta
- [ ] **TestFlight** (iOS) — convite por e-mail
- [ ] **APK direto** (Android) — distribuição via link no GitHub Releases
- [ ] **Expo Updates** — OTA updates para correções rápidas sem nova review

### Produção
- [ ] App Store — quando estabilizar
- [ ] Google Play — quando estabilizar
- [ ] Manter Expo Updates ativo para hotfixes

---

## Métricas críticas mobile

- **Tempo de cold start** — meta: < 2s
- **Tempo até primeiro tap útil** — meta: < 3s após cold start
- **Taxa de crash** — meta: < 0.5%
- **Retenção D1/D7/D30** — referência da indústria, não punição

---

## O que NÃO fazer no mobile

- ❌ Replicar toda a complexidade do web — mobile é registro + consulta, não análise profunda
- ❌ Gráficos densos — usar visualizações simplificadas
- ❌ Páginas de configuração extensas — manter no web
- ❌ Importação de extratos no mobile — fluxo é melhor no desktop com arquivo grande
- ❌ Notificações push agressivas — princípios de gamificação saudável valem aqui também

---

## Saídas esperadas v3

- App iOS e Android publicados nas lojas
- Paridade funcional para os fluxos principais (registro, consulta, wishlist)
- Push notifications funcionando
- Pelo menos 1 widget de home screen ativo
- Sync offline operacional
