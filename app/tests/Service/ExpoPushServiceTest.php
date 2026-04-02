<?php

namespace App\Tests\Service;

use App\Service\ExpoPushService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ExpoPushServiceTest extends TestCase
{
    public function testSendPushEmptyTokens(): void
    {
        $service = new ExpoPushService(new MockHttpClient(), new NullLogger());
        $result = $service->sendPush([], 'Title', 'Body', 'notif-id');

        $this->assertEquals(0, $result['sent']);
        $this->assertEmpty($result['errors']);
    }

    public function testSendPushSuccessful(): void
    {
        $response = new MockResponse(json_encode([
            'data' => [
                ['status' => 'ok', 'id' => 'ticket-1'],
                ['status' => 'ok', 'id' => 'ticket-2'],
            ],
        ]));

        $service = new ExpoPushService(new MockHttpClient($response), new NullLogger());
        $result = $service->sendPush(
            ['ExponentPushToken[token1]', 'ExponentPushToken[token2]'],
            'Title',
            'Body',
            'notif-123',
        );

        $this->assertEquals(2, $result['sent']);
        $this->assertEmpty($result['errors']);
    }

    public function testSendPushDeviceNotRegistered(): void
    {
        $response = new MockResponse(json_encode([
            'data' => [
                ['status' => 'ok', 'id' => 'ticket-1'],
                ['status' => 'error', 'message' => 'not registered', 'details' => ['error' => 'DeviceNotRegistered']],
            ],
        ]));

        $service = new ExpoPushService(new MockHttpClient($response), new NullLogger());
        $result = $service->sendPush(
            ['ExponentPushToken[good]', 'ExponentPushToken[bad]'],
            'Title',
            'Body',
            'notif-456',
        );

        $this->assertEquals(1, $result['sent']);
        $this->assertCount(1, $result['errors']);
        $this->assertEquals('ExponentPushToken[bad]', $result['errors'][0]);
    }

    public function testSendPushBatching(): void
    {
        $callCount = 0;
        $factory = function () use (&$callCount) {
            $callCount++;
            $data = array_fill(0, min(100, 150 - ($callCount - 1) * 100), ['status' => 'ok', 'id' => 'ticket']);
            return new MockResponse(json_encode(['data' => $data]));
        };

        $service = new ExpoPushService(new MockHttpClient($factory), new NullLogger());
        $tokens = array_map(fn($i) => "ExponentPushToken[token$i]", range(1, 150));

        $result = $service->sendPush($tokens, 'Title', 'Body', 'notif-789');

        $this->assertEquals(2, $callCount);
        $this->assertEquals(150, $result['sent']);
    }

    public function testSendPushHttpError(): void
    {
        $response = new MockResponse('Server Error', ['http_code' => 500]);

        $service = new ExpoPushService(new MockHttpClient($response), new NullLogger());
        $result = $service->sendPush(
            ['ExponentPushToken[token1]'],
            'Title',
            'Body',
            'notif-err',
        );

        $this->assertEquals(0, $result['sent']);
        $this->assertEmpty($result['errors']);
    }

    public function testSendPushWithImageUrl(): void
    {
        $requestBody = null;
        $response = new MockResponse(json_encode([
            'data' => [['status' => 'ok', 'id' => 'ticket-1']],
        ]));

        $factory = function ($method, $url, $options) use ($response, &$requestBody) {
            $requestBody = $options['body'] ?? null;
            return $response;
        };

        $service = new ExpoPushService(new MockHttpClient($factory), new NullLogger());
        $service->sendPush(
            ['ExponentPushToken[token1]'],
            'Title',
            'Body',
            'notif-img',
            'https://example.com/image.png',
        );

        $this->assertNotNull($requestBody);
        $decoded = json_decode($requestBody, true);
        $this->assertEquals('https://example.com/image.png', $decoded[0]['image']);
    }
}
