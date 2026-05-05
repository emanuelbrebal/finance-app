# 14 — Validação de Produto

Estratégia para validar produto **antes de codar muito** e construir lista de espera com early adopters reais.

**O formulário em si** está em `14b-formulario.md` — pronto pra copiar pro Tally / Google Forms.

**Quando usar**: AGORA. Antes do MVP. Não depois.

---

## Por que validar antes de codar

Construir é caro (em tempo). Mudar de rumo depois de 3 meses de código é caríssimo. 30 minutos fazendo formulário + 2 semanas distribuindo evita isso.

Você vai descobrir:
- Se o problema que você quer resolver é real pra outras pessoas
- Quais features importam de verdade vs quais você acha que importam
- Quanto as pessoas pagariam (não confunda com "quanto valeria")
- Quem são seus early adopters (a lista de espera vira sua base inicial)

---

## Ferramentas recomendadas

| Ferramenta | Custo | Por que |
|---|---|---|
| **Tally** | Grátis | Visual bonito, lógica condicional, sem branding agressivo |
| **Typeform** | Grátis (limitado) | Bonito mas limita respostas no plano free |
| **Google Forms** | Grátis | Funcional, sem charme, dados crus pra Sheets |
| **Fillout** | Grátis | Alternativa moderna ao Tally |

**Recomendação**: Tally. Visual profissional, free generoso, exportação fácil pra CSV.

---

## Estratégia de distribuição

### Onde divulgar (em ordem de prioridade)

**1. WhatsApp pessoal**
- Grupos de amigos próximos primeiro (alta confiança = alta resposta)
- Grupos de faculdade, trabalho, hobbies
- Status pessoal com link

**2. Redes sociais pessoais**
- Twitter/X com thread explicando o porquê do projeto
- LinkedIn com post estruturado (problema → solução → CTA)
- Instagram stories (talvez post no feed também)

**3. Comunidades online**
- Discord de devs BR
- Grupos de Telegram de finanças
- Reddit BR (com cuidado pra não parecer spam — comentar com valor antes)

**4. Indicação de amigos**
- Pedir explicitamente: "se você conhece 2 pessoas que se encaixam, manda pra elas"

### Mensagem padrão pra distribuir

Versão WhatsApp/curta:
```
E aí! Tô construindo um app de finanças pessoais
focado em quem quer JUNTAR dinheiro (não investir).

Tô coletando opinião antes de começar a programar
pra construir o que faz sentido. 5 min do seu tempo
pode mudar bastante o produto:

[link]

Quem responder entra na lista de acesso antecipado.
```

Versão LinkedIn/longa:
```
Construindo um app de finanças pessoais e preciso da sua ajuda.

Diferente da maioria, esse app não é sobre investimentos —
é sobre **acumular capital** com consciência. Pra quem está
construindo patrimônio do zero, sem culpa.

Antes de gastar 6 meses programando, quero ENTENDER
o que vocês precisam de verdade.

5 minutos do seu tempo:
[link]

Se quiser acompanhar o processo, salva esse perfil — vou
postar progresso, decisões e bastidores semanalmente.

Bora?
```

---

## Análise das respostas

**Meta inicial**: 50-100 respostas em 2-3 semanas.

**O que olhar primeiro**:
1. **Pergunta 2.3** (frustração) — campo livre. Padrões aqui são ouro
2. **Pergunta 3** (problemas) — média de cada item. Os 3 mais altos são onde focar
3. **Pergunta 4.2** (feature mais importante) — distribuição mostra o que priorizar
4. **Pergunta 5.2** (preço) — mediana é referência pra precificação

**Sinais de validação**:
- ✅ Mais de 70% marcam algum problema com 4 ou 5
- ✅ Mediana de disposição a pagar > R$ 10
- ✅ Mais de 60% querem entrar na lista de acesso antecipado
- ✅ Padrões claros aparecem nos campos livres

**Sinais de alerta**:
- ⚠️ Maioria já satisfeita com método atual (4 ou 5 na 2.2)
- ⚠️ Mediana de pagamento R$ 0
- ⚠️ Features mais votadas são as que concorrentes já fazem bem
- ⚠️ Ninguém quer fazer entrevista de 15 min (baixo engajamento)

Se houver sinais de alerta, **não desistir** — investigar mais fundo. Talvez precise reposicionar, não desistir do produto.

---

## Saídas esperadas

- 50+ respostas qualificadas
- Lista de espera com 30+ e-mails
- Top 3 features priorizadas pelo público
- Faixa de preço validada
- 5-10 entrevistas de 15min agendadas
- Padrões de frustração documentados pra usar na landing page
- Decisões fundamentadas pra atualizar `12-precificacao.md` e `08-roadmap.md`

---

## Próximos passos depois do formulário

1. Compilar resultados em planilha
2. Atualizar `12-precificacao.md` com preço validado
3. Repriorizar `08-roadmap.md` com features validadas
4. Fazer entrevistas com 5-10 pessoas que toparam
5. Construir MVP focado nos problemas top 3
6. Manter lista de espera engajada com updates regulares

---

## Princípios da validação

- **Não valide o que você quer ouvir**: o objetivo é descobrir verdade, não confirmar viés
- **Texto livre é ouro**: pessoas escrevem coisas que múltipla escolha não captura
- **Disposição a pagar ≠ valor**: respostas otimistas; aplicar desconto mental de 30-50%
- **Quem toparia entrevista é early adopter real**: priorize esses
- **Compartilhamento natural valida posicionamento**: se ninguém compartilha o formulário, talvez o pitch precise mudar
