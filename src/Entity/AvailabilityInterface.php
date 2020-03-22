<?php

declare(strict_types=1);

namespace App\Entity;

interface AvailabilityInterface
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_BOOKED = 'booked';
    public const STATUS_LOCKED = 'locked';

    public const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_BOOKED,
        self::STATUS_LOCKED,
    ];

    public function book(User $planningAgent, \DateTimeImmutable $bookedAt = null): void;

    public function declareAvailable(\DateTimeImmutable $updatedAt = null): void;
}
