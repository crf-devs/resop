<?php

declare(strict_types=1);

namespace App\DataFixtures\Factory;

use App\Domain\DatePeriodCalculator;
use App\Entity\User;
use App\Entity\UserAvailability;

final class UserAvailabilityFactory
{
    public static function create(string $slotInterval, User $user, string $startTime, string $status = UserAvailability::STATUS_AVAILABLE): UserAvailability
    {
        $startDate = DatePeriodCalculator::roundToDailyInterval(new \DateTimeImmutable($startTime), \DateInterval::createFromDateString($slotInterval));

        return new UserAvailability(
            null,
            $user,
            $startDate,
            $startDate->add(\DateInterval::createFromDateString($slotInterval)),
            $status
        );
    }
}
