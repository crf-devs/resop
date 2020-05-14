<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\Organization;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class OrganizationExtension extends AbstractExtension
{
    private RequestStack $requestStack;
    private RoutingExtension $routingExtension;

    public function __construct(RequestStack $requestStack, RoutingExtension $routingExtension)
    {
        $this->requestStack = $requestStack;
        $this->routingExtension = $routingExtension;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'getOrganizationPath']),
            new TwigFunction('url', [$this, 'getOrganizationUrl']),
        ];
    }

    public function getOrganizationPath(string $name, array $parameters = []): string
    {
        return $this->routingExtension->getPath($name, $this->buildParameters($name, $parameters));
    }

    public function getOrganizationUrl(string $name, array $parameters = [], bool $schemeRelative = false): string
    {
        return $this->routingExtension->getUrl($name, $this->buildParameters($name, $parameters), $schemeRelative);
    }

    private function buildParameters(string $name, array $parameters): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!preg_match('/^app_organization_.*$/', $name)
            || !$request
            || !($organization = $request->attributes->get('currentOrganization'))
            || !$organization instanceof Organization
        ) {
            return $parameters;
        }

        $parameter = $parameters['organization'] ?? null;
        $parameters = array_merge($parameters, ['organization' => $organization->getId()]);
        if (null !== $parameter && $parameters['organization'] !== $parameter) {
            $parameters['organizationId'] = $parameter;
        }

        return $parameters;
    }
}
