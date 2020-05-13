<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class AbstractOrganizationController extends AbstractController
{
    /**
     * {@inheritdoc}
     */
    protected function generateUrl(string $route, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        if (preg_match('/^app_organization_.*$/', $route)) {
            /** @var Organization $user */
            $user = $this->getUser();
            $parameters = array_merge(['organization' => $user->getId()], $parameters);
        }

        return parent::generateUrl($route, $parameters, $referenceType);
    }
}
