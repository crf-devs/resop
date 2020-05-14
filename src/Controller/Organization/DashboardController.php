<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * {organization} parameter is useless for the moment, but will be useful in ticket https://github.com/crf-devs/resop/issues/338
 *
 * @Route("/{organization<\d+>}", name="app_organization_dashboard", methods={"GET"})
 */
final class DashboardController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('organization/home.html.twig');
    }
}
