<?php

namespace App\Tests\Controller;

use App\Entity\Channel;
use App\Enum\ChannelStatus;
use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationControllerTest extends WebTestCase
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
        $email = 'client-notify-' . uniqid() . '@example.com';
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

    private function createChannel(): array
    {
        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'name' => 'Notify Test Channel ' . uniqid(),
            'description' => 'Test',
            'language' => 'pl',
            'isPublic' => true,
        ]));

        return $this->getJsonResponse();
    }

    private function sendNotification(string $channelId, ?string $apiKey, array $payload = []): void
    {
        $headers = ['CONTENT_TYPE' => 'application/json'];
        if ($apiKey !== null) {
            $headers['HTTP_X_API_KEY'] = $apiKey;
        }

        $body = array_merge(['title' => 'Test title', 'body' => 'Test body'], $payload);

        $this->client->request('POST', '/api/channels/' . $channelId . '/notify', [], [], $headers, json_encode($body));
    }

    public function testNotifyHappyPath(): void
    {
        $channel = $this->createChannel();

        $this->sendNotification($channel['id'], $channel['apiKey']);

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('notificationId', $data);
        $this->assertArrayHasKey('subscribersCount', $data);
        $this->assertIsString($data['notificationId']);
        $this->assertIsInt($data['subscribersCount']);
    }

    public function testNotifyWithOptionalFields(): void
    {
        $channel = $this->createChannel();

        $this->sendNotification($channel['id'], $channel['apiKey'], [
            'imageUrl' => 'https://example.com/img.png',
            'extraData' => ['action' => 'open_url'],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('notificationId', $data);
    }

    public function testNotifyMissingApiKey(): void
    {
        $channel = $this->createChannel();

        $this->sendNotification($channel['id'], null);

        $this->assertResponseStatusCodeSame(401);
        $data = $this->getJsonResponse();
        $this->assertEquals(401, $data['code']);
    }

    public function testNotifyInvalidApiKey(): void
    {
        $channel = $this->createChannel();

        $this->sendNotification($channel['id'], 'invalid-key-123');

        $this->assertResponseStatusCodeSame(401);
        $data = $this->getJsonResponse();
        $this->assertEquals(401, $data['code']);
    }

    public function testNotifyChannelNotFound(): void
    {
        $this->sendNotification('00000000-0000-0000-0000-000000000000', 'any-key');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testNotifyBlockedChannel(): void
    {
        $channel = $this->createChannel();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(Channel::class)->find($channel['id']);
        $entity->setStatus(ChannelStatus::BLOCKED);
        $entity->setBlockedReason('Spam');
        $em->flush();

        $this->sendNotification($channel['id'], $channel['apiKey']);

        $this->assertResponseStatusCodeSame(403);
        $data = $this->getJsonResponse();
        $this->assertEquals(403, $data['code']);
    }

    public function testNotifyInactiveChannel(): void
    {
        $channel = $this->createChannel();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository(Channel::class)->find($channel['id']);
        $entity->setStatus(ChannelStatus::INACTIVE);
        $em->flush();

        $this->sendNotification($channel['id'], $channel['apiKey']);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testNotifyMissingTitle(): void
    {
        $channel = $this->createChannel();

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_KEY' => $channel['apiKey'],
        ];

        $this->client->request('POST', '/api/channels/' . $channel['id'] . '/notify', [], [], $headers, json_encode([
            'body' => 'Only body',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNotifyMissingBody(): void
    {
        $channel = $this->createChannel();

        $headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_KEY' => $channel['apiKey'],
        ];

        $this->client->request('POST', '/api/channels/' . $channel['id'] . '/notify', [], [], $headers, json_encode([
            'title' => 'Only title',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNotifyDeletedChannelReturns404(): void
    {
        $channel = $this->createChannel();

        // Soft-delete the channel
        $this->client->request('DELETE', '/api/client/channels/' . $channel['id'], [], [], $this->getAuthHeaders($this->clientToken));
        $this->assertResponseStatusCodeSame(204);

        $this->sendNotification($channel['id'], $channel['apiKey']);

        $this->assertResponseStatusCodeSame(404);
    }

    public function testNotificationSavedInDatabase(): void
    {
        $channel = $this->createChannel();

        $this->sendNotification($channel['id'], $channel['apiKey'], [
            'title' => 'DB Test Title',
            'body' => 'DB Test Body',
            'imageUrl' => 'https://example.com/test.png',
            'extraData' => ['key' => 'value'],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $notification = $em->getRepository(\App\Entity\Notification::class)->find($data['notificationId']);

        $this->assertNotNull($notification);
        $this->assertEquals('DB Test Title', $notification->getTitle());
        $this->assertEquals('DB Test Body', $notification->getBody());
        $this->assertEquals('https://example.com/test.png', $notification->getImageUrl());
        $this->assertEquals(['key' => 'value'], $notification->getExtraData());
        $this->assertFalse($notification->isTest());
        $this->assertEquals($channel['id'], (string) $notification->getChannel()->getId());
    }
}
