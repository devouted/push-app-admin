<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConsumerRegisterControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterNewConsumer(): void
    {
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[test-happy-path-' . uniqid() . ']',
            'device_name' => 'iPhone 15',
            'device_model' => 'iPhone15,2',
            'device_os' => 'iOS',
            'device_os_version' => '17.4',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('created_at', $data);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $data['uuid']
        );
    }

    public function testRegisterIdempotentSameExpoToken(): void
    {
        $token = 'ExponentPushToken[test-idempotent-' . uniqid() . ']';

        // First registration
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => $token,
            'device_name' => 'iPhone 15',
            'device_os' => 'iOS',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $first = json_decode($this->client->getResponse()->getContent(), true);

        // Second registration with same expo_token — should update, not create
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => $token,
            'device_name' => 'Pixel 8',
            'device_os' => 'Android',
        ]));

        $this->assertResponseStatusCodeSame(200);
        $second = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($first['uuid'], $second['uuid']);
    }

    public function testRegisterValidationMissingExpoToken(): void
    {
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'device_name' => 'iPhone 15',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterValidationEmptyExpoToken(): void
    {
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => '',
        ]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterValidationEmptyBody(): void
    {
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([]));

        $this->assertResponseStatusCodeSame(422);
    }

    public function testRegisterWithOnlyRequiredField(): void
    {
        $this->client->request('POST', '/api/consumers/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'expo_token' => 'ExponentPushToken[test-minimal-' . uniqid() . ']',
        ]));

        $this->assertResponseStatusCodeSame(201);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('uuid', $data);
        $this->assertArrayHasKey('created_at', $data);
    }
}
