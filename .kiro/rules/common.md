# Wspólne zasady pracy z Redmine

## ⚠️ KRYTYCZNE: Powiązanie katalogu z projektem Redmine

- Ten katalog (`push-app-admin`) jest powiązany WYŁĄCZNIE z projektem **push-app** (Redmine project_id: **81**)
- ❌ NIGDY nie wykonuj operacji Redmine dla innych projektów (np. `symfony api crm`, id: 80)
- ❌ NIGDY nie twórz, nie edytuj, nie przeglądaj zadań w projektach innych niż `push-app`
- ✅ Wszystkie operacje Redmine (search, create, update, transition) MUSZĄ używać `project_id: 81`
- ✅ Przy każdym wyszukiwaniu zadań dodawaj filtr `project_id: 81`

## Ogólne zasady

- Przed każdą operacją mutującą sprawdź tożsamość przez `redmine_get_current_user`
- Upewnij się, że używasz właściwego serwera MCP dla danego kontekstu
- Nigdy nie generuj komend shell/curl do komunikacji z Redmine — używaj wyłącznie MCP tools

## Zasady korzystania z fs_read

- Tryb `Search` wymaga sciezki do PLIKU, nie katalogu. Sluzy do przeszukiwania zawartosci pliku.
- Tryb `Directory` sluzy do listowania zawartosci katalogu (szukanie plikow po nazwie).
- Aby znalezc plik po nazwie: uzyj `Directory` z odpowiednia glebia, potem `Line` aby go odczytac.

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


## ⚠️ KRYTYCZNE: Custom Fields przy operacjach Redmine

Przy KAŻDYM `redmine_update_issue` i `redmine_create_issue` MUSISZ przekazać `custom_fields`, inaczej Redmine zwróci 500.

Zasada: **przekazuj aktualne wartosci BEZ ZMIAN** — pobierz je z `redmine_get_issue` i wstaw jak sa.
Jedyny moment zmiany wartosci to raportowanie zuzycia po otrzymaniu Credits/Time od uzytkownika.

Przy tworzeniu nowego zadania uzyj wartosci domyslnych:
```json
"custom_fields": [{"id": 8, "value": "0"}, {"id": 9, "value": "0"}]
```

## ⚠️ KRYTYCZNE: Raportowanie zuzycia — Credits i Time

### Jednostki

- Custom field 8 ("Zuzyte tokeny") = **Credits** (integer, przeliczone z CLI * 100)
- Custom field 9 ("Czas pracy (s)") = **Time w sekundach** (integer)

### Pomiar czasu per task

Agent MUSI mierzyc czas pracy nad kazdym taskiem:
1. Przy podjeciu taska — zanotuj timestamp z kontekstu (np. `22:35:58`)
2. Przy zakonczeniu taska — zanotuj timestamp, oblicz roznice w sekundach
3. Zapamietaj czas kazdego taska do konca sesji (np. #2433: 170s, #2434: 500s)

### Kiedy pytac o Credits/Time

Agent pyta uzytkownika o Credits/Time **RAZ** — po zakonczeniu WSZYSTKICH taskow w ramach biezacego polecenia (np. po zakonczeniu pracy nad epickiem).

Wzorzec:
```
Agent: Zakonczylem prace nad taskami w ramach Epica #XXXX.
Zmierzone czasy: #2433: 170s, #2434: 500s, #2435: 220s, #2436: 212s (suma: 1102s).
Podaj wartosci Credits i Time z CLI.
Uzytkownik: Credits: 2.54 • Time: 1150s
```

### Proporcjonalny rozdzial Credits i Time

Po otrzymaniu wartosci z CLI agent rozdziela OBA (Credits i Time) proporcjonalnie do zmierzonych czasow:

Przyklad: zmierzone czasy [170s, 500s, 220s, 212s], suma=1102s. CLI: Credits=2.54, Time=1150s.

| Task | Zmierzony czas | Udzial | Credits (2.54*100=254) | Time (1150s) |
|------|---------------|--------|----------------------|-------------|
| #2433 | 170s | 15.4% | 39 | 177s |
| #2434 | 500s | 45.4% | 115 | 522s |
| #2435 | 220s | 20.0% | 51 | 230s |
| #2436 | 212s | 19.2% | 49 | 221s |
| Suma | 1102s | 100% | 254 | 1150s |

Zasady:
- Credits z CLI przemnoz * 100 PRZED rozdzialem (np. 2.54 -> 254)
- Zaokraglaj do integer (floor), reszte dodaj do ostatniego taska zeby suma sie zgadzala
- Time z CLI rozdzielaj proporcjonalnie (NIE uzywaj zmierzonych czasow jako wartosci koncowych)
- Wartosci sa kumulatywne — dodawaj do istniejacych wartosci z zadania

### Przeliczanie

- Credits z CLI to wartosc dziesietna — PRZED zapisem do Redmine przemnoz * 100 (np. `0.40` -> `40`, `1.25` -> `125`)
- Custom field 8 ("Zuzyte tokeny") przyjmuje TYLKO integer — nigdy nie wpisuj wartosci ulamkowych
- Time wpisuj jako liczbe sekund (integer), np. `"27"`, `"150"`
- Wartosci sa kumulatywne — dodawaj do istniejacych wartosci z zadania

## ⚠️ KRYTYCZNE: Ograniczenia API Redmine (potwierdzone testami)

### Zakaz emoji i znakow specjalnych w notes

- ❌ Emoji (checkmark, rakieta itp.) w `notes` powoduja HTTP 500
- ❌ Sekwencje `\n` (literalny backslash+n) w `notes` powoduja HTTP 500
- ✅ Uzywaj TYLKO czystego ASCII w `notes` (polskie znaki bez diakrytykow, myslniki, kropki)

### Rozbijanie operacji na osobne wywolania

Laczenie wielu pol w jednym uzyciu `redmine_update_issue` czesto powoduje HTTP 500.

Bezpieczny wzorzec — rozbijaj na kroki:
1. `status_id` — osobne wywolanie
2. `assigned_to_id` + `custom_fields` — osobne wywolanie
3. `notes` + `custom_fields` — osobne wywolanie

Kombinacje ktore powoduja 500:
- `status_id` + `notes` + `custom_fields` razem
- `status_id` + `assigned_to_id` + `notes` + `custom_fields` razem

### Ograniczenia per konto na Epicach (tracker_id=7)

- **Konto DEV (id: 29):** na Epicach moze zmienic TYLKO `status_id`. Notes, custom_fields i ich kombinacje zwracaja 500.
- **Konto PM (id: 28):** na Epicach moze zmienic status, custom_fields i notes (ale rozbijaj na osobne wywolania).
- **Konto QA (id: 30):** nieprzetestowane na Epicach — stosuj ostrozny wzorzec (sam status_id).
