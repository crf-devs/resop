<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\Organization;
use App\Form\Factory\OrganizationSelectorFormFactory;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use App\Security\Voter\OrganizationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{organization}/users", name="app_organization_user_list", methods={"GET"})
 * @IsGranted(OrganizationVoter::CAN_LIST_ASSETS, subject="organization")
 */
class UserListController extends AbstractController
{
    protected UserRepository $userRepository;
    protected OrganizationRepository $organizationRepository;
    private OrganizationSelectorFormFactory $organizationSelectorFormFactory;

    public function __construct(OrganizationRepository $organizationRepository, UserRepository $userRepository, OrganizationSelectorFormFactory $organizationSelectorFormFactory)
    {
        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;
        $this->organizationSelectorFormFactory = $organizationSelectorFormFactory;
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

        return $this->render(
            'organization/user/user-list.html.twig',
            [
                'organization' => $organization,
                'users' => $this->userRepository->findByOrganization($organization),
                'organization_selector_form' => $this->organizationSelectorFormFactory->createForm(
                    $organization,
                    $currentOrganization,
                    $request->attributes->get('_route')
                )->createView(),
            ]
        );
    }
}
