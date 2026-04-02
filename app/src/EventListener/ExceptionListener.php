<?php

namespace App\EventListener;

use App\Dto\Response\ErrorResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;

readonly class ExceptionListener
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    /**
     * @throws ExceptionInterface
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = match(true) {
            $exception instanceof AuthenticationException => 401,
            $exception instanceof AccessDeniedException => 403,
            $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
            default => 500
        };

        $errorResponse = new ErrorResponse(
            $statusCode,
            $exception->getMessage(),
            $this->getErrorType($statusCode),
        );

        $response = new JsonResponse(
            json_decode($this->serializer->serialize($errorResponse, 'json'), true),
            $statusCode
        );

        $event->setResponse($response);
    }

    private function getErrorType(int $statusCode): string
    {
        return match($statusCode) {
            400 => 'bad_request',
            401 => 'unauthorized',
            403 => 'forbidden',
            404 => 'not_found',
            409 => 'conflict',
            422 => 'validation_error',
            500 => 'internal_server_error',
            default => 'error'
        };
    }
}
