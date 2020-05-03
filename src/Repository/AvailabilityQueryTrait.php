<?php

declare(strict_types=1);

namespace App\Repository;

use App\Domain\DatePeriodCalculator;
use App\Entity\AvailabilityInterface;
use Doctrine\ORM\QueryBuilder;

trait AvailabilityQueryTrait
{
    private function addAvailabilityCondition(QueryBuilder $qb, array $formData, string $class, string $groupByField): QueryBuilder
    {
        if (empty($formData['minimumAvailableHours']) &&
            (empty($formData['availableFrom']) || empty($formData['availableTo']))
        ) {
            return $qb;
        }

        $availableStatuses = [AvailabilityInterface::STATUS_AVAILABLE];
        if ($formData['displayAvailableWithBooked'] ?? false) {
            $availableStatuses[] = AvailabilityInterface::STATUS_BOOKED;
        }

        return $this->addAvailabilityBetween(
            $qb,
            $formData['availableFrom'] ?? null ?: $formData['from'],
            $formData['availableTo'] ?? null ?: $formData['to'],
            $this->slotInterval,
            $class,
            $groupByField,
            $availableStatuses,
            $formData['minimumAvailableHours'] ?? null,
        );
    }

    private function addAvailabilityBetween(
        QueryBuilder $qb,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        string $slotIntervalStr,
        string $availabilityClass,
        string $groupByField,
        array $statuses = [AvailabilityInterface::STATUS_AVAILABLE],
        ?int $minimalAvailableHours = null
    ): QueryBuilder {
        $slotInterval = \DateInterval::createFromDateString($slotIntervalStr);

        // Round to the closest even the start and end date
        $start = DatePeriodCalculator::roundToDailyInterval($start, $slotInterval);
        $end = DatePeriodCalculator::roundToDailyInterval($end, $slotInterval, false);

        $interval = DatePeriodCalculator::intervalToSeconds($start->diff($end));
        $numberOfInterval = (int) ($interval / DatePeriodCalculator::intervalToSeconds($slotInterval));

        if (!empty($minimalAvailableHours)) { // can be null or 0
            $minimalAvailableIntervals = ceil($minimalAvailableHours * 3600 / DatePeriodCalculator::intervalToSeconds($slotInterval));
            $numberOfInterval = (int) min($numberOfInterval, $minimalAvailableIntervals);
        }

        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select(sprintf('IDENTITY(abse.%s)', $groupByField))
            ->from($availabilityClass, 'abse')
            ->andWhere('abse.status IN (:statuses)')
            ->andWhere('abse.startTime >= :searchStartTime')
            ->andWhere('abse.startTime < :searchEndTime')
            ->andWhere('abse.endTime > :searchStartEndTime')
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
