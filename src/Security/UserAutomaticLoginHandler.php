<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

final class UserAutomaticLoginHandler
{
    private GuardAuthenticatorHandler $guardHandler;
    private UserLoginFormAuthenticator $formAuthenticator;

    public function __construct(
        GuardAuthenticatorHandler $guardHandler,
        UserLoginFormAuthenticator $formAuthenticator
    ) {
        $this->guardHandler = $guardHandler;
        $this->formAuthenticator = $formAuthenticator;
    }

    /**
     * @param User|UserInterface $user
     */
    public function handleAuthentication(Request $request, UserInterface $user): Response
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException(sprintf('Method %s only accepts a %s instance as its second argument.', __METHOD__, User::class));
        }

        $response = $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $this->formAuthenticator,
            'main'
        );

        if (!$response instanceof Response) {
            throw new \RuntimeException('Guard handler must return a Response object.');
        }

        return $response;
    }
}
