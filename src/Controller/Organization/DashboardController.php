<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{organization<\d+>}", name="app_organization_dashboard", methods={"GET"})
 * @Security("is_granted('ROLE_ORGANIZATION', organization)")
 */
final class DashboardController extends AbstractController
{
    public function __invoke(Organization $organization): Response
    {
        return $this->render('organization/home.html.twig', [
            'organization' => $organization,
        ]);
    }
}
