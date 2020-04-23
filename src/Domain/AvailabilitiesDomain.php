<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilityInterface;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;

final class AvailabilitiesDomain
{
    public string $slotInterval;
    public array $availabilityDomains = [];

    /**
     * @param AvailabilityInterface[] $availabilities
     *
     * @return AvailabilitiesDomain
     *
     * @throws \Exception
     */
    public static function generate(\DateTimeImmutable $start, \DateTimeImmutable $end, string $slotInterval, array $availabilities = [], ?\DateInterval $disabledIntervalFromNow = null): self
    {
        $period = new \DatePeriod(
            $start,
            \DateInterval::createFromDateString($slotInterval),
            $end
        );

        $availabilityDomains = [];
        foreach ($period as $date) {
            $availabilityEntity = null;
            foreach ($availabilities as $k => $availability) {
                if ($availability->getStartTime()->format('Y-m-d H:i') === $date->format('Y-m-d H:i')) {
                    $availabilityEntity = $availability;
                    unset($availabilities[$k]);

                    break;
                }
            }

            $availabilityDomains[] = new AvailabilityDomain($date, $availabilityEntity, $disabledIntervalFromNow);
        }

        return new self($availabilityDomains, $slotInterval);
    }

    /**
     * @param AvailabilityDomain[] $availabilityDomains
     */
    public function __construct(array $availabilityDomains, string $slotInterval)
    {
        Assertion::allIsInstanceOf($availabilityDomains, AvailabilityDomain::class);

        $this->availabilityDomains = $availabilityDomains;
        $this->slotInterval = $slotInterval;
    }

    public function compute(EntityManagerInterface $em, string $availabilityClass, object $object): void
    {
        foreach ($this->availabilityDomains as $availabilityDomain) {
            if ($availabilityDomain->tick && null === $availabilityDomain->availability) {
                $em->persist(new $availabilityClass(
                    null,
                    $object,
                    $availabilityDomain->date,
                    $availabilityDomain->date->add(\DateInterval::createFromDateString($this->slotInterval)),
                    AvailabilityInterface::STATUS_AVAILABLE
                ));
            } elseif (!$availabilityDomain->tick && null !== $availabilityDomain->availability) {
                $em->remove($availabilityDomain->availability);
            }
        }
    }
}
