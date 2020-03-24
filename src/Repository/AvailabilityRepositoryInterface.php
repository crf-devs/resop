<?php

declare(strict_types=1);

namespace App\Repository;

use DateTimeInterface;

interface AvailabilityRepositoryInterface
{
    public function loadRawDataForEntity(array $availabilitables, DateTimeInterface $from, DateTimeInterface $to): array;

    public function findByOwnerAndDates(array $owners, \DateTimeInterface $start, \DateTimeInterface $end): array;
}
