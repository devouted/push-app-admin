<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExpoPushService
{
    private const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @param string[] $expoTokens
     * @return array{sent: int, errors: string[]} List of tokens that returned DeviceNotRegistered
     */
    public function sendPush(array $expoTokens, string $title, string $body, string $notificationId, ?string $imageUrl = null): array
    {
        if (empty($expoTokens)) {
            return ['sent' => 0, 'errors' => []];
        }

        $deviceNotRegistered = [];
        $sent = 0;

        foreach (array_chunk($expoTokens, self::BATCH_SIZE) as $batch) {
            $messages = [];
            foreach ($batch as $token) {
                $message = [
                    'to' => $token,
                    'title' => $title,
                    'body' => $body,
                    'data' => ['notification_id' => $notificationId],
                ];
                if ($imageUrl) {
                    $message['image'] = $imageUrl;
                }
                $messages[] = $message;
            }

            try {
                $response = $this->httpClient->request('POST', self::EXPO_API_URL, [
                    'json' => $messages,
                    'headers' => [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                ]);

                $result = $response->toArray(false);
                $data = $result['data'] ?? [];

                foreach ($data as $i => $ticket) {
                    if (($ticket['status'] ?? '') === 'ok') {
                        $sent++;
                    } elseif (($ticket['details']['error'] ?? '') === 'DeviceNotRegistered') {
                        $deviceNotRegistered[] = $batch[$i];
                        $this->logger->warning('Expo DeviceNotRegistered', ['token' => $batch[$i]]);
                    } else {
                        $this->logger->error('Expo push error', [
                            'token' => $batch[$i],
                            'ticket' => $ticket,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                $this->logger->error('Expo Push API request failed', [
                    'error' => $e->getMessage(),
                    'batch_size' => count($batch),
                ]);
            }
        }

        return ['sent' => $sent, 'errors' => $deviceNotRegistered];
    }
}
