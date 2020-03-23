<?php

declare(strict_types=1);

namespace App\Repository;

interface AvailabilitableRepositoryInterface
{
    public function findByIds(array $ids): array;
}
