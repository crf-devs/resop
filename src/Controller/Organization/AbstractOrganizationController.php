<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractOrganizationController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if (preg_match('/^app_organization_.*$/', $route)) {
            /** @var Request $request */
            $request = $this->get('request_stack')->getCurrentRequest();
            /** @var Organization $currentOrganization */
            $currentOrganization = $request->attributes->get('currentOrganization');
            $organization = $parameters['organization'] ?? null;
            $parameters = array_merge($parameters, ['organization' => $currentOrganization->getId()]);
            if (null !== $organization && $currentOrganization->getId() !== $organization) {
                $parameters['organizationId'] = $organization;
            }
        }

        return parent::generateUrl($route, $parameters, $referenceType);
    }
}
