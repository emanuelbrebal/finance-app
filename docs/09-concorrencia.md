# 09 — Estudo de Concorrência

Mapeamento do campo de batalha antes do lançamento público. Pré-requisito: v1 completa.

**Objetivo**: encontrar brechas reais e construir diferenciais competitivos sólidos antes de comunicar o produto ao mercado brasileiro.

---

## Concorrentes prioritários

### Plannerfin (plannerfin.com.br)
Concorrente direto no mercado BR. Foco a confirmar via auditoria.

### Pierre (pierre.finance / lp.pierre.finance)
Concorrente direto. Posicionamento: "assistente de IA para finanças pessoais" via agentes autônomos (Marie, Einstein, Galileu). Backed pela Cloudwalk. Forte presença em mídia (Exame, Valor, Terra, EM).

**Stack confirmada (a partir da landing page)**:
- Open Finance oficial regulamentado pelo BC (não scraping) — diferencial técnico forte
- IA conversacional como interface principal ("pergunta como pra um amigo")
- Agentes autônomos especializados (cada um com nome próprio)
- Disponível via app, web e WhatsApp
- Criptografia AES-256
- Somente leitura (não movimenta dinheiro)

**Precificação confirmada**:
- Básico: grátis (1 banco, 1 agente, conversas limitadas)
- Pro: R$ 39/mês ou R$ 390/ano (5 bancos, 2 agentes, conversas ilimitadas)
- Premium: R$ 199/mês ou R$ 1.990/ano (bancos ilimitados, até 10 agentes)

**Hipóteses sobre fraquezas a validar na auditoria**:
- Foco em "entender o passado" (categorização, fatura, alertas) — pouca menção a metas e acúmulo de longo prazo
- IA conversacional pode ser fraca em ações concretas vs explicações
- Sem menção clara a wishlist, anti-impulso ou consciência pré-compra
- Sem gamificação visível na landing
- Preço Premium alto (R$ 1.990/ano) pra usuário individual não-empresarial

---

## Plano de auditoria

### Plannerfin
- [ ] Funcionalidades completas (mapear cada tela e fluxo)
- [ ] UX e onboarding (cronometrar tempo até primeiro valor entregue)
- [ ] Preço, planos, modelo (freemium? trial? assinatura?)
- [ ] Proposta de valor (como se posicionam na home)
- [ ] Tom de voz (cobrança? motivação? neutro?)
- [ ] Limitações percebidas (reviews na App Store, Reclame Aqui, redes sociais)
- [ ] Diferenciais que comunicam

### Pierre
- [ ] Mesmo checklist do Plannerfin
- [ ] **Atenção especial à IA**: que features de IA estão realmente implementadas vs prometidas?
- [ ] Como a IA é apresentada ao usuário (chat? sugestões? automação?)
- [ ] Validar se IA traz valor real ou é diferencial cosmético

---

## Eixos de análise comparativa

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

---

## Tabela comparativa (template)

A preencher após auditoria:

```
| Feature              | Plannerfin | Pierre | finance-app |
|----------------------|------------|--------|-------------|
| Importação OFX       | ?          | ?      | ✅          |
| Open Finance oficial | ?          | ?      | ⏳ v2       |
| Wishlist anti-impulso| ?          | ?      | ✅          |
| Gamificação saudável | ?          | ?      | ✅          |
| Reserva como entidade| ?          | ?      | ✅          |
| Insights automáticos | ?          | ?      | ✅          |
| Categorização que aprende | ?     | ?      | ✅          |
| Preço transparente   | ?          | ?      | ⏳          |
```

Legenda: ✅ implementado, ⏳ no roadmap, ❌ fora do escopo, ? a verificar.

---

## Lacunas a procurar

Hipóteses sobre o que provavelmente NÃO está bem resolvido nos concorrentes:

1. **Anti-impulso por design** — wishlist com checkpoints objetivos é raro
2. **Reserva de emergência como conceito** — geralmente vira só "outra meta"
3. **Gamificação saudável** — apps tendem a XP/streaks punitivas ou nada
4. **Tom não-punitivo** — apps BR ainda usam linguagem de "estourou orçamento"
5. **Foco em acúmulo, não investimento** — maioria mistura ou foca em ativos
6. **Importação flexível sem credenciais** — OFX + CSV genérico é raro
7. **Open source / código auditável** — diferencial em fintech pessoal

Validar cada hipótese durante a auditoria. Se for verdade, vira diferencial.

---

## Saídas esperadas do estudo

### 1. Análise completa
Este arquivo (`09-concorrencia.md`) atualizado com dados reais.

### 2. Lista de diferenciais competitivos (5–8)
Argumentos exclusivos do finance-app que viram copy da landing page. Exemplo:

```
1. "Wishlist com 5 checkpoints objetivos — você sabe quando pode comprar"
2. "Gamificação que celebra o real, não o engajamento com o app"
3. "Open source: seu código, seus dados, sua auditoria"
4. ...
```

### 3. Reforço do que NÃO implementar
Decisão fundamentada (com base no que concorrentes fazem mal) sobre o que manter fora do escopo. Exemplo: se plannerfin foca em recomendação de investimentos e mata UX, isso reforça nossa decisão de NÃO ser app de investimento.

### 4. Posicionamento final (1 parágrafo)
Texto curto e claro para usar em landing page, pitch deck e bio. Exemplo de estrutura:

> *"finance-app é a plataforma open source de gestão financeira para brasileiros que querem [DIFERENCIAL CENTRAL]. Diferente de [CONCORRENTES], a gente [PROPOSTA ÚNICA]."*

---

## Princípio orientador

A concorrência informa, não dita. Se um concorrente faz algo que viola nossos princípios (XP punitivo, dark patterns), **não copiamos** mesmo que funcione comercialmente. Diferenciação saudável > paridade tóxica.
