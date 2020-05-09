<?php

declare(strict_types=1);

namespace App\Tests\Behat;

use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;

final class OrganizationPlanningContext extends RawMinkContext
{
    /**
     * Example: availability of user "Jane DOE" should be "available" on "next monday" at 02:00
     * Example: availability of asset "VL - 75016" should be "locked" on "next monday" at 02:00
     *
     * @Then /^availability of (user|asset) "(?P<username>(?:[^"]|\\")*)" should be "(available|booked|locked|unknown)" on "(?P<date>(?:[^"]|\\")*)" at ([0-9]{2}\:[0-9]{2})$/
     *
     * @throws ExpectationException
     */
    public function checkResourceAvailability(string $resourceType, string $username, string $expectedAvailability, string $date, string $time): void
    {
        $thResource = $this->getSession()->getPage()->find('css', sprintf('th:contains("%s")', $username));

        if (null === $thResource || $thResource->getParent()->getAttribute('data-type') !== sprintf('%ss', $resourceType)) {
            throw new ExpectationException(sprintf('%s "%s" not found in the planning', ucfirst($resourceType), $username), $this->getSession());
        }

        try {
            $datetime = new \DateTimeImmutable($date);
        } catch (\Exception $exception) {
            throw new ExpectationException(sprintf('"%s" is not a valid date', $date), $this->getSession());
        }

        $slot = $thResource->getParent()->find(
            'css',
            sprintf('td[data-from="%s %s"]', $datetime->format('Y-m-d'), $time)
        );

        if (null === $slot) {
            throw new ExpectationException(sprintf('Slot "%s" at %s is not displayed on planning', $date, $time), $this->getSession());
        }

        if ($expectedAvailability !== $currentAvailability = $slot->getAttribute('data-status')) {
            throw new ExpectationException(sprintf('Availability of %s "%s" for slot "%s" at %s is %s. Expected: %s', $resourceType, $username, $date, $time, $currentAvailability, $expectedAvailability), $this->getSession());
        }
    }
}
