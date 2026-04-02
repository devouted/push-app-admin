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

Nazwy kontenerow generowane automatycznie przez Docker Compose na podstawie katalogu projektu:

- `push-app-admin-apache-1` - Apache + PHP 8.4 (Symfony API) — **kontener PHP**
- `push-app-admin-frontend-1` - Node.js 20 + Vite (React)
- `push-app-admin-mysql-1` - MariaDB 11
- `push-app-admin-redis-1` - Redis Alpine

⚠️ NIGDY nie uzywaj starych nazw (`symfony-sceleton-apache-1`, `symfony_php`). Zawsze uzywaj nazw powyzej.

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
docker exec push-app-admin-apache-1 php bin/phpunit tests/

## Trackery (tracker_id)

- 7 = Epic
- 8 = Story
- 9 = Task
- 10 = Bug
- 11 = Spike

## Statusy (status_id)

- 7 = Backlog
- 8 = In Progress
- 9 = Code Review
- 10 = QA
- 11 = Done

## Priorytety (priority_id)

- 3 = Niski
- 4 = Normalny
- 5 = Wysoki
- 6 = Pilny
- 7 = Natychmiastowy

## Pola niestandardowe (custom_fields)

- 8 = Zużyte tokeny (integer) — łączna liczba zużytych tokenów AI
- 9 = Czas pracy (s) (integer) — łączny czas pracy w sekundach

## Dane dostępowe

- MySQL: symfony / symfony / symfony

