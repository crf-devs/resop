<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;

interface SearchableRepositoryInterface
{
    public function search(Organization $organization, string $query): array;
}
