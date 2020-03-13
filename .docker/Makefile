COMPOSE_FILE := -f .docker/docker-compose.yml
COMPOSE_DIR := --project-directory .docker
COMPOSE := docker-compose $(COMPOSE_FILE) $(COMPOSE_DIR)
SYMFONY := php bin/console

.DEFAULT_GOAL:=help

.PHONY: help

help:  ## Display this help
	@awk 'BEGIN {FS = ":.*##"; printf "\nUsage:\n  make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)


##@ Docker

.PHONY: build
build: ## Build docker images
	$(COMPOSE) build

.PHONY: up
up: ## Start docker containers
	$(COMPOSE) up -d

.PHONY: down
down: ## Stop and remove docker containers/networks
	$(COMPOSE) down

.PHONY: start
start: ## Start docker containers
	$(COMPOSE) start

.PHONY: stop
stop: ## Stop docker containers
	$(COMPOSE) stop

.PHONY: workspace
workspace: ## Run workspace container as developer
	$(COMPOSE) run workspace bash

##@ Symfony

.PHONY: create
create: ## Create database via doctrine
	$(COMPOSE) exec workspace $(SYMFONY) doctrine:database:create -v

.PHONY: console
console: ## cmd= Execute a command in the symfony console
	$(COMPOSE) exec workspace $(SYMFONY) $(cmd)

.PHONY: entity
entity: ## Make a Symfony entity
	$(COMPOSE) exec workspace $(SYMFONY)  make:entity

.PHONY: migrate
migrate: ## Create migration
	$(COMPOSE) exec workspace $(SYMFONY)  make:migration

.PHONY: persist
persist: ## Persist migration to database
	$(COMPOSE) exec workspace $(SYMFONY)  doctrine:migrations:migrate

.PHONY: fixtures
fixtures: ## Load fixtures into database
	$(COMPOSE) exec workspace $(SYMFONY)  doctrine:fixtures:load

##@ Symfony Webpack / Encore
.PHONY: encore
encore: ## Run encore once
	yarn encore dev

.PHONY: watch
watch: ## Run encore in watch mode
	yarn encore dev --watch

.PHONY: stan
stan: ## Run PHP Stan
	php vendor/bin/phpstan analyse -c phpstan.neon

.PHONY: cs
cs: ## Run PHP Code sniffer
	php vendor/bin/phpcs