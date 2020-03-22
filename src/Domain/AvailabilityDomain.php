<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\UserAvailability;

final class AvailabilityDomain
{
    public bool $tick = false;

    public ?UserAvailability $userAvailability;

    public \DateTimeImmutable $date;

    public function __construct(\DateTimeImmutable $date, ?UserAvailability $userAvailability)
    {
        $this->date = $date;
        $this->userAvailability = $userAvailability;
        $this->tick = null !== $userAvailability;
    }

    public function isEditable(): bool
    {
        return null === $this->userAvailability || UserAvailability::STATUS_AVAILABLE === $this->userAvailability->status;
    }
}
