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
git clone https://github.com/emanuelbrebal/finance-app
cd finance-app

# 2. Copie o .env e suba os containers
cp backend/.env.example backend/.env
docker compose up -d

# 3. Instale dependências, gere a key e rode as migrations
docker compose exec laravel composer install
docker compose exec laravel php artisan key:generate
docker compose exec laravel php artisan migrate

# 4. Instale o frontend e suba o dev server
cd frontend && npm install && npm run dev
```

Acesse em `http://localhost:5173` — crie sua conta na tela de cadastro.

## Estrutura

```
finance-app/
├── CLAUDE.md           # contexto permanente (lido pelo Claude Code automaticamente)
├── DESIGN.md           # sistema de design: paleta, tipografia, componentes
├── docs/               # spec e arquitetura do projeto
│   ├── 00-visao-geral.md
│   ├── 01-arquitetura.md
│   ├── 02-schema.md
│   ├── 03-endpoints.md
│   ├── 04-estrutura-pastas.md   # mapa atualizado do código real
│   ├── 05-gamificacao.md
│   ├── 06-importacao.md
│   ├── 07-wishlist.md
│   └── 08-roadmap.md
├── backend/            # Laravel 11 API (PHP 8.3 + PostgreSQL + Redis)
└── frontend/           # React 18 SPA (TypeScript + Vite + TailwindCSS)
```

## Trabalhando com Claude Code

O arquivo `CLAUDE.md` é lido automaticamente em toda conversa e contém princípios, padrões e o mapa de documentação. Para tarefas específicas, instrua o Claude Code a ler o doc relevante antes de codar:

```
Leia docs/02-schema.md e docs/05-gamificacao.md.
Implemente os marcos de patrimônio da Fatia X.
```

## Status do MVP

| Módulo | Backend | Frontend |
|---|---|---|
| Auth (login / cadastro) | ✅ | ✅ |
| Contas | ✅ | ✅ |
| Categorias | ✅ | ✅ |
| Transações + filtros | ✅ | ✅ |
| Dashboard (KPIs + gráfico) | ✅ | ✅ |
| Perfil | ✅ | ✅ |
| Importação OFX/CSV | ⏳ v1 | ⏳ v1 |
| Wishlist + checkpoints | ⏳ v1 | ⏳ v1 |
| Gamificação / marcos | ⏳ v1 | ⏳ v1 |
| Insights automáticos | ⏳ v1 | ⏳ v1 |

## Roadmap

- **MVP** ✅ — Auth + CRUD completo + dashboard + filtros + perfil
- **v1** — Wishlist, gamificação, insights, importação OFX/CSV, notificações
- **v1.5** — Modo co-piloto, busca de preços (SerpAPI)
- **v2** — Modo desafio, Open Finance, monitoramento contínuo de preços

Detalhes em `docs/08-roadmap.md`.
