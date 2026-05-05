# Regra — Imutabilidade de Docs e Plans

Documentos de planejamento e documentação do projeto são **imutáveis** após criados. Esta regra evita que ideias e contexto histórico sejam perdidos por edições in-place.

---

## Regra 1 — Não alterar docs existentes

### Pastas protegidas

- `docs/` — documentação do produto
- `.claude/plans/` — planos de execução (incluindo subpastas: `mvp-v1/`, `mvp-v1.5/`, etc.)

### O que é proibido

- Usar `Edit` em arquivos `.md` dentro das pastas protegidas
- Usar `Write` para sobrescrever arquivos `.md` existentes em pastas protegidas
- Renomear ou mover arquivos `.md` em pastas protegidas

### O que é permitido

- **Criar arquivos novos** dentro das pastas protegidas
- Editar livremente código (`backend/`, `frontend/`, `*.php`, `*.ts`, `*.tsx`, etc.)
- Editar arquivos de configuração (`docker-compose.yml`, `Makefile`, `package.json`, `composer.json`, `.env.example`, etc.)
- Editar `MEMORY.md` e arquivos do sistema de memória em `memory/`

---

## Regra 2 — Quando precisar alterar, criar entrada no changelog

Quando você identificar a necessidade de mudar, complementar ou corrigir um doc/plano existente, **NÃO edite o original**. Em vez disso, crie um novo documento em:

```
.claude/plans/changelog/
```

(Crie a pasta se não existir.)

### Convenção de nome

```
YYYY-MM-DD-NNN-titulo-curto.md
```

- `YYYY-MM-DD` = data da criação (use `currentDate` do contexto)
- `NNN` = contador incremental de 3 dígitos no dia (`001`, `002`, ...)
- `titulo-curto` = kebab-case, máx. 5 palavras

Exemplo: `2026-05-04-001-add-investment-kind-validation.md`

### Estrutura obrigatória do changelog

```markdown
---
target: <caminho-relativo-do-doc-afetado>
type: <idea | correction | expansion | deprecation>
date: <YYYY-MM-DD>
---

# <Título descritivo>

## Motivação
<Por que essa mudança/ideia surgiu — contexto da conversa, descoberta na implementação, feedback, etc.>

## Conteúdo
<A nova ideia, correção, expansão ou nota de descontinuação>

## Impacto
<Quais decisões anteriores isso muda; quais módulos do plano afeta>

## Patch sugerido (opcional)
<Se aplicável, mostrar como ficaria o trecho atualizado em forma de diff ou bloco de código — mas nunca aplicar diretamente no doc original>
```

### Tipos válidos

| type | quando usar |
|---|---|
| `idea` | nova ideia que se conecta a um doc existente |
| `correction` | correção factual (algo no doc está errado) |
| `expansion` | aprofundamento de algo já mencionado, com novo detalhe |
| `deprecation` | marcar uma decisão anterior como descontinuada |

---

## Regra 3 — Master doc para pastas com >10 documentos

Quando uma subpasta de `.claude/plans/` ultrapassar **10 arquivos `.md`**, crie um arquivo mestre nessa pasta.

### Comportamento

1. Verifique se a pasta já tem um arquivo `plan.md`. **Se tiver, não crie nada — `plan.md` já é o mestre.**
2. Caso contrário, crie `MASTER.md` na raiz da pasta com:
   - Lista numerada dos documentos da pasta
   - Ordem de execução recomendada
   - Apenas menção (caminho + título + 1 linha de descrição) — não duplicar conteúdo

### Exemplo de `MASTER.md`

```markdown
# Master — <nome-da-pasta>

Ordem de execução para os docs desta pasta.

1. [01-improvements.md](01-improvements.md) — schema changes (caixinhas, investment kind)
2. [03-docker-unified.md](03-docker-unified.md) — dev infra unificada
3. [04-onboarding.md](04-onboarding.md) — fluxo de primeiro acesso
...
```

### O que NÃO fazer

- Não copiar conteúdo dos docs filhos para o master
- Não criar master se já existe `plan.md` (ele é o mestre)
- Não recontar documentos da pasta `changelog/` (changelog não conta para o limite)

---

## Resumo prático

| Situação | O que fazer |
|---|---|
| Quero corrigir um typo em `docs/02-schema.md` | Criar `.claude/plans/changelog/YYYY-MM-DD-NNN-fix-typo-schema.md` com `type: correction` |
| Tenho uma nova ideia para o módulo de wishlist | Criar entrada no changelog com `type: idea` apontando para `mvp-v1.5/05-wishlist-link.md` |
| Descobri que uma decisão de plano está obsoleta | Criar entrada com `type: deprecation` |
| Pasta `mvp-v2/` tem 11 docs e nenhum `plan.md` | Criar `mvp-v2/MASTER.md` listando ordem |
| Pasta `mvp-v1.5/` tem 11 docs mas tem `plan.md` | Não fazer nada — `plan.md` já é o mestre |
| Quero adicionar uma seção totalmente nova ao plano | Criar arquivo novo na subpasta (não é alteração) |

---

## Por que essa regra existe

Docs e planos capturam o **raciocínio histórico** do projeto. Editar in-place destrói esse histórico — um leitor futuro não consegue saber o que foi pensado quando, ou por que uma decisão mudou. O changelog preserva a evolução das ideias e protege o trabalho de planejamento já feito.
