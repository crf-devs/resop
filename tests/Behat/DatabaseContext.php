<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver;

final class DatabaseContext implements Context
{
    /**
     * @BeforeSuite ~@javascript
     */
    public static function beforeSuite(): void
    {
        StaticDriver::setKeepStaticConnections(true);
    }

    /**
     * @BeforeScenario ~@javascript
     */
    public function beforeScenario(): void
    {
        StaticDriver::beginTransaction();
    }

    /**
     * @AfterScenario ~@javascript
     */
    public function afterScenario(): void
    {
        StaticDriver::rollBack();
    }

    /**
     * @AfterSuite ~@javascript
     */
    public static function afterSuite(): void
    {
        StaticDriver::setKeepStaticConnections(false);
    }
}
