.PHONY: help up down build rebuild bash-laravel bash-vite migrate fresh seed test logs ps key

help:
	@echo "finance-app — comandos disponiveis"
	@echo ""
	@echo "  make up            sobe todos os servicos em background"
	@echo "  make down          derruba todos os servicos"
	@echo "  make build         builda imagens (laravel)"
	@echo "  make rebuild       builda imagens sem cache"
	@echo "  make bash-laravel  shell dentro do container laravel"
	@echo "  make bash-vite     shell dentro do container vite"
	@echo "  make migrate       roda migrations no laravel"
	@echo "  make fresh         drop + migrate + seed (cuidado, apaga dados)"
	@echo "  make seed          roda seeders"
	@echo "  make test          roda phpunit"
	@echo "  make logs          tail -f de todos os logs"
	@echo "  make ps            lista containers"
	@echo "  make key           gera APP_KEY"

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

rebuild:
	docker compose build --no-cache

bash-laravel:
	docker compose exec laravel sh

bash-vite:
	docker compose exec vite sh

migrate:
	docker compose exec laravel php artisan migrate

fresh:
	docker compose exec laravel php artisan migrate:fresh --seed

seed:
	docker compose exec laravel php artisan db:seed

test:
	docker compose exec laravel php artisan test

logs:
	docker compose logs -f

ps:
	docker compose ps

key:
	docker compose exec laravel php artisan key:generate
