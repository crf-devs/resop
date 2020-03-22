<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="app_organization_index")
 */
final class IndexController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('organization/home.html.twig');
    }
}
