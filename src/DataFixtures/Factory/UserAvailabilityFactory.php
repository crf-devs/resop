<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Domain\AvailabilitiesDomain;
use App\Entity\User;
use App\Entity\UserAvailability;

final class UserAvailabilityFactory
{
    public static function create(User $user, string $startTime, string $status = UserAvailability::STATUS_AVAILABLE): UserAvailability
    {
        $startTime = new \DateTime($startTime);
        $startTime->setTime((int) $startTime->format('H'), 0, 0, 0);
        if (1 === (int) $startTime->format('H') % 2) {
            $startTime->setTime((int) $startTime->format('H') - 1, 0, 0, 0);
        }
        $startTime = \DateTimeImmutable::createFromMutable($startTime);

        return new UserAvailability(
            null,
            $user,
            $startTime,
            $startTime->add(new \DateInterval(AvailabilitiesDomain::SLOT_INTERVAL)),
            $status
        );
    }
}
