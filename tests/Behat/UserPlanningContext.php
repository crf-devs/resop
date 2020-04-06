<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\MinkContext;

final class UserPlanningContext implements Context
{
    private MinkContext $minkContext;

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
     * @When /^I (?P<action>(?:check|uncheck)) "(?P<day>(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday))" column$/
     */
    public function checkColumn(string $action, string $day): void
    {
        $page = $this->minkContext->getSession()->getPage();
        /** @var int $time */
        $time = strtotime($day);
        $day = (int) date('w', $time) - 1;
        if (-1 === $day) {
            $day = 6;
        }
        $elements = $page->findAll('css', sprintf('table.availability-form-table tbody td[data-day="%d"] input[type="checkbox"]', $day));
        if (0 === \count($elements)) {
            throw new ElementNotFoundException($this->minkContext->getSession()->getDriver(), 'form field', 'id|name|label|value', $day);
        }

        foreach ($elements as $element) {
            if ('check' === $action) {
                $element->check();
            } else {
                $element->uncheck();
            }
        }
    }

    /**
     * @When /^(?:the )?column "(?P<day>(?:monday|tuesday|wednesday|thursday|friday|saturday|sunday))" should be (?P<action>(?:checked|unchecked))$/
     */
    public function verifyColumn(string $day, string $state): void
    {
        $page = $this->minkContext->getSession()->getPage();
        /** @var int $time */
        $time = strtotime($day);
        $day = (int) date('w', $time) - 1;
        if (-1 === $day) {
            $day = 6;
        }
        $locator = sprintf('table.availability-form-table tbody td[data-day="%d"] input[type="checkbox"]', $day);
        $locator .= 'checked' === $state ? ':checked' : ':not(:checked)';
        $elements = $page->findAll('css', $locator);
        if (12 > \count($elements)) {
            throw new ExpectationException(sprintf('Some checkboxes of column "%s" are not checked.', $day), $this->minkContext->getSession()->getDriver());
        }
    }
}
