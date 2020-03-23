<?php

declare(strict_types=1);

namespace App\Controller\User\Security;

use App\Form\Type\UserLoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    public function __invoke(): Response
    {
        if (is_object($this->getUser())) {
            return $this->redirectToRoute('user_home');
        }

        $loginForm = $this->createForm(
            UserLoginType::class,
            ['identifier' => $this->authenticationUtils->getLastUsername()]
        );

        return $this->render('user/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
