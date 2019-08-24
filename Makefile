.SILENT:
.PHONY: install test server help cache-clear assets watch db fixtures

.DEFAULT_GOAL = help

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-10s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

composer.lock: composer.json
	composer update

vendor: composer.lock
	composer install

yarn.lock: package.json
	yarn upgrade

node_modules: yarn.lock
	yarn install

assets: node_modules ## Compilation des assets avec Webpack Encore
	yarn run dev

watch: node_modules ## Compilation en continu des assets avec Webpack Encore
	yarn run dev-server

db: vendor ## Création de la base de donnée et des migrations
	-bin/console doctrine:database:drop --if-exists --force
	-bin/console doctrine:database:create --if-not-exists
	bin/console doctrine:migrations:migrate --no-interaction

fixtures: ## Création des fixtures
	bin/console doctrine:fixtures:load --no-interaction

install: db assets ## Installe les dépendances et les assets

test: vendor ## Lance les tests unitaires et fonctionnels
	bin/phpunit

cache-clear: vendor ## Nettoie le cache
	bin/console cache:clear