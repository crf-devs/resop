<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilityInterface;

final class AvailabilityDomain
{
    public bool $tick = false;

    public ?AvailabilityInterface $availability;

    public \DateTimeImmutable $date;

    public function __construct(\DateTimeImmutable $date, ?AvailabilityInterface $availability)
    {
        $this->date = $date;
        $this->availability = $availability;
        $this->tick = null !== $availability && AvailabilityInterface::STATUS_LOCKED !== $availability->getStatus();
    }

    public function isEditable(): bool
    {
        return null === $this->availability || AvailabilityInterface::STATUS_AVAILABLE === $this->availability->getStatus();
    }
}
