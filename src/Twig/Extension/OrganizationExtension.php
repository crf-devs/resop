<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\Security\Core\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class OrganizationExtension extends AbstractExtension
{
    private Security $security;
    private RoutingExtension $routingExtension;

    public function __construct(Security $security, RoutingExtension $routingExtension)
    {
        $this->security = $security;
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
        /** @var Organization|User|null $user */
        $user = $this->security->getUser();
        if (!preg_match('/^app_organization_.*$/', $name) || !$user instanceof Organization) {
            return $parameters;
        }

        $organization = $parameters['organization'] ?? null;
        $parameters = array_merge($parameters, ['organization' => $user->getId()]);
        if (null !== $organization && $parameters['organization'] !== $organization) {
            $parameters['organizationId'] = $organization;
        }

        return $parameters;
    }
}
