<?php

namespace App\Tests\Controller;

use App\Entity\Notification;
use App\Tests\Traits\AuthenticatedApiTestTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChannelTestEndpointControllerTest extends WebTestCase
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
        $email = 'client-test-ep-' . uniqid() . '@example.com';
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

    private function createChannel(?string $name = null): array
    {
        $this->client->request('POST', '/api/client/channels', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'name' => $name ?? 'Test EP Channel ' . uniqid(),
            'description' => 'Test',
            'language' => 'pl',
            'isPublic' => true,
        ]));

        return $this->getJsonResponse();
    }

    public function testTestEndpointDefaultTitleBody(): void
    {
        $channel = $this->createChannel('My Test Channel');

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/test', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([]));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('notificationId', $data);
        $this->assertArrayHasKey('subscribersCount', $data);

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $notification = $em->getRepository(Notification::class)->find($data['notificationId']);
        $this->assertNotNull($notification);
        $this->assertEquals('Test kanalu My Test Channel', $notification->getTitle());
        $this->assertEquals('To jest testowe powiadomienie', $notification->getBody());
        $this->assertTrue($notification->isTest());
    }

    public function testTestEndpointCustomTitleBody(): void
    {
        $channel = $this->createChannel();

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/test', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([
            'title' => 'Custom Title',
            'body' => 'Custom Body',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $notification = $em->getRepository(Notification::class)->find($data['notificationId']);
        $this->assertEquals('Custom Title', $notification->getTitle());
        $this->assertEquals('Custom Body', $notification->getBody());
        $this->assertTrue($notification->isTest());
    }

    public function testTestEndpointNotFound(): void
    {
        $this->client->request('POST', '/api/client/channels/00000000-0000-0000-0000-000000000000/test', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([]));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testTestEndpointOtherClientChannel(): void
    {
        $channel = $this->createChannel();
        $otherToken = $this->createClientUserAndLogin();

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/test', [], [], array_merge(
            $this->getAuthHeaders($otherToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([]));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testTestEndpointRequiresAuth(): void
    {
        $channel = $this->createChannel();

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/test', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertResponseStatusCodeSame(403);
    }

    public function testTestEndpointReturnsNotificationIdAndSubscribersCount(): void
    {
        $channel = $this->createChannel();

        $this->client->request('POST', '/api/client/channels/' . $channel['id'] . '/test', [], [], array_merge(
            $this->getAuthHeaders($this->clientToken),
            ['CONTENT_TYPE' => 'application/json']
        ), json_encode([]));

        $this->assertResponseStatusCodeSame(201);
        $data = $this->getJsonResponse();
        $this->assertIsString($data['notificationId']);
        $this->assertIsInt($data['subscribersCount']);
    }
}
