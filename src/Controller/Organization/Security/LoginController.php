<?php

declare(strict_types=1);

namespace App\Controller\Organization\Security;

use App\Entity\User;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/login", name="app_organization_login")
 */
final class LoginController extends AbstractController
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
        if (is_object($this->getUser())) {
            return $this->redirectToRoute($this->getUser() instanceof User ? 'user_home' : 'app_organization_index');
        }

        return $this->render('organization/login.html.twig', [
            'organizations' => $this->organizationRepository->loadActiveOrganizations(),
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
