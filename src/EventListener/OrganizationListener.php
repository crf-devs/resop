<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Repository\OrganizationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class OrganizationListener implements EventSubscriberInterface
{
    private OrganizationRepository $organizationRepository;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(OrganizationRepository $organizationRepository, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->organizationRepository = $organizationRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!($route = $request->attributes->get('_route')) || !preg_match('/^app_organization_.*$/', $route)) {
            return;
        }

        $id = $request->attributes->getInt('organization');
        $organization = $this->organizationRepository->find($id);
        if (!$organization) {
            throw new NotFoundHttpException(sprintf('Organization with id "%d" not found.', $id));
        }

        if (!$this->authorizationChecker->isGranted('ROLE_ORGANIZATION', $organization)) {
            throw new AccessDeniedException('Access denied.');
        }

        $request->attributes->set('currentOrganization', $organization);
        $request->attributes->set('organization', $organization);

        if (null === ($organizationId = $request->query->get('organizationId'))) {
            return;
        }

        $organization = $this->organizationRepository->find($organizationId);
        if (!$organization) {
            throw new NotFoundHttpException(sprintf('Organization with id "%d" not found.', $organizationId));
        }

        $request->attributes->set('organization', $organization);
    }
}
