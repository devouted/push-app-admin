<?php

namespace App\Tests\Traits;

trait AuthenticatedApiTestTrait
{
    /**
     * Login as admin and return JWT token
     *
     * @return string JWT token
     */
    protected function loginAsAdmin(): string
    {
        $this->client->request('POST', '/api/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'Test@1234'
        ]));
        
        return json_decode($this->client->getResponse()->getContent(), true)['token'];
    }

    /**
     * Get authorization headers with Bearer token
     *
     * @param string $token JWT token
     * @return array HTTP headers
     */
    protected function getAuthHeaders(string $token): array
    {
        return ['HTTP_AUTHORIZATION' => 'Bearer ' . $token];
    }

    /**
     * Parse JSON response from client
     *
     * @return array Decoded JSON response
     */
    protected function getJsonResponse(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }

    /**
     * Assert error response structure and code
     *
     * @param int $expectedCode Expected HTTP status code
     * @return void
     */
    protected function assertErrorResponse(int $expectedCode): void
    {
        $data = $this->getJsonResponse();
        $this->assertArrayHasKey('code', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('type', $data);
        $this->assertEquals($expectedCode, $data['code']);
    }
}
