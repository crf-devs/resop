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
        $dayNumber = (int) date('N', $time) - 1;
        $elements = $page->findAll('css', sprintf('table.availability-form-table tbody td[data-day="%d"] input[type="checkbox"]:not(:disabled)', $dayNumber));
        if (0 === \count($elements)) {
            throw new ElementNotFoundException($this->minkContext->getSession()->getDriver(), 'form field', 'data-day', sprintf('%s (%d)', $day, $dayNumber));
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

        $locator = sprintf('table.availability-form-table tbody td[data-day="%d"] input[type="checkbox"]:not(:disabled)', (int) date('N', $time) - 1);
        // Reverse the state to ensure no element is checked/unchecked
        $locator .= 'checked' === $state ? ':not(:checked)' : ':checked';
        $count = \count($page->findAll('css', $locator));
        if (0 < $count) {
            throw new ExpectationException(sprintf('%d checkboxes of column "%s" are not %s.', (int) $count, (string) $day, (string) $state), $this->minkContext->getSession()->getDriver());
        }
    }
}
