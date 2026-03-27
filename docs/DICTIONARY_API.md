# Dictionary API Documentation

## Overview

Dictionary API provides translation resources for the frontend application. All endpoints are publicly accessible and don't require authentication.

## Endpoints

### Get Available Locales

```
GET /api/dictionaries/locales
```

Returns a list of available language codes.

**Response:**
```json
["en", "pl"]
```

### Get Translations

```
GET /api/dictionaries/translations/{locale}
```

Returns all translations for the specified locale, grouped by domain.

**Parameters:**
- `locale` (path, required): Language code (en, pl)

**Response:**
```json
{
  "messages": {
    "error.internal_server": "Internal server error",
    "error.not_found": "Resource not found",
    "success.created": "Resource created successfully",
    "ui.button.save": "Save",
    "ui.label.email": "Email"
  },
  "validators": {
    "user.email.required": "Email is required",
    "user.email.invalid": "Invalid email address",
    "user.password.too_short": "Password must be at least {{ limit }} characters long"
  },
  "security": {
    "auth.login_success": "Login successful",
    "auth.token_expired": "Token has expired"
  }
}
```

**Error Response (400):**
```json
{
  "code": 400,
  "message": "Invalid locale. Available: en, pl",
  "type": "bad_request"
}
```

## Translation Key Conventions

### Structure
Keys use dot notation with hierarchical structure:
- `domain.category.key`
- Example: `ui.button.save`, `error.user_not_found`

### Domains
- **messages**: General UI messages, errors, success messages
- **validators**: Validation error messages
- **security**: Authentication and authorization messages

### Categories
- `error.*`: Error messages
- `success.*`: Success messages
- `ui.button.*`: Button labels
- `ui.label.*`: Form labels
- `ui.message.*`: UI messages
- `user.*`: User-related messages

## Frontend Integration

### React with i18next

```javascript
import i18next from 'i18next';
import { initReactI18next } from 'react-i18next';

// Fetch translations on app init
const loadTranslations = async (locale) => {
  const response = await fetch(`/api/dictionaries/translations/${locale}`);
  return await response.json();
};

// Initialize i18next
const initI18n = async () => {
  const translations = await loadTranslations('en');
  
  i18next
    .use(initReactI18next)
    .init({
      resources: {
        en: translations
      },
      lng: 'en',
      fallbackLng: 'en',
      interpolation: {
        escapeValue: false
      }
    });
};

// Usage in components
import { useTranslation } from 'react-i18next';

function MyComponent() {
  const { t } = useTranslation('messages');
  return <button>{t('ui.button.save')}</button>;
}
```

### Vue with vue-i18n

```javascript
import { createI18n } from 'vue-i18n';

// Fetch translations
const loadTranslations = async (locale) => {
  const response = await fetch(`/api/dictionaries/translations/${locale}`);
  return await response.json();
};

// Create i18n instance
const createI18nInstance = async () => {
  const translations = await loadTranslations('en');
  
  return createI18n({
    locale: 'en',
    fallbackLocale: 'en',
    messages: {
      en: translations
    }
  });
};

// Usage in templates
<template>
  <button>{{ $t('messages.ui.button.save') }}</button>
</template>
```

## Caching Recommendations

### Browser Cache
- Cache translations in localStorage/sessionStorage
- Invalidate cache on app version change
- Refresh translations periodically (e.g., daily)

### Example Cache Strategy

```javascript
const CACHE_KEY = 'app_translations';
const CACHE_VERSION = '1.0.0';

const getTranslations = async (locale) => {
  const cached = localStorage.getItem(`${CACHE_KEY}_${locale}`);
  const version = localStorage.getItem(`${CACHE_KEY}_version`);
  
  if (cached && version === CACHE_VERSION) {
    return JSON.parse(cached);
  }
  
  const response = await fetch(`/api/dictionaries/translations/${locale}`);
  const translations = await response.json();
  
  localStorage.setItem(`${CACHE_KEY}_${locale}`, JSON.stringify(translations));
  localStorage.setItem(`${CACHE_KEY}_version`, CACHE_VERSION);
  
  return translations;
};
```

## OpenAPI Documentation

All Dictionary API endpoints are documented in OpenAPI/Swagger under the `Dictionary` tag.

Access the interactive documentation at: `/api/doc`

## Notes

- Translations support parameter interpolation (e.g., `{{ limit }}`)
- All endpoints return JSON
- No authentication required
- CORS enabled for frontend access
