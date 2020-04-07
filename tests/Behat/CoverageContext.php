<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Text;

final class CoverageContext implements Context
{
    private static ?CodeCoverage $coverage = null;

    /**
     * @BeforeSuite
     */
    public static function setup(): void
    {
        if (!\extension_loaded('pcov') || !getenv('COVERAGE')) {
            return;
        }

        $filter = new Filter();
        $filter->addDirectoryToWhitelist(__DIR__.'/../../src');
        self::$coverage = new CodeCoverage(null, $filter);
    }

    /**
     * @AfterSuite
     */
    public static function teardown(): void
    {
        if (null === self::$coverage) {
            return;
        }

        (new Clover())->process(self::$coverage, __DIR__.'/../../var/behat/coverage.xml');

        $buffer = ob_get_clean();
        echo (new Text())->process(self::$coverage, true);
        ob_start();
        echo $buffer;
    }

    /**
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope): void
    {
        if (null === self::$coverage) {
            return;
        }
        self::$coverage->start("{$scope->getFeature()->getTitle()}::{$scope->getScenario()->getTitle()}");
    }

    /**
     * @AfterScenario
     */
    public function after(): void
    {
        if (null === self::$coverage) {
            return;
        }
        self::$coverage->stop();
    }
}
