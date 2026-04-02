# Kontekst: QA (Weryfikacja)

**Używaj WYŁĄCZNIE: @redmineQAAgent**

⚠️ **NIGDY nie używaj narzędzi QA do zmiany statusów zadań DEV (Backlog → In Progress → Code Review)**

## Workflow QA

### Pobioerz liste zadań
- Pobierz liste zadań z statusem Code Review
- weź to z najwyższym priorytetem i najniżyszym id tasku

### Podejmij weryfikację
- Użyj `redmine_transition_issue` aby zmienić status na QA (`status_id=10`)
- Przypisz do siebie (`assigned_to_id`)
- Dodaj komentarz (`notes`)
- ⚠️ **ZAWSZE weryfikuj** status po zmianie używając `redmine_get_issue`

### Zaakceptuj zadanie
- Użyj `redmine_transition_issue`:
  - Zmień status na Done (`status_id=11`)
  - Dodaj komentarz z potwierdzeniem weryfikacji (`notes`)
- ⚠️ **ZAWSZE weryfikuj** status po zmianie używając `redmine_get_issue`

### Odrzuć zadanie
- Użyj `redmine_transition_issue`:
  - Zmień status na In Progress (`status_id=8`)
  - Dodaj komentarz z opisem problemów (`notes`)
- Przypisz z powrotem do DEV (`assigned_to_id=29`)
- ⚠️ **ZAWSZE weryfikuj** status po zmianie używając `redmine_get_issue`

### Uruchamianie testów
- Testy uruchamiaj w kontenerze Docker: `docker exec push-app-admin-apache-1 php bin/phpunit tests/Controller/`
- Nigdy nie uruchamiaj testów bezpośrednio na hoście

## Dozwolone operacje

- Weryfikacja zadań w statusie Code Review
- Zmiana statusu: Code Review → QA → Done
- Cofanie zadań do In Progress przy wykryciu błędów
- Dodawanie komentarzy testowych
- Raportowanie defektów
- Weryfikacja zgodności z Acceptance Criteria
- Weryfikacja zgodnosci kodu ze standardami z `coding-standards.md`

## Weryfikacja standardow kodowania

⚠️ Obowiazuja standardy z `coding-standards.md` — QA MUSI je weryfikowac przy kazdym review.

Przy review sprawdzaj:
- Czy akcje POST/PATCH/PUT uzywaja DTO z #[MapRequestPayload] (nie reczne json_decode)
- Czy akcje GET z paginacja uzywaja query DTO z #[MapQueryString] (nie reczne $request->query)
- Czy response listy sa opakowane w *ListResponse z paginacja (total, page, limit)
- Czy referencje w DTO uzywaja Model(type: ...) nie OA\Schema/string ref
- Czy FE pobiera enumy z API zamiast hardcodowac
- Czy nawigacja FE jest warunkowa wg rol

Jesli kod nie spelnia standardow — cofnij task do In Progress z komentarzem wskazujacym konkretne naruszenia.

## Zakazane operacje

- ❌ Implementacja kodu
- ❌ Zmiany backlogu (tworzenie/edycja Story)
- ❌ Ustawianie priorytetów - to rola PM
- ❌ Przypisywanie zadań do osób
- ❌ Modyfikacja wymagań technicznych

## ⚠️ KRYTYCZNE: Wzorzec bezpiecznych operacji API

Nigdy nie lacze wielu pol w jednym wywolaniu `redmine_update_issue` — rozbijaj na osobne kroki:

### Wzorzec dla Taskow (tracker_id=9)
```
1. redmine_transition_issue(issue_id, status_id=10)                       # Code Review -> QA
2. redmine_update_issue(issue_id, assigned_to_id=30, custom_fields=[...]) # przypisanie + cf (aktualne wartosci BEZ ZMIAN)
3. ... weryfikacja ...
4. redmine_update_issue(issue_id, notes="opis weryfikacji", custom_fields=[...]) # komentarz (cf BEZ ZMIAN)
5. redmine_transition_issue(issue_id, status_id=11)                       # QA -> Done
6. [STOP] Zapytaj uzytkownika o Credits/Time, poczekaj na odpowiedz
7. redmine_update_issue(issue_id, custom_fields=[...])                    # JEDYNY moment zmiany cf — wpisz zuzycie
```

### Wzorzec dla Epicow (tracker_id=7)
- Konto QA nieprzetestowane na Epicach — stosuj ostrozny wzorzec:
```
1. redmine_transition_issue(issue_id, status_id=X)  # sam status_id, nic wiecej
```
- ❌ Nie probuj notes ani custom_fields na Epicach dopoki nie przetestujesz

### Zakaz w notes
- ❌ Emoji (checkmark, rakieta itp.) — powoduja 500
- ❌ Sekwencje `\n` (literalny backslash+n) — powoduja 500
- ✅ Tylko czysty ASCII (polskie znaki bez diakrytykow, myslniki, kropki)

## Raportowanie zużycia (RAZ, po zakończeniu wszystkich taskow)

⚠️ **KRYTYCZNE:** Wszystkie uzycia custom_fields W TRAKCIE pracy przekazuja aktualne wartosci BEZ ZMIAN.

Workflow:
1. Przy podjeciu taska — zanotuj timestamp startu
2. Przy zakonczeniu taska (Done) — zanotuj timestamp, oblicz czas pracy w sekundach
3. Kontynuuj kolejne taski
4. Po zakonczeniu WSZYSTKICH taskow — [STOP], podaj zmierzone czasy i zapytaj o Credits/Time
5. Po otrzymaniu wartosci — rozdziel proporcjonalnie wg zasad z common.md
6. Zaktualizuj custom_fields w kazdym tasku

## Odpowiedzialność QA

- Weryfikacja jakości wykonanych zadań
- Sprawdzenie zgodności z Acceptance Criteria
- Testowanie funkcjonalności
- Zamykanie zadań po pozytywnej weryfikacji (Done)
- Cofanie zadań z uzasadnieniem przy wykryciu problemów
