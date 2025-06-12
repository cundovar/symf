## Nom du projet
PROJECT_NAME=cour symfony

up:
	@echo "🚀 Lancement du projet $(PROJECT_NAME)..."
	docker-compose up -d --build

down:
	@echo "🛑 Arrêt et suppression des conteneurs..."
	docker-compose down -v --remove-orphans

restart:
	@echo "🔄 Restart du projet..."
	make down
	make up

logs:
	@echo "📜 Logs de tous les conteneurs..."
	docker-compose logs -f

bash:
	@echo "🖥️  Ouverture d'un shell dans le conteneur PHP..."
	docker-compose exec php bash

composer:
	@echo "📦 Composer install dans PHP..."
	docker-compose exec php composer install

composer-update:
	@echo "📦 Composer update dans PHP..."
	docker-compose exec php composer update

composer-require:
	@read -p "Quel package Composer installer ? " pkg; \
	docker-compose exec php composer require $$pkg

symfo:
	@read -p "Commande Symfony ? (ex: cache:clear) " cmd; \
	docker-compose exec php php bin/console $$cmd

migrate:
	@echo "🗄️ création fichier dans dossier migrations..."
	docker-compose exec php php bin/console make:migration
	
migration:
	@echo "🗄️ migration dans base de données..."
	docker-compose exec php php bin/console doctrine:migrations:migrate

database:
	@echo "🗄️ création base de données..."
	docker-compose exec php php bin/console doctrine:database:create
