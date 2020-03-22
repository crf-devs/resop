<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;

final class UserLoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private UserRepository $userRepository;

    private RouterInterface $router;

    private CsrfTokenManagerInterface $csrfTokenManager;

    public function __construct(UserRepository $userRepository,
                                RouterInterface $router,
                                CsrfTokenManagerInterface $csrfTokenManager)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
    }

    public function supports(Request $request)
    {
        return $request->attributes->get('_route') === 'app_login'
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $loginCredentials = $request->request->get('login');

        $credentials = [
            'identifier' => $loginCredentials['identifier'],
            'birthday' => $this->formatBirthday($loginCredentials['birthday']),
            'csrf_token' => $loginCredentials['_token'],
        ];

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['identifier']);

        return $credentials;
    }

    private function formatBirthday(array $birthdayArray): string
    {
        $day = sprintf("%02d", $birthdayArray['day']);
        $month = sprintf("%02d", $birthdayArray['month']);
        $year = $birthdayArray['year'];

        return $birthday = $year . '-' . $month . '-' . $day;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $csrfToken = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new InvalidCsrfTokenException();
        }

        return $this->userRepository->findUserByIdentifier($credentials['identifier']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        //TODO Fix "Invalid credentials." translation key
        return $credentials['birthday'] === $user->getBirthday();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse($this->router->generate('user_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof UsernameNotFoundException) {
            return new RedirectResponse($this->router->generate('user_new'));
        }

        return parent::onAuthenticationFailure($request, $exception);
    }


    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }
}
