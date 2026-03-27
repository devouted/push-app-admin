<?php

namespace App\Tests\Controller;

use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    use AuthenticatedApiTestTrait;

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }
    public function testLoginSuccess(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'Test@1234'
        ]));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('user', $data);
    }

    public function testLoginInvalidCredentials(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
        $this->assertErrorResponse(401);
    }

    public function testMeEndpointAuthenticated(): void
    {
        $token = $this->loginAsAdmin();

        $this->client->request('GET', '/api/users/me', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
    }

    public function testMeEndpointUnauthorized(): void
    {
        $this->client->request('GET', '/api/users/me');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testMeEndpointInvalidToken(): void
    {
        $this->client->request('GET', '/api/users/me', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid.token.here',
            'CONTENT_TYPE' => 'application/json'
        ]);
        $this->assertResponseStatusCodeSame(401);
    }

    public function testLoginValidationMissingEmail(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'password' => 'test123'
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testLoginValidationInvalidEmailFormat(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'notanemail',
            'password' => 'test123'
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testLoginValidationMissingPassword(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com'
        ]));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testLoginValidationPasswordTooShort(): void
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => '12345'
        ]));
        $this->assertResponseStatusCodeSame(422);
    }
}
