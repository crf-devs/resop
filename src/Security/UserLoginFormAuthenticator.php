<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserLoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    public const SECURITY_LAST_BIRTHDAY = '_security.last_birthday';

    private UserRepository $userRepository;
    private RouterInterface $router;
    private CsrfTokenManagerInterface $csrfTokenManager;
    private ValidatorInterface $validator;
    private UserPasswordEncoderInterface $userPasswordEncoder;

    public function __construct(UserRepository $userRepository, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, ValidatorInterface $validator, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->validator = $validator;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route') && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $loginCredentials = (array) $request->request->get('user_login');

        $credentials = [
            'identifier' => $loginCredentials['identifier'] ?? null,
            'birthday' => $loginCredentials['birthday'] ?? false ? $this->formatBirthday($loginCredentials['birthday']) : null,
            'password' => $loginCredentials['password'] ?? null,
            'csrf_token' => $loginCredentials['_token'] ?? null,
        ];

        $request->getSession()->set(Security::LAST_USERNAME, $credentials['identifier']);
        $request->getSession()->set(self::SECURITY_LAST_BIRTHDAY, $credentials['birthday']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $csrfToken = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($csrfToken)) {
            throw new InvalidCsrfTokenException();
        }

        if (!(
            0 === $this->validator->validate($credentials['identifier'], [new Assert\Regex(['pattern' => User::NIVOL_FORMAT])])->count()
            || 0 === $this->validator->validate($credentials['identifier'], [new Assert\Email()])->count()
        )) {
            throw new BadCredentialsException();
        }

        return $this->userRepository->loadUserByUsername($credentials['identifier']);
    }

    /**
     * @param array              $credentials
     * @param UserInterface|User $user
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        /** @var User $user */
        if (null === $user->getPassword()) {
            return $credentials['birthday'] === $user->birthday;
        }

        return !empty($credentials['password']) && $this->userPasswordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
    {
        return new RedirectResponse($this->router->generate('app_user_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($exception instanceof UsernameNotFoundException) {
            return new RedirectResponse($this->router->generate('app_user_create'));
        }

        return parent::onAuthenticationFailure($request, $exception);
    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }

    private function formatBirthday(array $birthdayArray): string
    {
        $day = sprintf('%02d', $birthdayArray['day']);
        $month = sprintf('%02d', $birthdayArray['month']);
        $year = $birthdayArray['year'];

        return $year.'-'.$month.'-'.$day;
    }
}
