<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;

trait MinkContextTrait
{
    private ?MinkContext $minkContext = null;

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope): void
    {
        /** @var InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();
        $minkContext = $environment->getContext(MinkContext::class);

        if (!$minkContext instanceof MinkContext) {
            throw new \RuntimeException('Invalid mink context');
        }

        $this->minkContext = $minkContext;
    }

    private function getMinkContext(): MinkContext
    {
        if (null === $this->minkContext) {
            throw new \RuntimeException('Invalid mink context value');
        }

        return $this->minkContext;
    }
}
