# Política de Commits — OncoMente

## Formato obrigatório

```
<emoji> <type>(<scope>): <subject>
```

- **emoji**: obrigatório
- **type**: ver tabela abaixo
- **scope**: obrigatório quando fizer sentido
- **subject**: imperativo, máx. 5 palavras, sem ponto final

## Tipos + Emojis

| type | emoji | quando usar |
|------|-------|-------------|
| feat | ✨ | nova funcionalidade |
| fix | 🐛 | correção de bug |
| style | 👌 | ajustes visuais / formatting (sem lógica) |
| refactor | ♻️ | refatoração sem mudança de comportamento |
| chore | 🔧 | configuração, dependências, scripts |
| docs | 📚 | documentação |
| test | 🧪 | testes |
| ci | 🧱 | CI/CD, pipelines |
| remove | 🗑️ | remoção de código ou arquivos |
| revert | ⏪ | reversão de commit |

## Scopes Mobile

`screens`, `navigation`, `components`, `hooks`, `store`, `services`, `styles`, `assets`, `mock`

Exemplos por área:
- `oncology`, `mental-health`, `mascot`, `personal-area`, `auth`, `home`

## Scopes Backend

`auth`, `user`, `media`, `leisures`, `personal`, `mascot`, `prisma`

## Regras

- **1 commit = 1 mudança lógica**
- Commits completos em si, incrementais e reversíveis
- NUNCA misturar: `feat` + `refactor`, `style` + `logic`, `mobile` + `backend` não relacionados
- Ordem recomendada: `refactor` (no-op) → `feat` mínima → extensões → `test` → `docs`/`chore`/`style`

## Exemplos

```
✨ feat(mental-health): add breathing exercises screen
🐛 fix(auth): token refresh on 401
♻️ refactor(components): extract reusable card component
👌 style(oncology): adjust spacing on nutrition screen
🗑️ remove(navigation): delete unused social area tabs
🔧 chore(deps): upgrade expo sdk to 52
```

## Comando

Use `/commit` para commitar seguindo essa política.