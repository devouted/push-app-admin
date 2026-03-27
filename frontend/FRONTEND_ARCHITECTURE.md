# Frontend Architecture

## Struktura projektu

```
frontend/src/
├── components/
│   ├── ui/              # Bazowe komponenty UI (Button, Input, Card, Modal)
│   ├── forms/           # Komponenty formularzy
│   └── layout/          # Layout komponenty (Header, Nav)
├── styles/
│   ├── base/
│   │   ├── _reset.scss       # Reset stylów
│   │   ├── _typography.scss  # Typografia
│   │   └── _variables.scss   # Zmienne globalne
│   ├── components/
│   │   ├── _button.scss      # Style dla Button
│   │   ├── _input.scss       # Style dla Input
│   │   ├── _card.scss        # Style dla Card
│   │   └── _modal.scss       # Style dla Modal
│   ├── layout/
│   │   ├── _header.scss      # Style dla Header
│   │   ├── _nav.scss         # Style dla nawigacji
│   │   └── _main.scss        # Style dla głównego kontenera
│   ├── pages/
│   │   ├── _login.scss       # Style dla Login
│   │   └── _users.scss       # Style dla Users
│   └── main.scss             # Import wszystkich stylów
├── pages/               # Strony (tylko kompozycja)
├── api/                 # Axios config
└── context/             # React Context
```

## Komponenty UI

### Button

```jsx
import { Button } from "../components/ui";

// Podstawowe użycie
<Button variant="primary" onClick={handleClick}>
  Kliknij mnie
</Button>

// Warianty
<Button variant="primary">Primary</Button>
<Button variant="secondary">Secondary</Button>
<Button variant="ghost">Ghost</Button>
<Button variant="error">Error</Button>
<Button variant="success">Success</Button>

// Rozmiary
<Button size="sm">Small</Button>
<Button size="md">Medium (default)</Button>
<Button size="lg">Large</Button>

// Disabled
<Button disabled>Disabled</Button>
```

### Input

```jsx
import { Input } from "../components/ui";

// Podstawowe użycie
<Input
  type="text"
  label="Email"
  placeholder="Wpisz email"
  value={email}
  onChange={(e) => setEmail(e.target.value)}
/>

// Z błędem walidacji
<Input
  type="password"
  label="Hasło"
  value={password}
  onChange={(e) => setPassword(e.target.value)}
  error="Hasło musi mieć minimum 6 znaków"
/>

// Typy
<Input type="text" />
<Input type="email" />
<Input type="password" />
```

### Card

```jsx
import { Card } from "../components/ui";

// Podstawowe użycie
<Card>
  <p>Zawartość karty</p>
</Card>

// Z tytułem
<Card title="Tytuł karty">
  <p>Zawartość</p>
</Card>

// Z akcjami
<Card
  title="Tytuł"
  actions={
    <>
      <Button variant="ghost">Anuluj</Button>
      <Button variant="primary">Zapisz</Button>
    </>
  }
>
  <p>Zawartość</p>
</Card>
```

### Modal

```jsx
import { Modal, Button } from "../components/ui";

const [isOpen, setIsOpen] = useState(false);

<Modal
  isOpen={isOpen}
  onClose={() => setIsOpen(false)}
  title="Tytuł modala"
  actions={
    <>
      <Button variant="ghost" onClick={() => setIsOpen(false)}>
        Anuluj
      </Button>
      <Button variant="primary" onClick={handleConfirm}>
        Potwierdź
      </Button>
    </>
  }
>
  <p>Zawartość modala</p>
</Modal>
```

## Konwencje stylowania

### Kiedy używać DaisyUI

- **Zawsze** dla standardowych komponentów UI (button, input, card, modal, table, alert)
- Dla layoutu (navbar, menu, drawer)
- Dla utility classes (loading, badge, avatar)

### Kiedy używać Tailwind utilities

- Layout (flex, grid, spacing, sizing)
- Responsywność (sm:, md:, lg:)
- Proste modyfikacje (text-center, font-bold)

### Kiedy używać SCSS

- Style specyficzne dla strony, które nie są pokryte przez DaisyUI
- Customowe animacje
- Złożone selektory (rzadko potrzebne)

### Czego unikać

- ❌ Inline styles w komponentach
- ❌ Duplikowanie stylów DaisyUI
- ❌ Nadmierne zagnieżdżanie w SCSS

## Dodawanie nowych komponentów

### 1. Komponent UI

```bash
# Utwórz plik w src/components/ui/
touch src/components/ui/NewComponent.jsx
```

```jsx
// src/components/ui/NewComponent.jsx
export default function NewComponent({ variant = "default", children, ...props }) {
  return (
    <div className={`new-component new-component-${variant}`} {...props}>
      {children}
    </div>
  );
}
```

```jsx
// Dodaj do src/components/ui/index.js
export { default as NewComponent } from "./NewComponent.jsx";
```

### 2. Komponent Layout

```bash
# Utwórz plik w src/components/layout/
touch src/components/layout/NewLayout.jsx
```

### 3. Strona

```bash
# Utwórz plik w src/pages/
touch src/pages/NewPage.jsx
```

```jsx
// src/pages/NewPage.jsx
import { Button, Card } from "../components/ui";

export default function NewPage() {
  return (
    <div>
      <h1 className="text-3xl font-bold mb-6">Nowa strona</h1>
      <Card>
        <p>Zawartość</p>
      </Card>
    </div>
  );
}
```

## Guidelines dla developerów

### Struktura komponentu

```jsx
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import api from "../api/axios";
import { Button, Input, Card } from "../components/ui";

export default function MyComponent() {
  // 1. Hooks
  const [data, setData] = useState([]);
  const navigate = useNavigate();

  // 2. Funkcje pomocnicze
  const handleSubmit = async (e) => {
    e.preventDefault();
    // ...
  };

  // 3. Render
  return (
    <div>
      <Card>
        {/* Zawartość */}
      </Card>
    </div>
  );
}
```

### Nazewnictwo

- Komponenty: PascalCase (Button, UserForm)
- Pliki komponentów: PascalCase.jsx (Button.jsx)
- Pliki SCSS: kebab-case z underscore (_button.scss)
- Funkcje: camelCase (handleSubmit, fetchUsers)
- Stałe: UPPER_SNAKE_CASE (AVAILABLE_ROLES)

### Props API

```jsx
// ✅ Dobre
<Button variant="primary" size="sm" disabled={loading}>
  Zapisz
</Button>

// ❌ Złe
<Button isPrimary isSmall isDisabled={loading}>
  Zapisz
</Button>
```

### Walidacja formularzy

```jsx
const validateForm = () => {
  const errors = {};
  
  if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
    errors.email = "Nieprawidłowy format email";
  }
  
  setValidationErrors(errors);
  return Object.keys(errors).length === 0;
};
```

### Obsługa błędów API

```jsx
try {
  await api.post("/endpoint", data);
  navigate("/success");
} catch (err) {
  setError(err.response?.data?.message || "Błąd podczas operacji");
}
```

## DaisyUI Themes

Projekt używa domyślnego theme DaisyUI. Dostępne kolory:

- `primary` - główny kolor akcji
- `secondary` - drugorzędny kolor
- `accent` - kolor akcentu
- `neutral` - neutralny (szary)
- `base-100`, `base-200`, `base-300` - kolory tła
- `info`, `success`, `warning`, `error` - kolory statusów

## Build i Development

```bash
# Development
npm run dev

# Build
npm run build

# Lint
npm run lint
npm run lint:fix
```
