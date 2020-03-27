<?php

declare(strict_types=1);

namespace App\EventListener;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class RouteLoggerListener implements EventSubscriberInterface, LoggerAwareInterface
{
    private const ROUTE_HEADER = 'X-Route';

    use LoggerAwareTrait;

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $event->getResponse()->headers->set(self::ROUTE_HEADER, $event->getRequest()->attributes->get('_route', 'null'));
    }
}
