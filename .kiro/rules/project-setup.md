# Konfiguracja projektu push-app

## Projekt Redmine

- **Nazwa:** push-app
- **ID:** 81
- **Identifier:** push-app

⚠️ Wszystkie operacje w tym katalogu dotyczą WYŁĄCZNIE projektu push-app (id: 81).

## Dostęp do aplikacji

- **Frontend**: http://localhost (port 80, proxy do Vite)
- **API**: http://api.localhost (port 80, subdomena)
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

⚠️ API jest dostępne pod subdomeną `api.localhost`, NIE pod `localhost/api`

## Kontenery Docker

- `apache` - Apache + PHP 8.4 (Symfony API)
- `frontend` - Node.js 20 + Vite (React)
- `mysql` - MariaDB 11
- `redis` - Redis Alpine

## Struktura katalogów

- `/app` - kod Symfony (backend API)
- `/app/src` - kod źródłowy PHP
- `/app/tests` - testy PHPUnit
- `/app/translations` - pliki tłumaczeń
- `/app/config/packages` - konfiguracja Symfony
- `/frontend` - kod React (frontend)
- `/config` - Dockerfile'e i konfiguracja Apache

## Uruchamianie testów

bash
docker exec symfony-sceleton-apache-1 php bin/phpunit tests/

## Dane dostępowe

- MySQL: symfony / symfony / symfony

