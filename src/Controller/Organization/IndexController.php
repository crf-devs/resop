<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_index", methods={"GET"})
 */
final class IndexController extends AbstractOrganizationController
{
    public function __invoke(): Response
    {
        return $this->redirectToRoute('app_organization_dashboard');
    }
}
