<?php

namespace App\Tests\Controller;

use App\Entity\Consumer;
use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NotificationDetailControllerTest extends WebTestCase
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
        $email = 'client-detail-' . uniqid() . '@example.com';
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

    private function createChannelAndNotification(): array
    {
        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'name' => 'Detail Test Channel ' . uniqid(),
            'description' => 'Test',
            'language' => 'pl',
            'isPublic' => true,
            'icon' => 'https://example.com/icon.png',
        ]));
        $channel = $this->getJsonResponse();

        $this->client->request('POST', '/api/channels/' . $channel['id'] . '/notify', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_API_KEY' => $channel['apiKey'],
        ], json_encode([
            'title' => 'Test Notification',
            'body' => 'Test body content',
            'imageUrl' => 'https://example.com/img.png',
            'extraData' => ['action' => 'open'],
        ]));
        $notification = $this->getJsonResponse();

        return ['channel' => $channel, 'notification' => $notification];
    }

    public function testGetNotificationDetails(): void
    {
        $data = $this->createChannelAndNotification();

        $this->client->request('GET', '/api/notifications/' . $data['notification']['notificationId']);

        $this->assertResponseIsSuccessful();
        $response = $this->getJsonResponse();
        $this->assertEquals($data['notification']['notificationId'], $response['id']);
        $this->assertEquals('Test Notification', $response['title']);
        $this->assertEquals('Test body content', $response['body']);
        $this->assertEquals('https://example.com/img.png', $response['imageUrl']);
        $this->assertEquals(['action' => 'open'], $response['extraData']);
        $this->assertArrayHasKey('channel', $response);
        $this->assertEquals($data['channel']['id'], $response['channel']['id']);
        $this->assertArrayHasKey('name', $response['channel']);
        $this->assertArrayHasKey('icon', $response['channel']);
        $this->assertArrayHasKey('createdAt', $response);
    }

    public function testGetNotificationNotFound(): void
    {
        $this->client->request('GET', '/api/notifications/00000000-0000-0000-0000-000000000000');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testGetNotificationUpdatesConsumerLastActiveAt(): void
    {
        $data = $this->createChannelAndNotification();

        // Create a consumer
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[detail-test-' . uniqid() . ']',
            'device_name' => 'Test Device',
        ]));
        $consumer = $this->getJsonResponse();

        // Get notification with consumer UUID header
        $this->client->request('GET', '/api/notifications/' . $data['notification']['notificationId'], [], [], [
            'HTTP_X_CONSUMER_UUID' => $consumer['uuid'],
        ]);

        $this->assertResponseIsSuccessful();

        // Verify last_active_at was updated
        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $consumerEntity = $em->getRepository(Consumer::class)->find($consumer['uuid']);
        $this->assertNotNull($consumerEntity->getLastActiveAt());
    }

    public function testGetNotificationUpdatesConsumerViaQueryParam(): void
    {
        $data = $this->createChannelAndNotification();

        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[detail-qp-' . uniqid() . ']',
            'device_name' => 'Test Device',
        ]));
        $consumer = $this->getJsonResponse();

        $this->client->request('GET', '/api/notifications/' . $data['notification']['notificationId'] . '?consumer_uuid=' . $consumer['uuid']);

        $this->assertResponseIsSuccessful();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $consumerEntity = $em->getRepository(Consumer::class)->find($consumer['uuid']);
        $this->assertNotNull($consumerEntity->getLastActiveAt());
    }

    public function testGetNotificationWithoutConsumerWorksNormally(): void
    {
        $data = $this->createChannelAndNotification();

        $this->client->request('GET', '/api/notifications/' . $data['notification']['notificationId']);

        $this->assertResponseIsSuccessful();
        $response = $this->getJsonResponse();
        $this->assertEquals('Test Notification', $response['title']);
    }

    public function testGetNotificationWithInvalidConsumerUuidWorksNormally(): void
    {
        $data = $this->createChannelAndNotification();

        $this->client->request('GET', '/api/notifications/' . $data['notification']['notificationId'], [], [], [
            'HTTP_X_CONSUMER_UUID' => '00000000-0000-0000-0000-000000000000',
        ]);

        $this->assertResponseIsSuccessful();
    }
}
