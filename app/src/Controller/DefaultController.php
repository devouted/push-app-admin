<?php

namespace App\Controller;

use App\Dto\ResponseDtoInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{
    protected function response(ResponseDtoInterface $dto, int $status = 200): JsonResponse
    {
        return $this->json($dto, $status);
    }
}
