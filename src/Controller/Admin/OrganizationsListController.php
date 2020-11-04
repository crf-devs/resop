<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organizations", name="app_admin_organizations", methods={"GET"})
 */
final class OrganizationsListController extends AbstractController
{
    public function __invoke(OrganizationRepository $organizationRepository): Response
    {
        return $this->render('admin/organizations.html.twig', [
            'organizations' => $organizationRepository->findAllWithParent(),
        ]);
    }
}
