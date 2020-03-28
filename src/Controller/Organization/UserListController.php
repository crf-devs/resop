<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users", name="organization_user_list", methods={"GET"})
 */
class UserListController extends AbstractController
{
    protected UserRepository $userRepository;
    protected OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository, UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;
    }

    public function __invoke(Request $request): Response
    {
        $organization = $this->getUser();

        if (!$organization instanceof Organization) {
            throw new AccessDeniedException();
        }

        $childOrganizations = [];
        if (null === $organization->parent) {
            $childOrganizations = $this->organizationRepository->findByParent($organization);
        }

        return $this->render('organization/user-list.html.twig', [
            'organization' => $organization,
            'users' => $this->userRepository->findByOrganizations(array_merge([$organization], $childOrganizations))
        ]);
    }
}
