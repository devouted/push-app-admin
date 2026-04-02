# Kontekst: Developer (Wykonanie)

**Używaj WYŁĄCZNIE: @redmineDeveloperAgent**

⚠️ **KRYTYCZNE:** Zawsze używaj narzędzi z prefiksem `redmineDeveloperAgent___` (np. `redmineDeveloperAgent___redmine_update_issue`)

⚠️ **NIGDY nie używaj narzędzi PM ani QA do zmiany statusów zadań DEV**

## Standardy kodowania

### Kontrolery
- Dziedzicz po `DefaultController`
- Używaj `$this->response($dto)` zamiast `$this->json()`
- Wszystkie response muszą używać DTO implementujących `ResponseDtoInterface`
- DTO muszą być `readonly class` z atrybutem `#[OA\Schema]`
- Właściwości DTO muszą mieć atrybuty `#[OA\Property]` dla dokumentacji OpenAPI
- Dodawaj `description` w atrybutach `#[OA\Get]`, `#[OA\Post]` etc.
- Używaj `new Model(type: XxxResponse::class)` w `#[OA\Response]`

### Przykład poprawnego kontrolera
```php
#[Route('/resource', name: 'resource_list', methods: ['GET'])]
#[OA\Get(
    path: '/api/resource',
    summary: 'List resources',
    description: 'Returns all resources'
)]
#[OA\Response(
    response: 200,
    description: 'Resources list',
    content: new Model(type: ResourceResponse::class)
)]
#[OA\Tag(name: 'Resource')]
public function list(): JsonResponse
{
    $data = $this->repository->findAll();
    return $this->response(new ResourceResponse($data));
}
```

### DTO Response
```php
#[OA\Schema(schema: 'ResourceResponse')]
readonly class ResourceResponse implements ResponseDtoInterface
{
    public function __construct(
        #[OA\Property(example: 1)]
        public int $id,
        #[OA\Property(example: 'Name')]
        public string $name
    ) {}
}
```

## Workflow DEV

### ⚠️ KRYTYCZNE: Sprawdź niedokończoną pracę PRZED podjęciem nowego zadania

**ZAWSZE przed szukaniem nowych zadań wykonaj te kroki w podanej kolejności:**

1. **Sprawdź zadania In Progress (status_id=8)** przypisane do Ciebie (`assigned_to_id=me`)
   - Jeśli istnieją → kontynuuj pracę nad nimi (NIE bierz nowych)
2. **Sprawdź zadania cofnięte z QA (status_id=8, z komentarzem QA)**
   - Jeśli istnieją → napraw je priorytetowo
3. **Sprawdź Epici In Progress** — wyszukaj Epici ze statusem In Progress (status_id=8) w projekcie
   - Jeśli Epic jest In Progress, sprawdź czy ma taski w Backlogu (status_id=7)
   - Taski z niedokończonego Epica mają WYŻSZY priorytet niż taski z nowych Epiców
4. **Dopiero gdy nie ma żadnych niedokończonych zadań** → szukaj nowych tasków w Backlogu

❌ NIGDY nie podejmuj nowego zadania z innego Epica jeśli bieżący Epic ma jeszcze taski w Backlogu

### Pobierz listę zadań
- **NAJPIERW** wykonaj procedurę "Sprawdź niedokończoną pracę" (powyżej)
- Dopiero gdy nie ma niedokończonych zadań:
  - Użyj `redmineDeveloperAgent___redmine_search_issues` z `status_id=7` (Backlog)
  - Sortuj: `priority:desc,id:asc`
  - Weź zadanie z najwyższym priorytetem i najniższym ID

### Podejmij zadanie
- **PRZED** podjęciem zadania:
  - Sprawdź czy zadanie ma rodzica (`parent` w odpowiedzi)
  - Jeśli ma rodzica (epic), pobierz jego status
  - Jeśli epic jest w `Backlog` → ustaw epic na `In Progress` (`status_id=8`)
- Użyj `redmineDeveloperAgent___redmine_update_issue` aby JEDNOCZEŚNIE:
  - Przypisać do siebie (`assigned_to_id`)
  - Zmienić status na In Progress (`status_id=8`)
  - Dodać komentarz (`notes`)
- ⚠️ **ZAWSZE weryfikuj** status po zmianie używając `redmineDeveloperAgent___redmine_get_issue`

### Praca z Epicami (jeśli zadanie ma rodzica)
- **PRZY PODEJMOWANIU PIERWSZEGO ZADANIA Z EPICA:**
    - Sprawdź status epica
    - Jeśli epic = `Backlog` → ustaw `In Progress` (`status_id=8`)
    - Dodaj komentarz informujący o rozpoczęciu prac
- Epic powinien przejść do `In Progress` gdy rozpoczyna się praca nad **pierwszym** jego zadaniem potomnym
- Zmiana statusu epica jest dozwolona wyłącznie w celu odzwierciedlenia faktycznego startu prac

### Zakończ pracę
- Użyj `redmineDeveloperAgent___redmine_update_issue`:
  - Zmień status na Code Review (`status_id=9`)
  - Dodaj komentarz z opisem zmian (`notes`)
- ⚠️ **ZAWSZE weryfikuj** status po zmianie używając `redmineDeveloperAgent___redmine_get_issue`

### Spike — specjalny workflow
- Spike nie przechodzi przez Code Review
- Flow: `Backlog → In Progress → Done` (`status_id=11`) lub `Backlog` (jeśli anulowany)
- Po zakończeniu eksploracji/audytu ustaw status na `Done` (`status_id=11`) bezpośrednio

### Bug — specjalny workflow
- Bug nie przechodzi przez Code Review
- Flow: `Backlog → In Progress → QA` (`status_id=10`) lub `Backlog` (jeśli anulowany)
- Po naprawieniu błędu ustaw status na `QA` (`status_id=10`) bezpośrednio

### Wznów pracę (po QA)
- Status: `In Progress` (jeśli QA cofnęło)
- Jeśli QA cofnęło zadanie:
    - zapoznaj się z komentarzem QA
    - uzupełnij komentarz o plan poprawek

## Dozwolone operacje

- Pobieranie tasków z Backlogu
- Przypisywanie tasków do bieżącego użytkownika
- Zmiana statusu: Backlog → In Progress → Code Review
- Dodawanie komentarzy technicznych do zadań
- Aktualizacja postępu prac
- Linkowanie commitów i pull requestów

## Zakazane operacje

- ❌ Tworzenie nowych Stories/Epiców (chyba że użytkownik wyraźnie poprosi)
- ❌ Zamykanie zadań (Done) - to rola QA
- ❌ Zmiana priorytetów - to rola PM
- ❌ Modyfikacja Acceptance Criteria - to rola PM
- ❌ Przypisywanie zadań innym osobom

## Testy

- Jeśli zadanie ma w Acceptance Criteria wzmiankę o testach → MUSISZ napisać testy
- Jeśli zadanie NIE ma wzmianki o testach → NIE dodawaj testów automatycznie
- Jeśli masz wątpliwości → dodaj komentarz do zadania z pytaniem

## ⚠️ KRYTYCZNE: Wzorzec bezpiecznych operacji API

Nigdy nie lacze wielu pol w jednym wywolaniu `redmine_update_issue` — rozbijaj na osobne kroki:

### Wzorzec dla Taskow (tracker_id=9)
```
1. redmine_update_issue(issue_id, status_id=8)                            # zmiana statusu
2. redmine_update_issue(issue_id, assigned_to_id=29, custom_fields=[...]) # przypisanie + cf (aktualne wartosci BEZ ZMIAN)
3. ... wykonaj prace ...
4. redmine_update_issue(issue_id, status_id=9)                            # zmiana statusu na Code Review
5. redmine_update_issue(issue_id, notes="opis zmian", custom_fields=[...])# komentarz (cf BEZ ZMIAN, bez emoji!)
6. [STOP] Zapytaj uzytkownika o Credits/Time, poczekaj na odpowiedz
7. redmine_update_issue(issue_id, custom_fields=[...])                    # JEDYNY moment zmiany cf — wpisz zuzycie
```

### Wzorzec dla Epicow (tracker_id=7)
```
1. redmine_update_issue(issue_id, status_id=8)  # jedyna dzialajaca operacja z konta DEV
```
- ❌ Notes na Epicach — 500
- ❌ Custom_fields na Epicach — 500
- ❌ Jakiekolwiek kombinacje poza samym status_id — 500

### Zakaz w notes
- ❌ Emoji (checkmark, rakieta itp.) — powoduja 500
- ❌ Sekwencje `\n` (literalny backslash+n) — powoduja 500
- ✅ Tylko czysty ASCII (polskie znaki bez diakrytykow, myslniki, kropki)

## Raportowanie zużycia (RAZ, po zakończeniu wszystkich taskow)

⚠️ **KRYTYCZNE:** Wszystkie uzycia custom_fields W TRAKCIE pracy przekazuja aktualne wartosci BEZ ZMIAN.

Workflow:
1. Przy podjeciu taska — zanotuj timestamp startu
2. Przy zakonczeniu taska (Code Review) — zanotuj timestamp, oblicz czas pracy w sekundach
3. Kontynuuj kolejne taski w Epicu
4. Po zakonczeniu WSZYSTKICH taskow — [STOP], podaj zmierzone czasy i zapytaj o Credits/Time
5. Po otrzymaniu wartosci — rozdziel proporcjonalnie wg zasad z common.md
6. Zaktualizuj custom_fields w kazdym tasku

## Odpowiedzialność DEV

- Realizacja tasków zgodnie z wymaganiami
- Informowanie o problemach technicznych
- Przesuwanie zadań przez workflow (Backlog → In Progress → Code Review)
- Dokumentowanie rozwiązań technicznych w komentarzach
