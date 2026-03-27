# Wspólne zasady pracy z Redmine

## Ogólne zasady

- Przed każdą operacją mutującą sprawdź tożsamość przez `redmine_get_current_user`
- Upewnij się, że używasz właściwego serwera MCP dla danego kontekstu
- Nigdy nie generuj komend shell/curl do komunikacji z Redmine — używaj wyłącznie MCP tools

## KRYTYCZNE: Separacja ról i workflow

- ❌ PM NIE MOŻE zmieniać statusów zadań DEV (Backlog → In Progress → Code Review)
- ❌ QA NIE MOŻE zmieniać statusów zadań DEV (Backlog → In Progress → Code Review)
- ❌ DEV NIE MOŻE zamykać zadań (Done) - to wyłącznie rola QA
- ❌ PM NIE MOŻE zamykać tasków (Done) - to wyłącznie rola QA
- ⚠️ Jeśli narzędzia danego kontekstu nie działają - użyj dostępnych narzędzi z innego kontekstu TYLKO jeśli workflow na to pozwala
- ✅ Każda rola używa WYŁĄCZNIE swoich narzędzi MCP dla swoich operacji

## Dostępne serwery MCP

1. **@redminePMAgent** - dla kontekstu Product Managera
2. **@redmineDeveloperAgent** - dla kontekstu Developera  
3. **@redmineQAAgent** - dla kontekstu QA

## Jak używać kontekstów

Użytkownik wskaże kontekst pracy poprzez:
- **Skróty:** `PM`, `DEV`, `QA` w zapytaniu (np. "PM: stwórz Story...", "jako DEV weź task...")
- Użycie odpowiedniego @agenta w zapytaniu
- Typ operacji (tworzenie Story → PM, implementacja → DEV, testowanie → QA)

## Rozpoznawanie kontekstu

- `PM` / `pm` / "jako PM" / "w kontekście PM" → @redminePMAgent
- `DEV` / `dev` / "jako DEV" / "w kontekście DEV" → @redmineDeveloperAgent
- `QA` / `qa` / "jako QA" / "w kontekście QA" → @redmineQAAgent
