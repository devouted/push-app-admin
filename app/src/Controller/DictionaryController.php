<?php

namespace App\Controller;

use App\Dto\Response\ErrorResponse;
use App\Dto\Response\LocalesResponse;
use App\Dto\Response\RolesResponse;
use App\Dto\Response\TranslationsResponse;
use App\Enum\UserLocale;
use App\Enum\UserRole;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/dictionaries')]
class DictionaryController extends DefaultController
{
    private const DOMAINS = ['messages', 'validators', 'security'];

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {}

    #[Route('/locales', name: 'dictionaries_locales', methods: ['GET'])]
    #[OA\Get(
        path: '/api/dictionaries/locales',
        summary: 'Get available locales',
        description: 'Returns list of available language codes',
        security: []
    )]
    #[OA\Response(
        response: 200,
        description: 'List of available locales',
        content: new Model(type: LocalesResponse::class)
    )]
    #[OA\Tag(name: 'Dictionary')]
    public function locales(): JsonResponse
    {
        return $this->response(new LocalesResponse(UserLocale::getValues()));
    }

    #[Route('/translations/{locale}', name: 'dictionaries_translations', methods: ['GET'])]
    #[OA\Get(
        path: '/api/dictionaries/translations/{locale}',
        summary: 'Get all translations for locale',
        description: 'Returns all translations grouped by domain (messages, validators, security)',
        security: []
    )]
    #[OA\Parameter(
        name: 'locale',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', enum: UserLocale::class)
    )]
    #[OA\Response(
        response: 200,
        description: 'Translations grouped by domain',
        content: new Model(type: TranslationsResponse::class)
    )]
    #[OA\Response(response: 400, description: 'Invalid locale', content: new Model(type: ErrorResponse::class))]
    #[OA\Tag(name: 'Dictionary')]
    public function translations(string $locale): JsonResponse
    {
        if (!in_array($locale, UserLocale::getValues())) {
            throw new BadRequestHttpException($this->translator->trans('error.invalid_locale', [], 'messages'));
        }

        $response = new TranslationsResponse(
            $this->loadDomainTranslations('messages', $locale),
            $this->loadDomainTranslations('validators', $locale),
            $this->loadDomainTranslations('security', $locale)
        );

        return $this->response($response);
    }

    #[Route('/roles', name: 'dictionaries_roles', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[OA\Get(path: '/api/dictionaries/roles', summary: 'Get available roles', description: 'Returns list of available user roles')]
    #[OA\Response(response: 200, description: 'List of available roles', content: new Model(type: RolesResponse::class))]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    #[OA\Tag(name: 'Dictionary')]
    public function roles(): JsonResponse
    {
        return $this->response(new RolesResponse(UserRole::getValues()));
    }

    private function loadDomainTranslations(string $domain, string $locale): array
    {
        $catalogue = $this->translator->getCatalogue($locale);
        return $catalogue->all($domain);
    }
}
