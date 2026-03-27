<?php

namespace App\Controller;

use App\Dto\Response\HealthResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class HealthController extends DefaultController
{
    #[Route('/health', name: 'health', methods: ['GET'])]
    #[OA\Get(
        path: '/api/health',
        description: 'Health check endpoint',
        summary: 'Check API health status',
        security: []
    )]
    #[OA\Response(
        response: 200,
        description: 'API is healthy',
        content: new Model(type: HealthResponse::class)
    )]
    #[OA\Tag(name: 'Health')]
    public function health(): JsonResponse
    {
        return $this->response(new HealthResponse(timestamp: (new \DateTime())->format(\DateTime::ATOM)));
    }
}
