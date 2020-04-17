<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\Organization;
use App\Form\Type\OrganizationSelectorType;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{id}/users", name="app_organization_user_list", methods={"GET"})
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

    public function __invoke(Request $request, Organization $organization): Response
    {
        $currentOrganization = $this->getUser();
        if (!$currentOrganization instanceof Organization) {
            throw new AccessDeniedException();
        }

        if ($currentOrganization !== $organization && $organization->parent !== $currentOrganization) {
            throw new AccessDeniedException('You cannot manage this organization');
        }

        $organizationSelectorForm = $this->createForm(
            OrganizationSelectorType::class,
            ['organization' => $organization],
            ['currentOrganization' => $currentOrganization, 'route_to_redirect' => $request->attributes->get('_route')]
        );

        return $this->render(
            'organization/user/user-list.html.twig',
            [
                'organization' => $organization,
                'users' => $this->userRepository->findByOrganization($organization),
                'organization_selector_form' => $organizationSelectorForm->createView(),
            ]
        );
    }
}
