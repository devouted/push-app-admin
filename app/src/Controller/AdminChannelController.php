<?php

namespace App\Controller;

use App\Dto\Request\BlockChannelRequest;
use App\Dto\Response\AdminChannelListItemResponse;
use App\Dto\Response\AdminChannelListResponse;
use App\Dto\Response\AdminChannelResponse;
use App\Dto\Response\ErrorResponse;
use App\Entity\Channel;
use App\Enum\ChannelStatus;
use App\Repository\ChannelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/channels')]
#[IsGranted('ROLE_ADMIN')]
class AdminChannelController extends DefaultController
{
    public function __construct(
        private readonly ChannelRepository $channelRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('', name: 'admin_channels_list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/admin/channels',
        summary: 'List all channels (admin)',
        description: 'Returns paginated list of all non-deleted channels with optional status filter'
    )]
    #[OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['active', 'blocked', 'inactive']))]
    #[OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1))]
    #[OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20))]
    #[OA\Response(response: 200, description: 'Channels list', content: new Model(type: AdminChannelListResponse::class))]
    #[OA\Tag(name: 'Admin Channels')]
    public function list(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        if ($status !== null && !in_array($status, ['active', 'blocked', 'inactive'], true)) {
            $status = null;
        }
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $channels = $this->channelRepository->findAllAdmin($status, $page, $limit);
        $total = $this->channelRepository->countAllAdmin($status);

        return $this->response(new AdminChannelListResponse(
            array_map(fn(Channel $c) => AdminChannelListItemResponse::fromEntity($c), $channels),
            $total,
            $page,
            $limit,
        ));
    }

    #[Route('/{id}/block', name: 'admin_channels_block', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/admin/channels/{id}/block',
        summary: 'Block a channel',
        description: 'Blocks a channel with a reason. Channel must exist, not be deleted, and not already blocked.'
    )]
    #[OA\RequestBody(content: new Model(type: BlockChannelRequest::class))]
    #[OA\Response(response: 200, description: 'Channel blocked', content: new Model(type: AdminChannelResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 409, description: 'Channel already blocked', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 422, description: 'Validation error', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Admin Channels')]
    public function block(string $id, #[MapRequestPayload] BlockChannelRequest $request): JsonResponse
    {
        $channel = $this->findChannel($id);

        if ($channel->getStatus() === ChannelStatus::BLOCKED) {
            throw new ConflictHttpException('Channel is already blocked');
        }

        $channel->setStatus(ChannelStatus::BLOCKED);
        $channel->setBlockedReason($request->reason);
        $this->em->flush();

        return $this->response(AdminChannelResponse::fromEntity($channel));
    }

    #[Route('/{id}/unblock', name: 'admin_channels_unblock', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/admin/channels/{id}/unblock',
        summary: 'Unblock a channel',
        description: 'Unblocks a previously blocked channel. Channel must be in blocked status.'
    )]
    #[OA\Response(response: 200, description: 'Channel unblocked', content: new Model(type: AdminChannelResponse::class))]
    #[OA\Response(response: 404, description: 'Channel not found', content: new Model(type: ErrorResponse::class))]
    #[OA\Response(response: 409, description: 'Channel is not blocked', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Admin Channels')]
    public function unblock(string $id): JsonResponse
    {
        $channel = $this->findChannel($id);

        if ($channel->getStatus() !== ChannelStatus::BLOCKED) {
            throw new ConflictHttpException('Channel is not blocked');
        }

        $channel->setStatus(ChannelStatus::ACTIVE);
        $channel->setBlockedReason(null);
        $this->em->flush();

        return $this->response(AdminChannelResponse::fromEntity($channel));
    }

    private function findChannel(string $id): Channel
    {
        $channel = $this->channelRepository->find($id);

        if (!$channel || $channel->isDeleted()) {
            throw $this->createNotFoundException('Channel not found');
        }

        return $channel;
    }
}
