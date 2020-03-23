<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilityInterface;
use DateTimeInterface;

trait AvailabilityRepositoryTrait
{
    public function findOneByInterval(DateTimeInterface $from, DateTimeInterface $to): ?AvailabilityInterface
    {
        $qb = $this->createQueryBuilder('a');

        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->lte('a.startTime', $qb->expr()->literal($to->format('Y-m-d H:i:s'))),
                $qb->expr()->gte('a.endTime', $qb->expr()->literal($from->format('Y-m-d H:i:s')))
            )
        );

        return $qb->getQuery()->getResult()[0] ?? null;
    }
}
