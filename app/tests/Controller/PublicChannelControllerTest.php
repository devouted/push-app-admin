<?php

namespace App\Tests\Controller;

use App\Entity\Channel;
use App\Entity\Consumer;
use App\Entity\Notification;
use App\Entity\Subscription;
use App\Enum\ChannelStatus;
use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PublicChannelControllerTest extends WebTestCase
{
    use AuthenticatedApiTestTrait;

    private KernelBrowser $client;
    private string $adminToken;
    private string $clientToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->adminToken = $this->loginAsAdmin();
        $this->clientToken = $this->createClientUserAndLogin();
    }

    private function createClientUserAndLogin(): string
    {
        $email = 'client-pub-' . uniqid() . '@example.com';
        $this->client->request('POST', '/api/admin/users', [], [], array_merge(
            $this->getAuthHeaders($this->adminToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode(['email' => $email, 'password' => 'Test@1234', 'roles' => ['ROLE_CLIENT']]));

        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['email' => $email, 'password' => 'Test@1234']));
        return $this->getJsonResponse()['token'];
    }

    private function createConsumer(): string
    {
        $this->client->request('POST', '/api/consumers/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'expo_token' => 'ExponentPushToken[pub-' . uniqid() . ']',
        ]));
        return $this->getJsonResponse()['uuid'];
    }

    private function createChannel(array $overrides = []): array
    {
        $payload = array_merge([
            'name' => 'Pub Channel ' . uniqid(),
            'description' => 'Test',
            'language' => 'pl',
            'isPublic' => true,
        ], $overrides);

        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode($payload));

        return $this->getJsonResponse();
    }

    private function consumerHeaders(string $uuid): array
    {
        return ['HTTP_X_CONSUMER_UUID' => $uuid];
    }

    // --- GET /public/channels ---

    public function testListPublicChannels(): void
    {
        $consumerUuid = $this->createConsumer();
        $this->createChannel(['name' => 'Public One', 'category' => 'news']);

        $this->client->request('GET', '/api/public/channels', [], [], $this->consumerHeaders($consumerUuid));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('items', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertGreaterThanOrEqual(1, count($data['items']));

        $item = $data['items'][0];
        $this->assertArrayHasKey('subscribersCount', $item);
        $this->assertArrayHasKey('name', $item);
        $this->assertArrayNotHasKey('apiKey', $item);
    }

    public function testListPublicChannelsFilterByCategory(): void
    {
        $consumerUuid = $this->createConsumer();
        $this->createChannel(['category' => 'sports']);
        $this->createChannel(['category' => 'tech']);

        $this->client->request('GET', '/api/public/channels?category=sports', [], [], $this->consumerHeaders($consumerUuid));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        foreach ($data['items'] as $item) {
            $this->assertEquals('sports', $item['category']);
        }
    }

    public function testListPublicChannelsFilterByLanguage(): void
    {
        $consumerUuid = $this->createConsumer();
        $this->createChannel(['language' => 'en']);

        $this->client->request('GET', '/api/public/channels?language=en', [], [], $this->consumerHeaders($consumerUuid));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        foreach ($data['items'] as $item) {
            $this->assertEquals('en', $item['language']);
        }
    }

    public function testListRequiresConsumerUuid(): void
    {
        $this->client->request('GET', '/api/public/channels');
        $this->assertResponseStatusCodeSame(401);
    }

    public function testListInvalidConsumerUuid(): void
    {
        $this->client->request('GET', '/api/public/channels', [], [], $this->consumerHeaders('00000000-0000-0000-0000-000000000000'));
        $this->assertResponseStatusCodeSame(401);
    }

    // --- POST /public/channels/{id}/subscribe ---

    public function testSubscribeHappyPath(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('id', $data);
    }

    public function testSubscribeAlreadySubscribed(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(409);
    }

    public function testSubscribeReactivateDeleted(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        // Subscribe
        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(201);

        // Unsubscribe
        $this->client->request('DELETE', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(204);

        // Re-subscribe
        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(201);
    }

    public function testSubscribeMaxSubscribersReached(): void
    {
        $channel = $this->createChannel(['maxSubscribers' => 1]);

        $consumer1 = $this->createConsumer();
        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumer1));
        $this->assertResponseStatusCodeSame(201);

        $consumer2 = $this->createConsumer();
        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumer2));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testSubscribeChannelNotFound(): void
    {
        $consumerUuid = $this->createConsumer();
        $this->client->request('POST', '/api/public/channels/00000000-0000-0000-0000-000000000000/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(404);
    }

    public function testSubscribeBlockedChannel(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(Channel::class)->find($channel['id']);
        $entity->setStatus(ChannelStatus::BLOCKED);
        $em->flush();

        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(404);
    }

    // --- DELETE /public/channels/{id}/subscribe ---

    public function testUnsubscribeHappyPath(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('DELETE', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(204);
    }

    public function testUnsubscribeNotSubscribed(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        $this->client->request('DELETE', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(404);
    }

    // --- GET /public/channels/{id}/notifications ---

    public function testNotificationsHappyPath(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        // Subscribe
        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(201);

        // Create notification via notify endpoint
        $this->client->request('POST', '/api/channels/' . $channel['id'] . '/notify', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_KEY' => $channel['apiKey'],
        ], json_encode(['title' => 'Test Notif', 'body' => 'Body']));
        $this->assertResponseStatusCodeSame(201);

        // Get notifications
        $this->client->request('GET', '/api/public/channels/' . $channel['id'] . '/notifications', [], [], $this->consumerHeaders($consumerUuid));

        $this->assertResponseIsSuccessful();
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('items', $data);
        $this->assertGreaterThanOrEqual(1, count($data['items']));
        $this->assertArrayHasKey('title', $data['items'][0]);
        $this->assertArrayHasKey('body', $data['items'][0]);
    }

    public function testNotificationsNotSubscribed(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        $this->client->request('GET', '/api/public/channels/' . $channel['id'] . '/notifications', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(403);
    }

    public function testNotificationsExcludesTestNotifications(): void
    {
        $consumerUuid = $this->createConsumer();
        $channel = $this->createChannel();

        // Subscribe
        $this->client->request('POST', '/api/public/channels/' . $channel['id'] . '/subscribe', [], [], $this->consumerHeaders($consumerUuid));

        // Create test notification
        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/test', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([]));

        // Create real notification
        $this->client->request('POST', '/api/channels/' . $channel['id'] . '/notify', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_KEY' => $channel['apiKey'],
        ], json_encode(['title' => 'Real', 'body' => 'Real body']));

        $this->client->request('GET', '/api/public/channels/' . $channel['id'] . '/notifications', [], [], $this->consumerHeaders($consumerUuid));

        $data = $this->getJsonResponse();
        foreach ($data['items'] as $item) {
            $this->assertNotEquals('Test kanalu', substr($item['title'], 0, 11));
        }
        $this->assertEquals(1, $data['total']);
    }

    public function testNotificationsChannelNotFound(): void
    {
        $consumerUuid = $this->createConsumer();
        $this->client->request('GET', '/api/public/channels/00000000-0000-0000-0000-000000000000/notifications', [], [], $this->consumerHeaders($consumerUuid));
        $this->assertResponseStatusCodeSame(404);
    }
}
