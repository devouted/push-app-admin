# Konwencja modułów i uprawnień

## Struktura modułów

```
src/
├── Module/
│   ├── User/
│   │   ├── Controller/
│   │   ├── Entity/
│   │   ├── Repository/
│   │   └── Service/
│   ├── Customer/
│   │   ├── Controller/
│   │   ├── Entity/
│   │   ├── Repository/
│   │   └── Service/
```

## Konwencja uprawnień

### Role
- `ROLE_USER` - podstawowy użytkownik
- `ROLE_ADMIN` - administrator
- `ROLE_MODULE_NAME_VIEW` - odczyt modułu
- `ROLE_MODULE_NAME_EDIT` - edycja modułu
- `ROLE_MODULE_NAME_DELETE` - usuwanie w module

### Voter Pattern
- Każdy moduł może mieć własny Voter w `src/Security/Voter/`
- Voter sprawdza uprawnienia na poziomie encji
- Przykład: `UserVoter`, `CustomerVoter`

### Atrybuty uprawnień
- `VIEW` - odczyt
- `EDIT` - edycja
- `DELETE` - usuwanie
- `CREATE` - tworzenie

## Przykład użycia

```php
// W kontrolerze
#[IsGranted('ROLE_USER_VIEW')]
public function list() {}

// Z Voter
$this->denyAccessUnlessGranted('EDIT', $user);
```
