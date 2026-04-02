<?php

namespace App\Controller;

use App\Dto\Response\AdminConsumerItemResponse;
use App\Dto\Response\AdminConsumerListResponse;
use App\Entity\Consumer;
use App\Repository\ConsumerRepository;
use App\Repository\SubscriptionRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/consumers')]
#[IsGranted('ROLE_ADMIN')]
class AdminConsumerController extends DefaultController
{
    public function __construct(
        private readonly ConsumerRepository $consumerRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
    ) {}

    #[Route('', name: 'admin_consumers_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/consumers',
        summary: 'List all consumers (admin)',
        description: 'Returns paginated list of all non-deleted consumers with active subscription count'
    )]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Consumers list', content: new Model(type: AdminConsumerListResponse::class))]
    #[OA\Tag(name: 'Admin Consumers')]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $consumers = $this->consumerRepository->findAllAdmin($page, $limit);
        $total = $this->consumerRepository->countAllAdmin();

        $items = array_map(fn(Consumer $c) => AdminConsumerItemResponse::fromEntity(
            $c,
            $this->subscriptionRepository->countActiveByConsumer($c),
        ), $consumers);

        return $this->response(new AdminConsumerListResponse($items, $total, $page, $limit));
    }
}
