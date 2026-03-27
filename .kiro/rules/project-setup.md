# Konfiguracja projektu Symfony API CRM

## Dostęp do aplikacji

- **Frontend**: http://localhost (port 80)
- **API**: http://api.localhost (port 80, subdomena)
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

⚠️ **WAŻNE**: API jest dostępne pod subdomeną `api.localhost`, NIE pod `localhost/api`

## Kontenery Docker

- `symfony-sceleton-apache-1` - Apache + PHP 8.4 (Symfony API)
- `symfony-sceleton-frontend-1` - Node.js + Vite (React frontend)
- `symfony-sceleton-mysql-1` - MariaDB 11
- `symfony-sceleton-redis-1` - Redis Alpine

## Apache VirtualHosts

Konfiguracja w `/config/apache.conf`:

1. **localhost** - proxy do frontendu (Vite dev server na porcie 5173)
2. **api.localhost** - Symfony API (DocumentRoot: `/var/www/html/public`)

## Struktura katalogów

- `/app` - kod Symfony
- `/app/src` - kod źródłowy PHP
- `/app/tests` - testy PHPUnit
- `/app/translations` - pliki tłumaczeń (messages, validators, security)
- `/app/config/packages` - konfiguracja Symfony
- `/frontend` - kod React

## Uruchamianie testów

```bash
# Wszystkie testy
docker exec symfony-sceleton-apache-1 php bin/phpunit tests/

# Konkretny test
docker exec symfony-sceleton-apache-1 php bin/phpunit tests/Controller/AuthControllerTest.php
```

## Dostęp do bazy danych

- User: symfony
- Password: symfony
- Database: symfony

## Przykłady wywołań API

```bash
# Lista dostępnych języków
curl http://api.localhost/api/dictionaries/locales

# Tłumaczenia dla języka
curl http://api.localhost/api/dictionaries/translations/en
curl http://api.localhost/api/dictionaries/translations/pl

# Health check
curl http://api.localhost/api/health
```
