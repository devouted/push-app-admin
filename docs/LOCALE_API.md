# Locale Management API

## Endpoints

### GET /api/users/me
Returns current user data including locale.

**Response:**
```json
{
  "id": 1,
  "email": "user@example.com",
  "roles": ["ROLE_USER"],
  "deletedAt": null,
  "locale": "en"
}
```

### PATCH /api/users/me/locale
Changes the locale for the authenticated user. The choice is persisted in the database.

**Request:**
```json
{ "locale": "pl" }
```

Allowed values: `en`, `pl`

**Response:** same as `GET /api/me` with updated locale.

---

## Language Switch Flow

1. On login / app init → `GET /api/users/me` → read `locale` field → set as current language
2. User clicks language switch → `PATCH /api/users/me/locale` with new value
3. Update local state with new locale
4. Reload translations (`GET /api/dictionaries/translations/{locale}`)

---

## Frontend Integration Example

```jsx
// Read locale on init
const { data } = await api.get("/users/me");
setLocale(data.locale ?? "en");

// Change locale
await api.patch("/users/me/locale", { locale: "pl" });
setLocale("pl");
// Reload translations
await loadTranslations("pl");
```

## Best Practices

- Always read locale from `GET /api/me` on app init — do not rely solely on localStorage
- Fallback to `"en"` if locale is missing or user is unauthenticated
- The backend automatically uses the user's locale for all API error/validation messages (via `LocaleListener`)
