.PHONY: help install up down restart logs backend-shell frontend-shell mysql-shell migrate seed test clean build

# Colors for output
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[0;33m
NC := \033[0m # No Color

help: ## Show this help message
	@echo '$(BLUE)MySaaS Docker Commands:$(NC)'
	@echo ''
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ''

install: ## Install and setup the project
	@echo '$(BLUE)Installing MySaaS...$(NC)'
	@cp -n backend/.env.docker backend/.env 2>/dev/null || true
	@cp -n frontend/.env.docker frontend/.env 2>/dev/null || true
	@docker-compose up -d
	@echo '$(YELLOW)Waiting for services to start...$(NC)'
	@sleep 10
	@docker-compose exec backend composer install
	@docker-compose exec backend php artisan key:generate
	@docker-compose exec backend php artisan migrate
	@docker-compose exec frontend npm install
	@echo '$(GREEN)Installation complete!$(NC)'
	@echo ''
	@echo 'Access the application at:'
	@echo '  - Frontend: http://localhost:5173'
	@echo '  - Backend: http://localhost:8000'
	@echo '  - PhpMyAdmin: http://localhost:8080'
	@echo '  - MailHog: http://localhost:8025'

up: ## Start all containers
	@echo '$(BLUE)Starting containers...$(NC)'
	@docker-compose up -d
	@echo '$(GREEN)Containers started!$(NC)'

down: ## Stop all containers
	@echo '$(BLUE)Stopping containers...$(NC)'
	@docker-compose down
	@echo '$(GREEN)Containers stopped!$(NC)'

restart: ## Restart all containers
	@echo '$(BLUE)Restarting containers...$(NC)'
	@docker-compose restart
	@echo '$(GREEN)Containers restarted!$(NC)'

logs: ## Show logs from all containers
	@docker-compose logs -f

logs-backend: ## Show backend logs
	@docker-compose logs -f backend

logs-frontend: ## Show frontend logs
	@docker-compose logs -f frontend

backend-shell: ## Access backend container shell
	@docker-compose exec backend bash

frontend-shell: ## Access frontend container shell
	@docker-compose exec frontend sh

mysql-shell: ## Access MySQL shell
	@docker-compose exec mysql mysql -u mysaas_user -pmysaas_password mysaas_central

redis-shell: ## Access Redis CLI
	@docker-compose exec redis redis-cli

migrate: ## Run database migrations
	@echo '$(BLUE)Running migrations...$(NC)'
	@docker-compose exec backend php artisan migrate
	@echo '$(GREEN)Migrations complete!$(NC)'

migrate-fresh: ## Fresh migration (WARNING: drops all tables)
	@echo '$(YELLOW)WARNING: This will drop all tables!$(NC)'
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker-compose exec backend php artisan migrate:fresh; \
		echo '$(GREEN)Fresh migration complete!$(NC)'; \
	fi

seed: ## Seed the database
	@echo '$(BLUE)Seeding database...$(NC)'
	@docker-compose exec backend php artisan db:seed
	@echo '$(GREEN)Seeding complete!$(NC)'

test: ## Run backend tests
	@echo '$(BLUE)Running tests...$(NC)'
	@docker-compose exec backend php artisan test

test-frontend: ## Run frontend tests
	@echo '$(BLUE)Running frontend tests...$(NC)'
	@docker-compose exec frontend npm run test

clean: ## Remove all containers, volumes, and images
	@echo '$(YELLOW)WARNING: This will remove all containers, volumes, and images!$(NC)'
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker-compose down -v --rmi all; \
		echo '$(GREEN)Cleanup complete!$(NC)'; \
	fi

build: ## Rebuild all containers
	@echo '$(BLUE)Rebuilding containers...$(NC)'
	@docker-compose build --no-cache
	@docker-compose up -d
	@echo '$(GREEN)Rebuild complete!$(NC)'

ps: ## Show container status
	@docker-compose ps

cache-clear: ## Clear Laravel cache
	@docker-compose exec backend php artisan cache:clear
	@docker-compose exec backend php artisan config:clear
	@docker-compose exec backend php artisan route:clear
	@docker-compose exec backend php artisan view:clear
	@echo '$(GREEN)Cache cleared!$(NC)'

tinker: ## Open Laravel Tinker
	@docker-compose exec backend php artisan tinker

composer-install: ## Install backend dependencies
	@docker-compose exec backend composer install

npm-install: ## Install frontend dependencies
	@docker-compose exec frontend npm install

backup-db: ## Backup central database
	@echo '$(BLUE)Backing up database...$(NC)'
	@docker-compose exec mysql mysqldump -u mysaas_user -pmysaas_password mysaas_central > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo '$(GREEN)Backup complete!$(NC)'

restore-db: ## Restore database from backup (usage: make restore-db FILE=backup.sql)
	@echo '$(BLUE)Restoring database...$(NC)'
	@docker-compose exec -T mysql mysql -u mysaas_user -pmysaas_password mysaas_central < $(FILE)
	@echo '$(GREEN)Restore complete!$(NC)'
