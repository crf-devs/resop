<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Driver\BrowserKitDriver;
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
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        /** @var MinkContext $minkContext */
        $minkContext = $environment->getContext(MinkContext::class);
        $this->minkContext = $minkContext;
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
            throw new UsernameNotFoundException(\sprintf('%s is not a valid User or Organization.', $username));
        }

        $firewall = $user instanceof Organization ? 'organizations' : 'main';
        $this->session->set(
            "_security_$firewall",
            serialize(new UsernamePasswordToken($user, null, $firewall, $user->getRoles()))
        );
        $this->session->save();

        /** @var BrowserKitDriver $driver */
        $driver = $this->minkContext->getSession()->getDriver();
        $driver->getClient()->getCookieJar()->set(new Cookie($this->session->getName(), $this->session->getId()));
    }
}
