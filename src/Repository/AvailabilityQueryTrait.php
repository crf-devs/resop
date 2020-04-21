<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilityInterface;
use Doctrine\ORM\QueryBuilder;

trait AvailabilityQueryTrait
{
    use AvailabilityTrait;

    private function isEven(int $number): bool
    {
        return 0 === $number % 2;
    }

    private function addAvailabilityBetween(
        QueryBuilder $qb,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        string $slotInterval,
        string $availabilityClass,
        string $groupByField,
        array $statuses = [AvailabilityInterface::STATUS_AVAILABLE],
        ?int $minimalAvailableTime = null
    ): QueryBuilder {
        // Round to the closest even the start and end date
        $start = self::round($start, $slotInterval);
        $end = self::round($end, $slotInterval, false);

        $interval = $start->diff($end);
        $numberOfInterval = (int) ($interval->h / $interval);
        $numberOfInterval += (int) (($interval->d * 24) / $interval);

        if (!empty($minimalAvailableTime)) { // can be null or 0
            $numberOfInterval = min($numberOfInterval, (int) ($minimalAvailableTime / $interval));
        }

        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select(sprintf('IDENTITY(abse.%s)', $groupByField))
            ->from($availabilityClass, 'abse')
            ->andWhere('abse.status IN (:statuses)')
            ->andWhere(':searchStartTime <= abse.startTime')
            ->andWhere('abse.startTime < :searchEndTime')
            ->andWhere(':searchStartEndTime < abse.endTime')
            ->andWhere('abse.endTime <= :searchEndEndTime')
            ->groupBy(sprintf('abse.%s', $groupByField))
            ->having('count(1) >= :numberOfInterval');

        $qb->andWhere($qb->expr()->in(
            sprintf('%s.id', $qb->getRootAliases()[0]),
            $subQuery->getDQL()
        ));

        $qb->setParameter('statuses', $statuses);
        $qb->setParameter('searchStartTime', $start);
        $qb->setParameter('searchEndTime', $end);
        $qb->setParameter('searchStartEndTime', $start);
        $qb->setParameter('searchEndEndTime', $end);
        $qb->setParameter('numberOfInterval', $numberOfInterval);

        return $qb;
    }
}
