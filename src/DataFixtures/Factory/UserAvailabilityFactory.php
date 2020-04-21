<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Entity\User;
use App\Entity\UserAvailability;
use App\Repository\AvailabilityTrait;

final class UserAvailabilityFactory
{
    use AvailabilityTrait;

    public static function create(string $slotInterval, User $user, string $startTime, string $status = UserAvailability::STATUS_AVAILABLE): UserAvailability
    {
        $startTime = self::round(new \DateTimeImmutable($startTime), $slotInterval);

        return new UserAvailability(
            null,
            $user,
            $startTime,
            $startTime->add(\DateInterval::createFromDateString($slotInterval)),
            $status
        );
    }
}
