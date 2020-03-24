<?php

declare(strict_types=1);

namespace App\Repository;

use DateTimeInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

trait AvailabilityRepositoryTrait
{
    private function getRawSlots(QueryBuilder $qb, DateTimeInterface $from, DateTimeInterface $to): array
    {
        return $qb->andWhere('(ua.startTime >= :start and ua.endTime <= :end) or (ua.startTime <= :start and ua.endTime >= :start) or (ua.startTime <= :end and ua.endTime >= :end)')
            ->setParameter('start', $from)
            ->setParameter('end', $to)
            ->getQuery()
            ->setHint(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->getArrayResult();
    }
}
