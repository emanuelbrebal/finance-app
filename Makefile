.PHONY: help up down build rebuild bash-laravel bash-vite migrate fresh seed test logs ps key worker-logs

# ── Worktree detection ─────────────────────────────────────────────────────────
WORKTREE_NAME  := $(notdir $(CURDIR))
ROOT_DIR       := $(shell git worktree list --porcelain 2>/dev/null | grep "^worktree " | head -1 | sed 's/worktree //')
COMPOSE_FILE   := $(ROOT_DIR)/docker-compose.yml
ENV_FILE       := $(CURDIR)/.env.docker
DC             := docker compose -f $(COMPOSE_FILE) --env-file $(ENV_FILE)

# ── Auto-generate .env.docker if missing ──────────────────────────────────────
$(ENV_FILE):
	@echo "Gerando $(ENV_FILE) para worktree '$(WORKTREE_NAME)'..."
	@printf 'COMPOSE_PROJECT_NAME=finance-$(WORKTREE_NAME)\n' > $(ENV_FILE)
	@printf 'BACKEND_PATH=$(CURDIR)/backend\n'  >> $(ENV_FILE)
	@printf 'FRONTEND_PATH=$(CURDIR)/frontend\n' >> $(ENV_FILE)
	@printf 'POSTGRES_PORT=5433\n'               >> $(ENV_FILE)
	@printf 'REDIS_PORT=6379\n'                  >> $(ENV_FILE)
	@printf 'LARAVEL_PORT=8000\n'                >> $(ENV_FILE)
	@printf 'VITE_PORT=5173\n'                   >> $(ENV_FILE)
	@echo "Edite $(ENV_FILE) para alterar portas (necessário se rodar worktrees simultâneas)."

help:
	@echo "finance-app — comandos disponíveis"
	@echo ""
	@echo "  make up            sobe todos os serviços em background"
	@echo "  make down          derruba todos os serviços"
	@echo "  make build         builda imagens (laravel)"
	@echo "  make rebuild       builda imagens sem cache"
	@echo "  make bash-laravel  shell dentro do container laravel"
	@echo "  make bash-vite     shell dentro do container vite"
	@echo "  make migrate       roda migrations no laravel"
	@echo "  make fresh         drop + migrate + seed (cuidado, apaga dados)"
	@echo "  make seed          roda seeders"
	@echo "  make test          roda phpunit"
	@echo "  make logs          tail -f de todos os logs"
	@echo "  make worker-logs   tail -f do worker e scheduler"
	@echo "  make ps            lista containers"
	@echo "  make key           gera APP_KEY"
	@echo ""
	@echo "  Worktree: $(WORKTREE_NAME)"
	@echo "  Project:  finance-$(WORKTREE_NAME)"
	@echo "  Compose:  $(COMPOSE_FILE)"

up: $(ENV_FILE)
	$(DC) up -d

down: $(ENV_FILE)
	$(DC) down

build: $(ENV_FILE)
	$(DC) build

rebuild: $(ENV_FILE)
	$(DC) build --no-cache

bash-laravel: $(ENV_FILE)
	$(DC) exec laravel sh

bash-vite: $(ENV_FILE)
	$(DC) exec vite sh

migrate: $(ENV_FILE)
	$(DC) exec laravel php artisan migrate

fresh: $(ENV_FILE)
	$(DC) exec laravel php artisan migrate:fresh --seed

seed: $(ENV_FILE)
	$(DC) exec laravel php artisan db:seed

test: $(ENV_FILE)
	$(DC) exec laravel php artisan test

logs: $(ENV_FILE)
	$(DC) logs -f

worker-logs: $(ENV_FILE)
	$(DC) logs -f worker scheduler

ps: $(ENV_FILE)
	$(DC) ps

key: $(ENV_FILE)
	$(DC) exec laravel php artisan key:generate
