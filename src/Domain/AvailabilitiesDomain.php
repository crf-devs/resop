<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\User;
use App\Entity\UserAvailability;
use Doctrine\Persistence\ObjectManager;

class AvailabilitiesDomain
{
    public array $availabilities;

    public function __construct(\DateTimeImmutable $start, \DateTimeImmutable $end, array $userAvailabilities)
    {
        $this->availabilities = [];

        $period = new \DatePeriod($start, new \DateInterval('PT2H'), $end);

        foreach ($period as $date) {
            $userAvailability = null;
            foreach ($userAvailabilities as $k => $ua) {
                if ($ua->startTime->format('Y-m-d H:i') === $date->format('Y-m-d H:i')) {
                    $userAvailability = $ua;
                    unset($userAvailabilities[$k]);

                    break;
                }
            }

            $this->availabilities[] = new AvailabilityDomain($date, $userAvailability);
        }
    }

    public function compute(ObjectManager $om, User $user): void
    {
        foreach ($this->availabilities as $availability) {
            if ($availability->tick && null === $availability->userAvailability) {
                $om->persist(new UserAvailability(
                    null,
                    $user,
                    $availability->date,
                    $availability->date->add(new \DateInterval('PT2H')),
                    UserAvailability::STATUS_AVAILABLE
                ));
            } elseif (!$availability->tick && null !== $availability->userAvailability) {
                $om->remove($availability->userAvailability);
            }
        }

        $om->flush();
    }
}

class AvailabilityDomain
{
    public bool $tick;

    public ?UserAvailability $userAvailability;

    public \DateTimeImmutable $date;

    public function __construct(\DateTimeImmutable $date, ?UserAvailability $ua)
    {
        $this->date = $date;
        $this->userAvailability = $ua;
        $this->tick = null !== $ua;
    }

    public function isEditable(): bool
    {
        return null === $this->userAvailability || UserAvailability::STATUS_AVAILABLE === $this->userAvailability->status;
    }
}
