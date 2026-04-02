<?php

namespace App\Tests\Controller;

use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChannelCrudControllerTest extends WebTestCase
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
        $email = 'client-channel-' . uniqid() . '@example.com';
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
        ], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        return $this->getJsonResponse()['token'];
    }

    private function createChannel(array $overrides = []): array
    {
        $payload = array_merge([
            'name' => 'Test Channel ' . uniqid(),
            'description' => 'Test description',
            'category' => 'news',
            'icon' => 'https://example.com/icon.png',
            'language' => 'pl',
            'isPublic' => true,
            'maxSubscribers' => 1000,
            'inactivityTimeoutDays' => 7,
        ], $overrides);

        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode($payload));

        return $this->getJsonResponse();
    }

    // --- POST /client/channels ---

    public function testCreateChannelHappyPath(): void
    {
        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'name' => 'My Channel',
            'description' => 'A test channel',
            'category' => 'news',
            'language' => 'pl',
            'isPublic' => true,
            'maxSubscribers' => 500,
            'inactivityTimeoutDays' => 14,
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();
        $this->assertEquals('My Channel', $data['name']);
        $this->assertEquals('A test channel', $data['description']);
        $this->assertEquals('news', $data['category']);
        $this->assertEquals('pl', $data['language']);
        $this->assertEquals('active', $data['status']);
        $this->assertTrue($data['isPublic']);
        $this->assertEquals(500, $data['maxSubscribers']);
        $this->assertEquals(14, $data['inactivityTimeoutDays']);
        $this->assertArrayHasKey('apiKey', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('createdAt', $data);
    }

    // --- GET /client/channels ---

    public function testListChannelsWithPagination(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->createChannel(['name' => "Channel $i"]);
        }

        $this->client->request('GET', '/api/client/channels?page=1&limit=2', [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertCount(2, $data['items']);
        $this->assertGreaterThanOrEqual(3, $data['total']);
        $this->assertEquals(1, $data['page']);
        $this->assertEquals(2, $data['limit']);
    }

    public function testListChannelsDoesNotContainApiKey(): void
    {
        $this->createChannel();

        $this->client->request('GET', '/api/client/channels', [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertNotEmpty($data['items']);
        $this->assertArrayNotHasKey('apiKey', $data['items'][0]);
    }

    // --- GET /client/channels/{id} ---

    public function testGetChannelDetails(): void
    {
        $channel = $this->createChannel();

        $this->client->request('GET', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertEquals($channel['id'], $data['id']);
        $this->assertArrayHasKey('apiKey', $data);
        $this->assertArrayHasKey('blockedReason', $data);
    }

    public function testGetChannelDetailsBlockedReason(): void
    {
        $channel = $this->createChannel();

        // Set blocked status directly via DB
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(\App\Entity\Channel::class)->find($channel['id']);
        $entity->setStatus(\App\Enum\ChannelStatus::BLOCKED);
        $entity->setBlockedReason('Spam detected');
        $em->flush();

        $this->client->request('GET', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertEquals('blocked', $data['status']);
        $this->assertEquals('Spam detected', $data['blockedReason']);
    }

    public function testGetChannelNotFound(): void
    {
        $this->client->request('GET', '/api/client/channels/00000000-0000-0000-0000-000000000000', [], [], $this->getAuthHeaders($this->clientToken));
        $this->assertResponseStatusCodeSame(404);
    }

    // --- PATCH /client/channels/{id} ---

    public function testUpdateChannel(): void
    {
        $channel = $this->createChannel();

        $this->client->request('PATCH', '/api/client/channels/' . $channel['id'], [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'name' => 'Updated Name',
            'description' => 'Updated desc',
            'language' => 'en',
        ]));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertEquals('Updated Name', $data['name']);
        $this->assertEquals('Updated desc', $data['description']);
        $this->assertEquals('en', $data['language']);
    }

    // --- DELETE /client/channels/{id} ---

    public function testDeleteChannelSoftDelete(): void
    {
        $channel = $this->createChannel();

        $this->client->request('DELETE', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));
        $this->assertResponseStatusCodeSame(204);

        // Channel should not be accessible anymore
        $this->client->request('GET', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));
        $this->assertResponseStatusCodeSame(404);

        // But should still exist in DB
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(\App\Entity\Channel::class)->find($channel['id']);
        $this->assertNotNull($entity);
        $this->assertNotNull($entity->getDeletedAt());
    }

    // --- Authorization: Client sees only own channels ---

    public function testClientCannotAccessOtherClientChannel(): void
    {
        $channel = $this->createChannel();

        // Create second client
        $otherToken = $this->createClientUserAndLogin();

        $this->client->request('GET', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($otherToken));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testClientCannotEditOtherClientChannel(): void
    {
        $channel = $this->createChannel();
        $otherToken = $this->createClientUserAndLogin();

        $this->client->request('PATCH', '/api/client/channels/' . $channel['id'], [], [], array_merge(
            $this->getAuthHeaders($otherToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode(['name' => 'Hacked']));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testClientCannotDeleteOtherClientChannel(): void
    {
        $channel = $this->createChannel();
        $otherToken = $this->createClientUserAndLogin();

        $this->client->request('DELETE', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($otherToken));
        $this->assertResponseStatusCodeSame(404);
    }

    // --- Access control: only ROLE_CLIENT ---

    public function testAdminCannotAccessClientChannels(): void
    {
        $this->client->request('GET', '/api/client/channels', [], [], $this->getAuthHeaders($this->adminToken));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testUnauthenticatedCannotAccessClientChannels(): void
    {
        $this->client->request('GET', '/api/client/channels');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testRegularUserCannotAccessClientChannels(): void
    {
        $email = 'regular-' . uniqid() . '@example.com';
        $this->client->request('POST', '/api/admin/users', [], [], array_merge(
            $this->getAuthHeaders($this->adminToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'email' => $email,
            'password' => 'Test@1234',
            'roles' => ['ROLE_USER'],
        ]));

        $this->client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['email' => $email, 'password' => 'Test@1234']));
        $regularToken = $this->getJsonResponse()['token'];

        $this->client->request('GET', '/api/client/channels', [], [], $this->getAuthHeaders($regularToken));
        $this->assertResponseStatusCodeSame(403);
    }
}
