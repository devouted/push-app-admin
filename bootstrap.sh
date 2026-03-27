#!/bin/bash

set -e

echo "🚀 Bootstrap Symfony API CRM"
echo "=============================="

echo "📝 Copying .env.example to .env..."
cp -n app/.env.example app/.env || true

echo "📦 Stopping existing containers..."
docker compose down -v

echo "🔨 Building Docker images..."
docker compose build --no-cache

echo "🚀 Starting containers..."
docker compose up -d

echo "⏳ Waiting for containers to be ready..."
sleep 5

echo "📚 Installing Composer dependencies..."
docker compose exec -T apache composer install --working-dir=/var/www/html

echo "🔄 Running composer update to sync lock file..."
docker compose exec -T apache composer update --working-dir=/var/www/html --no-interaction --no-audit

echo "🗄️  Running database migrations..."
docker compose exec -T apache php /var/www/html/bin/console doctrine:migrations:migrate --no-interaction

echo "🔐 Generating JWT keys..."
docker compose exec -T apache php /var/www/html/bin/console lexik:jwt:generate-keypair --skip-if-exists

echo "✅ Bootstrap completed!"
echo ""
echo "Access the application at: http://localhost"
echo "MySQL: localhost:3306 (symfony/symfony/symfony)"
echo "Redis: localhost:6379"
