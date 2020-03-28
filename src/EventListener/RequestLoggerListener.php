<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

final class RequestLoggerListener implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onRequest', 255],
                ['logUser'],
            ],
            SecurityEvents::INTERACTIVE_LOGIN => [
                ['onLogin'],
            ],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (self::isHealthCheck($request)) {
            return;
        }

        $this->logger->info(
            'Handle request',
            [
                'host' => $request->getHost(),
                'method' => $request->getMethod(),
                'url' => $request->getUri(),
                'request' => [
                    'headers' => self::getCleanHeaders($request),
                    'query' => $request->query->all(),
                ],
            ]
        );
    }

    public function logUser(RequestEvent $event): void
    {
        if (null === $this->tokenStorage->getToken() || self::isHealthCheck($event->getRequest())) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->logLogIn($user);
    }

    public function onLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->logLogIn($user);
    }

    private function logLogIn(UserInterface $user): void
    {
        if (!$user instanceof \JsonSerializable) {
            return;
        }
        $this->logger->info('User logged in', $user->jsonSerialize());
    }

    private static function isHealthCheck(Request $request): bool
    {
        return false !== strpos((string) $request->headers->get('user-agent'), 'ELB-HealthChecker');
    }

    private static function getCleanHeaders(Request $request): array
    {
        $headers = $request->headers->all();
        if (0 === \strpos($headers['authorization'][0] ?? $headers['authorization'] ?? '', 'Bearer')) {
            $headers['authorization'] = 'Bearer xxx';
        }

        if (0 === \strpos($headers['cookie'][0] ?? $headers['cookie'] ?? '', 'PHPSESSID')) {
            $headers['cookie'] = 'PHPSESSID=xxx';
        }

        return $headers;
    }
}
