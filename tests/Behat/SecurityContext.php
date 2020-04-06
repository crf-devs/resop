<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

final class SecurityContext implements Context
{
    private UserRepository $userRepository;
    private OrganizationRepository $organizationRepository;
    private SessionInterface $session;
    private MinkContext $minkContext;

    public function __construct(UserRepository $userRepository, OrganizationRepository $organizationRepository, SessionInterface $session)
    {
        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;
        $this->session = $session;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContext(BeforeScenarioScope $scope): void
    {
        $this->minkContext = $scope->getEnvironment()->getContext(MinkContext::class);
    }

    /**
     * @Given I am authenticated as a user
     */
    public function loginUser(string $username = 'user1@resop.com'): void
    {
        $this->login($username, $this->userRepository);
    }

    /**
     * @Given I am authenticated as an organization
     */
    public function loginOrganization(string $username = 'UL 05'): void
    {
        $this->login($username, $this->organizationRepository);
    }

    /**
     * @Given I am authenticated as :username
     */
    public function login(string $username, UserLoaderInterface $repository = null): void
    {
        if ($repository) {
            $user = $repository->loadUserByUsername($username);
        } elseif (!$user = $this->userRepository->loadUserByUsername($username)) {
            $user = $this->organizationRepository->loadUserByUsername($username);
        }

        if (!$user) {
            throw new UsernameNotFoundException(\sprintf('User %s is not valid.', $username));
        }

        $firewall = $user instanceof Organization ? 'organizations' : 'main';
        $this->session->set(
            "_security_$firewall",
            serialize(new UsernamePasswordToken($user, null, $firewall, $user->getRoles()))
        );
        $this->session->save();

        $this->minkContext
            ->getSession()
            ->getDriver()
            ->getClient()
            ->getCookieJar()
            ->set(new Cookie($this->session->getName(), $this->session->getId()));
    }
}
