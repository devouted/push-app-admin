<?php

namespace App\Tests\Controller;

use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DictionaryControllerTest extends WebTestCase
{
    use AuthenticatedApiTestTrait;

    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    // GET /api/dictionaries/locales

    public function testLocalesReturns200(): void
    {
        $this->client->request('GET', '/api/dictionaries/locales');
        $this->assertResponseIsSuccessful();
    }

    public function testLocalesReturnsEnAndPl(): void
    {
        $this->client->request('GET', '/api/dictionaries/locales');
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('locales', $data);
        $this->assertContains('en', $data['locales']);
        $this->assertContains('pl', $data['locales']);
    }

    // GET /api/dictionaries/translations/{locale}

    public function testTranslationsEnReturns200(): void
    {
        $this->client->request('GET', '/api/dictionaries/translations/en');
        $this->assertResponseIsSuccessful();
    }

    public function testTranslationsPlReturns200(): void
    {
        $this->client->request('GET', '/api/dictionaries/translations/pl');
        $this->assertResponseIsSuccessful();
    }

    public function testTranslationsResponseContainsDomains(): void
    {
        $this->client->request('GET', '/api/dictionaries/translations/en');
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('messages', $data);
        $this->assertArrayHasKey('validators', $data);
        $this->assertArrayHasKey('security', $data);
    }

    public function testTranslationsInvalidLocaleReturns400(): void
    {
        $this->client->request('GET', '/api/dictionaries/translations/de');
        $this->assertResponseStatusCodeSame(400);
        $this->assertErrorResponse(400);
    }

    // GET /api/dictionaries/roles

    public function testRolesReturns401WithoutToken(): void
    {
        $this->client->request('GET', '/api/dictionaries/roles');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testRolesReturns200ForAuthenticatedUser(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('GET', '/api/dictionaries/roles', [], [], $this->getAuthHeaders($token));
        $this->assertResponseIsSuccessful();
    }

    public function testRolesResponseContainsRoleUserAndRoleAdmin(): void
    {
        $token = $this->loginAsAdmin();
        $this->client->request('GET', '/api/dictionaries/roles', [], [], $this->getAuthHeaders($token));
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('roles', $data);
        $this->assertContains('ROLE_USER', $data['roles']);
        $this->assertContains('ROLE_ADMIN', $data['roles']);
    }
}
