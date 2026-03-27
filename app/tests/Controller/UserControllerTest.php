<?php

namespace App\Tests\Controller;

use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use AuthenticatedApiTestTrait;

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // GET /api/users/me

    public function testMeReturns200WithUserData(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('GET', '/api/users/me', [], [], $this->getAuthHeaders($token));
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('locale', $data);
    }

    public function testMeReturns403WithoutToken(): void
    {
        $this->client->request('GET', '/api/users/me');
        $this->assertResponseStatusCodeSame(403);
    }

    // PATCH /api/users/me/locale

    public function testUpdateLocaleToPl(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('PATCH', '/api/users/me/locale', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode(['locale' => 'pl']));
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertEquals('pl', $data['locale']);
    }

    public function testUpdateLocaleToEn(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('PATCH', '/api/users/me/locale', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode(['locale' => 'en']));
        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertEquals('en', $data['locale']);
    }

    public function testUpdateLocaleInvalidReturns422(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('PATCH', '/api/users/me/locale', [], [], array_merge($this->getAuthHeaders($token), ['CONTENT_TYPE' => 'application/json']), json_encode(['locale' => 'de']));
        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateLocaleReturns403WithoutToken(): void
    {
        $this->client->request('PATCH', '/api/users/me/locale', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['locale' => 'pl']));
        $this->assertResponseStatusCodeSame(403);
    }
}
