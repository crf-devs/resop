<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/children", name="app_organization_list", methods={"GET"})
 */
class OrganizationListController extends AbstractController
{
    protected OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    public function __invoke(): Response
    {
        $organization = $this->getUser();
        if (!$organization instanceof Organization || !$organization->isParent()) {
            throw new AccessDeniedException();
        }

        $organizations = $this->organizationRepository->findBy(['parent' => $organization], ['name' => 'ASC']);

        return $this->render('organization/list.html.twig', [
            'organizations' => $organizations,
        ]);
    }
}
