<?php

declare(strict_types=1);

namespace App\Controller\User\Security;

use App\Form\Type\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/login", name="app_login")
 */
final class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function __invoke(AuthenticationUtils $authenticationUtils): Response
    {
        $loginForm = $this->createForm(
            LoginType::class,
            ['identifier' => $authenticationUtils->getLastUsername()]
        );

        return $this->render('user/login.html.twig', [
            'loginForm' => $loginForm->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
