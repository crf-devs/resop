<?php

declare(strict_types=1);

namespace App\Controller\Organization\Security;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/login", name="app_organization_login")
 */
final class LoginController extends AbstractOrganizationController
{
    private AuthenticationUtils $authenticationUtils;
    private OrganizationRepository $organizationRepository;

    public function __construct(AuthenticationUtils $authenticationUtils, OrganizationRepository $organizationRepository)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->organizationRepository = $organizationRepository;
    }

    public function __invoke(): Response
    {
        /** @var Organization|User|null $user */
        $user = $this->getUser();
        if (\is_object($user)) {
            if ($user instanceof User) {
                return $this->redirectToRoute('app_user_home');
            }

            return $this->redirectToRoute('app_organization_dashboard', ['organization' => $user->getId()]);
        }

        return $this->render('organization/login.html.twig', [
            'organizations' => $this->organizationRepository->loadActiveOrganizations(),
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
