<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConsumerUpdateTokenControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    private function registerConsumer(): array
    {
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[token-update-' . uniqid() . ']',
        ]));

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    public function testUpdateTokenHappyPath(): void
    {
        $consumer = $this->registerConsumer();
        $uuid = $consumer['uuid'];

        $this->client->request('PATCH', "/api/consumers/{$uuid}/token", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[new-token-' . uniqid() . ']',
        ]));

        $this->assertResponseStatusCodeSame(200);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('updated_at', $data);
        $this->assertEquals($uuid, $data['uuid']);
    }

    public function testUpdateTokenNotFoundUuid(): void
    {
        $this->client->request('PATCH', '/api/consumers/00000000-0000-0000-0000-000000000000/token', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[some-token]',
        ]));

        $this->assertResponseStatusCodeSame(404);
    }

    public function testUpdateTokenValidationMissingExpoToken(): void
    {
        $consumer = $this->registerConsumer();
        $uuid = $consumer['uuid'];

        $this->client->request('PATCH', "/api/consumers/{$uuid}/token", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateTokenValidationEmptyExpoToken(): void
    {
        $consumer = $this->registerConsumer();
        $uuid = $consumer['uuid'];

        $this->client->request('PATCH', "/api/consumers/{$uuid}/token", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => '',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }
}
