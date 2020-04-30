<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\MinkExtension\Context\MinkContext;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;
use FriendsOfBehat\SymfonyExtension\Context\Environment\InitializedSymfonyExtensionEnvironment;
use PantherExtension\Driver\PantherDriver;

final class DatabaseContext implements Context
{
    /**
     * @BeforeSuite
     */
    public static function beforeSuite(): void
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario(BeforeScenarioScope $scope): void
    {
        if (self::isPantherSession($scope)) {
            return;
        }

        StaticDriver::beginTransaction();
    }

    /**
     * @AfterScenario
     */
    public function afterScenario(AfterScenarioScope $scope): void
    {
        if (self::isPantherSession($scope)) {
            return;
        }

        StaticDriver::rollBack();
    }

    /**
     * @AfterSuite
     */
    public static function afterSuite(): void
    {
        StaticDriver::setKeepStaticConnections(false);
    }

    private static function isPantherSession(ScenarioScope $scope): bool
    {
        /** @var InitializedSymfonyExtensionEnvironment $environment */
        $environment = $scope->getEnvironment();
        /** @var MinkContext $minkContext */
        $minkContext = $environment->getContext(MinkContext::class);

        return $minkContext->getSession()->getDriver() instanceof PantherDriver;
    }
}
