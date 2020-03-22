<?php

declare(strict_types=1);

namespace App\Controller\Organization\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/logout", name="app_organization_logout")
 */
final class LogoutController extends AbstractController
{
    public function __invoke(): Response
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
