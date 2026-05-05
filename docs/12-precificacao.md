# 12 — Precificação e Modelo de Negócio

Quanto cobrar, quando cobrar, e como estruturar planos. Pré-requisito: estudo de concorrência (`09-concorrencia.md`) e validação de produto (`14-validacao.md`).

**Princípio orientador**: o preço comunica o posicionamento. Cobrar pouco demais sinaliza falta de valor; cobrar como o Pierre Premium (R$ 199/mês) afasta o público que mais precisa do produto. O preço deve refletir o usuário-alvo: alguém começando a juntar dinheiro.

---

## Filosofia de monetização

### O que NÃO fazer
- ❌ Cobrar pelo essencial — registrar transação, ver patrimônio, importar extrato é direito básico do usuário sobre os próprios dados
- ❌ Limitar bancos no plano grátis a 1 (estratégia do Pierre) — frustra mais que monetiza
- ❌ Dark patterns de upgrade ("você está perdendo X")
- ❌ Cobrança de saída — exportação completa de dados sempre grátis (LGPD + ética)

### O que fazer
- ✅ Plano grátis genuinamente útil — usuário consegue **acumular dinheiro de verdade** sem pagar nada
- ✅ Cobrança por features avançadas que entregam valor mensurável extra
- ✅ Modelo previsível e transparente — sem letras miúdas
- ✅ Open source garante que código fica livre mesmo se empresa morrer

---

## Estrutura de planos sugerida

### 🌱 Plano Livre (R$ 0)
Tudo que precisa pra acumular dinheiro com método.
- Contas e categorias ilimitadas
- Transações ilimitadas (manuais e via importação)
- Importação de extratos OFX/CSV ilimitada
- Dashboard completo, todos os KPIs
- 3 objetivos simultâneos
- Wishlist com até 10 itens ativos
- Reserva de emergência
- Insights automáticos básicos (5 regras)
- Gamificação completa (marcos, jornada, streaks)
- Exportação completa de dados

### 🧭 Plano Navegador (R$ 14,90/mês ou R$ 149/ano)
Pra quem quer ir além do controle básico.
- Tudo do Livre, sem limites em objetivos e wishlist
- Insights avançados (todas as regras, frequência maior)
- Busca de preços sob demanda na wishlist (SerpAPI, X consultas/mês)
- Detecção automática de assinaturas
- Histórico de preços de itens da wishlist
- Notificações via WhatsApp (se implementado)
- Suporte prioritário

### 🏛️ Plano Soberano (R$ 29,90/mês ou R$ 299/ano)
Pra quem usa o app como ferramenta central de planejamento.
- Tudo do Navegador
- Múltiplos workspaces (família, casal, MEI separado do pessoal)
- Open Finance oficial (quando implementado em v2+)
- Modo desafio (sprints de economia)
- Monitoramento contínuo de preços
- API de exportação (integração com planilhas, outros apps)
- Acesso antecipado a novas features

---

## Comparativo com concorrência

```
                       Livre    Pro            Premium
finance-app            R$ 0     R$ 14,90/mês   R$ 29,90/mês
Pierre                 R$ 0     R$ 39/mês      R$ 199/mês
```

**Posicionamento de preço**: significativamente mais barato que Pierre, especialmente no tier Premium. Justificável porque:
- Não temos custo de Open Finance no MVP/v1 (importação manual)
- Open source = comunidade ajuda no desenvolvimento
- Foco em acúmulo de capital, público-alvo tem orçamento mais sensível
- Cada real cobrado é um real que o usuário não consegue guardar — preço razoável é coerente com missão

---

## Decisões a validar via formulário

Itens do `14-validacao.md` que alimentam decisões deste documento:

- [ ] **Disposição a pagar**: quanto o público-alvo pagaria mensalmente?
- [ ] **Features que valem upgrade**: quais features as pessoas pagariam por?
- [ ] **Mensal vs anual**: qual desconto torna anual atraente?
- [ ] **Trial vs freemium**: prefere testar 14 dias ou ter plano grátis pra sempre?

Não definir preço antes de validar. Os números acima são hipótese inicial.

---

## Estratégia de lançamento de cobrança

### Fase 1: Beta totalmente grátis (3-6 meses)
- Tudo grátis, todas as features
- Em troca: feedback ativo, depoimentos, NPS
- Beta testers ganham 1 ano de Plano Soberano grátis quando começar cobrança
- Cria base de usuários reais antes de monetizar

### Fase 2: Lançamento com cobrança (após v1.5)
- Plano Livre permanente
- Plano Navegador disponível
- Soberano só quando Open Finance estiver pronto
- Comunicação clara: "ainda no beta = ainda gratuito; lançamento oficial = planos pagos"

### Fase 3: Otimização baseada em dados (v2+)
- A/B test de preços
- Analisar conversão Livre → Navegador
- Ajustar features dos planos baseado em uso real

---

## Receita projetada (cálculo de viabilidade)

Cenário conservador, ano 1 pós-lançamento:

```
Usuários totais ano 1:        1.000
Conversão Livre → Pago:       5%   = 50 pagantes
Mix Navegador / Soberano:     80% / 20%
- 40 × R$ 14,90 × 12 =        R$ 7.152
- 10 × R$ 29,90 × 12 =        R$ 3.588
Receita anual total:          ~R$ 10.740
```

Não é o suficiente pra largar emprego. **É o suficiente pra cobrir custos de infra** (servidor, domínio, SerpAPI, etc.) e validar que tem mercado. O salto vem com escala.

Cenário 5.000 usuários, 7% conversão:
```
350 pagantes × ~R$ 200/ano médio = R$ 70.000/ano
```

Já viável como complemento de renda significativo. Meta de médio prazo.

---

## Custos a cobrir

Antes de definir preço final, calcular custo unitário por usuário:

- Servidor (VPS razoável): ~R$ 80/mês
- Banco de dados gerenciado: ~R$ 50/mês
- Domínio + e-mail: ~R$ 15/mês
- SerpAPI (busca de preços): variável por uso
- Open Finance (futuro): custo por consulta + setup
- Suporte (seu tempo): a calcular

Plano Livre precisa ser barato de servir. Plano pago precisa cobrir uso médio + margem.

---

## Princípios de comunicação de preço

- **Transparência radical**: página de preços lista tudo, sem "fale conosco"
- **Sem trial enganoso**: trial é trial, plano é plano
- **Garantia de 30 dias**: reembolso sem perguntas
- **Cancelamento em 1 clique**: nada de retenção forçada
- **Anual com desconto real**: ~17% de desconto (2 meses grátis)
- **Mudança de plano sem fricção**: upgrade imediato, downgrade no fim do ciclo

---

## Saídas esperadas

- Tabela de planos finalizada após validação
- Página de preços na landing page (`/precos`)
- Política de reembolso publicada
- FAQ de pagamento + cancelamento
- Integração com gateway (Stripe/Pagar.me) na v1.5
