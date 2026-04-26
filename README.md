# finance-app

Plataforma pessoal de gestão financeira focada em **acúmulo de capital** e **consciência de consumo**, com gamificação saudável.

> ⚠️ Nome de projeto provisório — `finance-app` é placeholder. Trocar quando definir nome final (ver opções discutidas: Estima, Sextante, Quilha, Rumo, etc.)

## O que é

App de finanças pessoais que:
- Registra transações manualmente ou importa de extratos OFX/CSV
- Categoriza automaticamente (regras que aprendem com correções)
- Mostra KPIs de acúmulo: patrimônio, taxa de poupança, burn rate, runway
- Trata reserva de emergência como conceito de primeira classe
- Tem wishlist com 5 checkpoints objetivos antes de liberar compra
- Gera insights automáticos (gastos anormais, assinaturas detectadas, projeção de meta)
- Celebra marcos da jornada (R$ 1k, 5k, 10k...) com gamificação saudável (sem XP, sem leaderboard)
- Manda notificações em momentos estratégicos (não spam)

## O que NÃO é

- ❌ App de investimento ou cotação de ativos
- ❌ Robô de scraping bancário
- ❌ App com dark patterns ou notificações ansiogênicas

## Stack

**Backend**: Laravel 11 (PHP 8.3) + PostgreSQL 16 + Redis + Sanctum
**Frontend**: React 18 + TypeScript + Vite + TailwindCSS + shadcn/ui + TanStack Query
**Infra**: Docker Compose

## Como começar

```bash
# 1. Clone o repo
git clone <url> finance-app
cd finance-app

# 2. Leia a documentação (importante!)
cat CLAUDE.md
ls docs/

# 3. (Quando o código existir)
docker compose up -d
```

## Estrutura

```
finance-app/
├── CLAUDE.md           # contexto permanente (lido pelo Claude Code automaticamente)
├── README.md           # este arquivo
├── docs/               # spec completa do projeto
│   ├── 00-visao-geral.md
│   ├── 01-arquitetura.md
│   ├── 02-schema.md
│   ├── 03-endpoints.md
│   ├── 04-estrutura-pastas.md
│   ├── 05-gamificacao.md
│   ├── 06-importacao.md
│   ├── 07-wishlist.md
│   └── 08-roadmap.md
├── backend/            # (a criar) Laravel API
└── frontend/           # (a criar) React SPA
```

## Trabalhando com Claude Code

Este projeto foi desenhado pra ser implementado em parceria com Claude Code. O arquivo `CLAUDE.md` é lido automaticamente em toda conversa e contém princípios e padrões que devem ser seguidos.

Para tarefas específicas, peça ao Claude Code para ler o doc relevante:

```
Leia docs/02-schema.md e docs/04-estrutura-pastas.md.
Em seguida, crie as migrations das tabelas de transactions, accounts e categories.
```

## Status

Documentação ✅ | Código backend ⏳ | Código frontend ⏳

## Roadmap resumido

- **MVP** (~1 semana): Auth + transações manuais + categorias + dashboard básico + importação CSV/OFX
- **v1** (~1 mês): Wishlist + gamificação + insights + notificações + snapshots + recorrentes
- **v1.5**: Modo co-piloto + busca de preços sob demanda (SerpAPI)
- **v2**: Modo desafio + monitoramento contínuo de preços + integrações Open Finance

Detalhes em `docs/08-roadmap.md`.
