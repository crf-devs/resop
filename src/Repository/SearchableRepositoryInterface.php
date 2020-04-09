<?php

declare(strict_types=1);

namespace App\Repository;

interface SearchableRepositoryInterface
{
    public function search(string $query): array;
}
