<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;

final class UserPlanningContext extends RawMinkContext
{
    /**
     * @When /^I (?P<action>(?:check|uncheck)) "(?P<time>[^"]+)" availability checkbox$/
     */
    public function checkColumn(string $action, string $time): void
    {
        $page = $this->getSession()->getPage();
        if (false === strtotime($time)) {
            throw new \InvalidArgumentException("Time \"$time\" is not valid.");
        }

        $dateTime = new \DateTime($time);
        $dateTime->setTime((int) $dateTime->format('H'), 0, 0, 0);
        if (1 === (int) $dateTime->format('H') % 2) {
            $dateTime->setTime((int) $dateTime->format('H') - 1, 0, 0, 0);
        }

        $elements = $page->findAll('css', sprintf('table.availability-form-table tbody td[data-from="%d"] input[type="checkbox"]:not(:disabled)', $dateTime->format('U')));
        if (0 === \count($elements)) {
            throw new ElementNotFoundException($this->getSession()->getDriver(), 'form field', 'css', sprintf('%s (%s) (%s)', $time, $dateTime->format('Y-m-d H:i:s'), $dateTime->format('U')));
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
     * @Then /^(?:the )?"(?P<time>[^"]+)" availability checkbox should not exists$/
     */
    public function checkColumnExists(string $time): void
    {
        $page = $this->getSession()->getPage();
        if (false === strtotime($time)) {
            throw new \InvalidArgumentException("Time \"$time\" is not valid.");
        }

        $dateTime = new \DateTime($time);
        $dateTime->setTime((int) $dateTime->format('H'), 0, 0, 0);
        if (1 === (int) $dateTime->format('H') % 2) {
            $dateTime->setTime((int) $dateTime->format('H') - 1, 0, 0, 0);
        }

        $elements = $page->findAll('css', sprintf('table.availability-form-table tbody td[data-from="%d"] input[type="checkbox"]:not(:disabled)', $dateTime->format('U')));
        if (0 < \count($elements)) {
            throw new ExpectationException("The checkbox for \"$time\" is available.", $this->getSession()->getDriver());
        }
    }

    /**
     * @When /^(?:the )?availability checkbox "(?P<time>[^"]+)" should be (?P<action>(?:checked|unchecked))$/
     */
    public function verifyColumn(string $time, string $state): void
    {
        $page = $this->getSession()->getPage();
        if (false === strtotime($time)) {
            throw new \InvalidArgumentException("Time \"$time\" is not valid.");
        }

        $dateTime = new \DateTime($time);
        $dateTime->setTime((int) $dateTime->format('H'), 0, 0, 0);
        if (1 === (int) $dateTime->format('H') % 2) {
            $dateTime->setTime((int) $dateTime->format('H') - 1, 0, 0, 0);
        }

        $locator = sprintf('table.availability-form-table tbody td[data-from="%d"] input[type="checkbox"]:not(:disabled)', $dateTime->format('U'));
        // Reverse the state to ensure no element is checked/unchecked
        $locator .= 'checked' === $state ? ':not(:checked)' : ':checked';
        $count = \count($page->findAll('css', $locator));
        if (0 < $count) {
            throw new ExpectationException(sprintf('%d checkboxes of column "%s" are not %s.', (int) $count, $time, (string) $state), $this->getSession()->getDriver());
        }
    }
}
