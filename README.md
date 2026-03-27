# Symfony API CRM

## Uruchomienie lokalne

### Szybkie uruchomienie
```bash
# Uruchom środowisko
docker compose up -d

# Sprawdź status
docker compose ps

# Zatrzymaj środowisko
docker compose down
```

### Pełna inicjalizacja od zera
```bash
# Uruchom skrypt bootstrap (czyści dane, buduje obrazy, instaluje zależności, wykonuje migracje)
./bootstrap.sh
```

**Uwaga:** Skrypt `bootstrap.sh` usuwa wszystkie dane (volumes) i buduje środowisko od podstaw.

## Co robi bootstrap.sh

Skrypt `bootstrap.sh` wykonuje pełną inicjalizację projektu:

1. **Zatrzymuje i usuwa kontenery** - `docker compose down -v` (usuwa volumes)
2. **Buduje obrazy Docker** - `docker compose build --no-cache`
3. **Uruchamia kontenery** - `docker compose up -d`
4. **Instaluje zależności PHP** - `composer install` w kontenerze Apache
5. **Synchronizuje composer.lock** - `composer update --no-interaction`
6. **Wykonuje migracje bazy danych** - `doctrine:migrations:migrate`

Frontend automatycznie instaluje zależności npm przy starcie kontenera (CMD w Dockerfile.frontend).

## Dostęp

- Aplikacja: http://localhost
- MySQL: localhost:3306
- Redis: localhost:6379

## Dane dostępowe

- MySQL: symfony/symfony/symfony

## Serwer webowy

- Apache 2.4 z mod_php (PHP 8.3)
