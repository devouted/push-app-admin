<?php

namespace App\Tests\Controller;

use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserManagementControllerTest extends WebTestCase
{
    use AuthenticatedApiTestTrait;

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }
    public function testListUsers(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('GET', '/api/admin/users', [], [], $this->getAuthHeaders($token));
        $this->assertResponseIsSuccessful();
    }

    public function testCreateUser(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'newuser' . time() . '@example.com',
            'password' => 'Test@1234',
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(201);
    }

    public function testGetUser(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('GET', '/api/admin/users/1', [], [], $this->getAuthHeaders($token));
        $this->assertResponseIsSuccessful();
    }

    public function testGetUserNotFound(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('GET', '/api/admin/users/99999', [], [], $this->getAuthHeaders($token));
        $this->assertResponseStatusCodeSame(404);
        $this->assertErrorResponse(404);
    }

    public function testUpdateUser(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('PUT', '/api/admin/users/1', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'roles' => ['ROLE_USER', 'ROLE_ADMIN']
        ]));
        $this->assertResponseIsSuccessful();
    }

    public function testAssignRoles(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users/1/roles', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'roles' => ['ROLE_USER', 'ROLE_ADMIN']
        ]));
        $this->assertResponseIsSuccessful();
    }

    public function testCreateUserValidationInvalidEmail(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'notanemail',
            'password' => 'password123',
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateUserValidationPasswordTooShort(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'newuser@example.com',
            'password' => '12345',
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateUserValidationWeakPassword(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testCreateUserValidationInvalidRole(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'roles' => ['INVALID_ROLE']
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateUserNotFound(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('PUT', '/api/admin/users/99999', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateUserValidationInvalidEmail(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('PUT', '/api/admin/users/1', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'notanemail'
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testDeleteUser(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => 'todelete' . time() . '@example.com',
            'password' => 'Test@1234',
            'roles' => ['ROLE_USER']
        ]));
        $userId = $this->getJsonResponse()['id'];

        $this->client->request('DELETE', '/api/admin/users/' . $userId, [], [], $this->getAuthHeaders($token));
        $this->assertResponseStatusCodeSame(204);
    }

    public function testDeleteUserNotFound(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('DELETE', '/api/admin/users/99999', [], [], $this->getAuthHeaders($token));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testAssignRolesNotFound(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users/99999/roles', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testAssignRolesValidationInvalidRole(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('POST', '/api/admin/users/1/roles', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'roles' => ['INVALID_ROLE']
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testAdminEndpointsForbiddenForNonAdmin(): void
    {
        $adminToken = $this->loginAsAdmin();
        $email = 'regularuser' . time() . '@example.com';
        $this->client->request('POST', '/api/admin/users', [], [], array_merge($this->getAuthHeaders($adminToken), ['CONTENT_TYPE' => 'application/json']), json_encode([
            'email' => $email,
            'password' => 'Test@1234',
            'roles' => ['ROLE_USER']
        ]));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => 'Test@1234'
        ]));
        $regularToken = $this->getJsonResponse()['token'];

        $this->client->request('GET', '/api/admin/users', [], [], $this->getAuthHeaders($regularToken));
        $this->assertResponseStatusCodeSame(403);
    }
}
