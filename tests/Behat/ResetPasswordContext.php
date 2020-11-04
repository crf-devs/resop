<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

final class ResetPasswordContext extends RawMinkContext
{
    private RouterInterface $router;
    private UserProviderInterface $userProvider;
    private ResetPasswordHelperInterface $resetPasswordHelper;

    public function __construct(RouterInterface $router, UserProviderInterface $userProvider, ResetPasswordHelperInterface $resetPasswordHelper)
    {
        $this->router = $router;
        $this->userProvider = $userProvider;
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    /**
     * @When I go to the reset password page of :username
     */
    public function generateToken(string $username): void
    {
        $this->visitPath(
            $this->router->generate('app_reset_password', [
                'token' => $this->resetPasswordHelper->generateResetToken(
                    $this->userProvider->loadUserByUsername($username)
                )->getToken(),
            ])
        );
    }
}
