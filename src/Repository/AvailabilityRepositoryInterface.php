<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilitableInterface;
use App\Entity\AvailabilityInterface;
use DateTimeInterface;

interface AvailabilityRepositoryInterface
{
    /**
     * @param AvailabilitableInterface[] $availabilitables
     */
    public function loadRawDataForEntity(array $availabilitables, DateTimeInterface $from, DateTimeInterface $to): array;

    /**
     * @param AvailabilitableInterface[] $owners
     *
     * @return AvailabilityInterface[]
     */
    public function findByOwnerAndDates(array $owners, DateTimeInterface $start, DateTimeInterface $end): array;
}
