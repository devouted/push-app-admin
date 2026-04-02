<?php

namespace App\Tests\Controller;

use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChannelApiKeyControllerTest extends WebTestCase
{
    use AuthenticatedApiTestTrait;

    private KernelBrowser $client;
    private string $clientToken;
    private string $adminToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->adminToken = $this->loginAsAdmin();
        $this->clientToken = $this->createClientUserAndLogin();
    }

    private function createClientUserAndLogin(): string
    {
        $email = 'client-apikey-' . uniqid() . '@example.com';
        $password = 'Test@1234';

        $this->client->request('POST', '/api/admin/users', [], [], array_merge(
            $this->getAuthHeaders($this->adminToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'email' => $email,
            'password' => $password,
            'roles' => ['ROLE_CLIENT'],
        ]));

        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => $email, 'password' => $password]));

        return $this->getJsonResponse()['token'];
    }

    private function createChannel(): array
    {
        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode(['name' => 'ApiKey Test ' . uniqid()]));

        return $this->getJsonResponse();
    }

    public function testApiKeyGeneratedOnCreate(): void
    {
        $channel = $this->createChannel();

        $this->assertArrayHasKey('apiKey', $channel);
        $this->assertNotEmpty($channel['apiKey']);
        $this->assertEquals(64, strlen($channel['apiKey']));
    }

    public function testRotateKeyReturnsNewKey(): void
    {
        $channel = $this->createChannel();
        $oldKey = $channel['apiKey'];

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/rotate-key', [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertNotEquals($oldKey, $data['apiKey']);
        $this->assertEquals(64, strlen($data['apiKey']));
    }

    public function testOldKeyInvalidAfterRotation(): void
    {
        $channel = $this->createChannel();
        $oldKey = $channel['apiKey'];

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/rotate-key', [], [], $this->getAuthHeaders($this->clientToken));
        $newData = $this->getJsonResponse();

        // Verify via GET that the stored key is the new one, not the old one
        $this->client->request('GET', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));
        $detail = $this->getJsonResponse();

        $this->assertEquals($newData['apiKey'], $detail['apiKey']);
        $this->assertNotEquals($oldKey, $detail['apiKey']);
    }

    public function testApiKeyVisibleInDetails(): void
    {
        $channel = $this->createChannel();

        $this->client->request('GET', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('apiKey', $data);
        $this->assertNotEmpty($data['apiKey']);
    }

    public function testApiKeyNotVisibleInList(): void
    {
        $this->createChannel();

        $this->client->request('GET', '/api/client/channels', [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertNotEmpty($data['items']);
        $this->assertArrayNotHasKey('apiKey', $data['items'][0]);
    }

    public function testOnlyOwnerCanRotateKey(): void
    {
        $channel = $this->createChannel();
        $otherToken = $this->createClientUserAndLogin();

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/rotate-key', [], [], $this->getAuthHeaders($otherToken));

        $this->assertResponseStatusCodeSame(404);
    }
}
