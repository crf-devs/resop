<?php

declare(strict_types=1);

namespace App\Controller\User\Security;

use App\Entity\Organization;
use App\Entity\User;
use App\Form\Type\UserLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/login", name="app_login")
 */
final class LoginController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    public function __invoke(Session $session): Response
    {
        /** @var Organization|User|null $user */
        $user = $this->getUser();
        if (\is_object($user)) {
            if ($user instanceof User) {
                return $this->redirectToRoute('app_user_home');
            }

            return $this->redirectToRoute('app_organization_dashboard', ['organization' => $user->getId()]);
        }

        $loginForm = $this->createForm(UserLoginType::class, [
            'identifier' => $this->authenticationUtils->getLastUsername(),
            'birthday' => $session->get('_security.last_birthday', '1990-01-01'),
        ]);

        return $this->render('user/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
