<?php

namespace App\EventListener;

use App\Entity\User;
use App\Enum\UserLocale;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

readonly class LocaleListener
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private LocaleAwareInterface  $translator
    ) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();

        $locale = ($user instanceof User) ? $user->getLocale() : UserLocale::EN->value;
        $event->getRequest()->setLocale($locale);
        $this->translator->setLocale($locale);
    }
}
