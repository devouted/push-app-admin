# App Directory

This directory contains the Symfony application code.

## Authorization & Permissions

### Role-based Access Control

Endpoints can be secured using Symfony's `IsGranted` attribute:

```php
#[IsGranted('ROLE_ADMIN')]
public function adminEndpoint(): JsonResponse
{
    // Only users with ROLE_ADMIN can access
}
```

### Available Roles

- `ROLE_USER` - Default role for all authenticated users
- `ROLE_ADMIN` - Administrative access

### Response Codes

- `401 Unauthorized` - Missing or invalid JWT token
- `403 Forbidden` - Valid token but insufficient permissions
- `200 OK` - Access granted

### Example

See `AdminController::test()` for a working example of role-based endpoint protection.
