<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilityInterface;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;

final class AvailabilitiesDomain
{
    public const SLOT_INTERVAL = 'PT2H';

    public array $availabilityDomains = [];

    /**
     * @param AvailabilityInterface[] $availabilities
     *
     * @return AvailabilitiesDomain
     *
     * @throws \Exception
     */
    public static function generate(\DateTimeImmutable $start, \DateTimeImmutable $end, array $availabilities = [], ?\DateInterval $disabledIntervalFromNow = null): self
    {
        $period = new \DatePeriod(
            $start,
            new \DateInterval(self::SLOT_INTERVAL),
            $end
        );

        $availabilityDomains = [];
        foreach ($period as $date) {
            $availabilityEntity = null;
            foreach ($availabilities as $k => $availibility) {
                if ($availibility->getStartTime()->format('Y-m-d H:i') === $date->format('Y-m-d H:i')) {
                    $availabilityEntity = $availibility;
                    unset($availabilities[$k]);

                    break;
                }
            }

            $availabilityDomains[] = new AvailabilityDomain($date, $availabilityEntity, $disabledIntervalFromNow);
        }

        return new self($availabilityDomains);
    }

    /**
     * @param AvailabilityDomain[] $availabilityDomains
     */
    public function __construct(array $availabilityDomains)
    {
        Assertion::allIsInstanceOf($availabilityDomains, AvailabilityDomain::class);

        $this->availabilityDomains = $availabilityDomains;
    }

    public function compute(EntityManagerInterface $em, string $avaibilityClass, object $object): void
    {
        foreach ($this->availabilityDomains as $availabilityDomain) {
            if ($availabilityDomain->tick && null === $availabilityDomain->availability) {
                $em->persist(new $avaibilityClass(
                    null,
                    $object,
                    $availabilityDomain->date,
                    $availabilityDomain->date->add(new \DateInterval(self::SLOT_INTERVAL)),
                    AvailabilityInterface::STATUS_AVAILABLE
                ));
            } elseif (!$availabilityDomain->tick && null !== $availabilityDomain->availability) {
                $em->remove($availabilityDomain->availability);
            }
        }
    }
}
