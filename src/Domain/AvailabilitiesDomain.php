<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\User;
use App\Entity\UserAvailability;
use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;

final class AvailabilitiesDomain
{
    private const SLOT_INTERVAL = 'PT2H';

    public array $availabilities = [];

    /**
     * @param UserAvailability[] $userAvailabilities
     *
     * @return AvailabilitiesDomain
     *
     * @throws \Exception
     */
    public static function generate(string $start, string $end, array $userAvailabilities = []): self
    {
        $period = new \DatePeriod(
            new \DateTimeImmutable($start),
            new \DateInterval(self::SLOT_INTERVAL),
            new \DateTimeImmutable($end)
        );

        $availabilities = [];
        foreach ($period as $date) {
            $userAvailability = null;
            foreach ($userAvailabilities as $k => $ua) {
                if ($ua->startTime->format('Y-m-d H:i') === $date->format('Y-m-d H:i')) {
                    $userAvailability = $ua;
                    unset($userAvailabilities[$k]);

                    break;
                }
            }

            $availabilities[] = new AvailabilityDomain($date, $userAvailability);
        }

        return new self($availabilities);
    }

    /**
     * @param AvailabilityDomain[] $availabilities
     */
    public function __construct(array $availabilities)
    {
        Assertion::allIsInstanceOf($availabilities, AvailabilityDomain::class);

        $this->availabilities = $availabilities;
    }

    public function compute(EntityManagerInterface $em, User $user): void
    {
        foreach ($this->availabilities as $availability) {
            if ($availability->tick && null === $availability->userAvailability) {
                $em->persist(new UserAvailability(
                    null,
                    $user,
                    $availability->date,
                    $availability->date->add(new \DateInterval(self::SLOT_INTERVAL)),
                    UserAvailability::STATUS_AVAILABLE
                ));
            } elseif (!$availability->tick && null !== $availability->userAvailability) {
                $em->remove($availability->userAvailability);
            }
        }
    }
}
