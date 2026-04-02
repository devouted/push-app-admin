# Standardy kodowania — push-app

Obowiazuja wszystkie role: DEV stosuje, QA weryfikuje.

## BE: Request DTO (POST/PATCH/PUT)

Kazda akcja przyjmujaca body MUSI uzywac DTO z `#[MapRequestPayload]`.

```php
// DOBRZE
public function block(string $id, #[MapRequestPayload] BlockChannelRequest $request): JsonResponse

// ZLE — reczne parsowanie JSON
$data = json_decode($request->getContent(), true) ?? [];
$title = $data['title'] ?? 'default';
```

Request DTO:
- `readonly class` z atrybutem `#[OA\Schema]`
- Pola z `#[OA\Property]` i walidacja `#[Assert\...]`
- W kontrolerze OA\RequestBody uzywa `new Model(type: XxxRequest::class)`, nigdy recznego `OA\JsonContent` z properties

## BE: Query DTO (GET z parametrami)

Akcje GET z paginacja/filtrami MUSZA uzywac DTO z `#[MapQueryString]`.

```php
// DOBRZE
public function list(#[MapQueryString] AdminChannelListQuery $query): JsonResponse

// ZLE — reczne czytanie z Request
$page = max(1, $request->query->getInt('page', 1));
$limit = min(100, max(1, $request->query->getInt('limit', 20)));
```

- Paginacja (page, limit) i filtry w DTO z walidacja Assert
- Nie dodawaj recznych `#[OA\Parameter]` — dokumentacja generuje sie z DTO
- Bazowe DTO `PaginationQuery` z page/limit, rozszerzaj dla filtrow

## BE: Response DTO

- Kazdy response przez `$this->response($dto)`, nigdy `$this->json()`
- `readonly class` implementujacy `ResponseDtoInterface`
- Atrybut `#[OA\Schema(schema: 'NazwaResponse')]`
- Pola z `#[OA\Property(example: ...)]`

## BE: Listy i paginacja

Kazdy endpoint zwracajacy liste MUSI:
1. Miec paginacje (page, limit, total)
2. Zwracac opakowane w `*ListResponse` DTO
3. Referencje do elementow listy przez `Model`, nie `Schema`

```php
// DOBRZE
#[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: ChannelListItemResponse::class)))]
public array $items,

// ZLE — OA\Schema ref
#[OA\Property(type: 'array', items: new OA\Items(ref: new OA\Schema(schema: 'ChannelListItemResponse')))]

// ZLE — string ref
#[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/ChannelListItemResponse'))]
```

## BE: OpenAPI — parametry path

Nie dodawaj recznych `#[OA\Parameter]` dla parametrow ktore sa w route — generuja sie automatycznie.

```php
// ZLE — zbedny OA\Parameter
#[Route('/{id}', methods: ['GET'])]
#[OA\Parameter(name: 'id', in: 'path', schema: new OA\Schema(type: 'string', format: 'uuid'))]

// DOBRZE — wystarczy route
#[Route('/{id}', methods: ['GET'])]
```

## BE: Kontrolery — ogolne

- Dziedzicz po `DefaultController`
- Dodawaj `summary` i `description` w atrybutach OA\Get/Post/Patch/Delete
- Uzywaj `new Model(type: XxxResponse::class)` w `#[OA\Response]`
- Uzywaj `#[OA\Tag(name: '...')]` na kazdej akcji

## FE: Enumy i slowniki

Listy wartosci (role, statusy, locale) MUSZA byc pobierane z API `/api/dictionaries/*`. Nie hardcoduj.

```js
// ZLE — hardcoded
const AVAILABLE_ROLES = [
    { value: "ROLE_USER", label: "User" },
    { value: "ROLE_ADMIN", label: "Admin" },
];

// DOBRZE — z API + fallback
const [roles, setRoles] = useState(FALLBACK_ROLES);
useEffect(() => {
    api.get('/dictionaries/roles').then(r => setRoles(r.data.roles));
}, []);
```

## FE: Nawigacja i widoki wg rol

Elementy nawigacji i widoki MUSZA byc renderowane warunkowo na podstawie rol uzytkownika z kontekstu auth. Uzytkownik nie moze widziec linkow/stron do ktorych nie ma dostepu.

- ROLE_ADMIN: Users, Moderation, Clients
- ROLE_CLIENT: Channels, Notifications
- Dashboard: wszyscy zalogowani
