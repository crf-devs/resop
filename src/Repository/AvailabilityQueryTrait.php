<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilityInterface;
use Doctrine\ORM\QueryBuilder;

trait AvailabilityQueryTrait
{
    private function isEven(int $number): bool
    {
        return 0 == $number % 2;
    }

    private function addAvailabilityBetween(QueryBuilder $qb, \DateTimeImmutable $start, \DateTimeImmutable $end, string $availabilityClass, string $groupByField): QueryBuilder
    {
        $start = $start->setTime((int) $start->format('h'), 0);
        $end = $end->setTime((int) $end->format('h'), 0);

        // Round to the closest even the start and end date
        $hour = (int) $start->format('h');
        if (!$this->isEven($hour)) {
            $start = $start->sub(new \DateInterval('PT1H'));
        }
        $hour = (int) $end->format('h');
        if (!$this->isEven($hour)) {
            $end = $end->add(new \DateInterval('PT1H'));
        }

        $interval = $start->diff($end);
        $numberOfInterval = (int) ($interval->h / 2);
        $numberOfInterval += (int) (($interval->d * 24) / 2);

        $subQuery = $this->getEntityManager()->createQueryBuilder()
            ->select(sprintf('IDENTITY(abse.%s)', $groupByField))
            ->from($availabilityClass, 'abse')
            ->andWhere('abse.status = :status')
            ->andWhere(':searchStartTime <= abse.startTime')
            ->andWhere('abse.startTime < :searchEndTime')
            ->andWhere(':searchStartEndTime < abse.endTime')
            ->andWhere('abse.endTime <= :searchEndEndTime')
            ->groupBy(sprintf('abse.%s', $groupByField))
            ->having('count(1) = :numberOfInterval');

        $qb->andWhere($qb->expr()->in(
            sprintf('%s.id', $qb->getRootAliases()[0]),
            $subQuery->getDQL()
        ));

        $qb->setParameter('status', AvailabilityInterface::STATUS_AVAILABLE);
        $qb->setParameter('searchStartTime', $start);
        $qb->setParameter('searchEndTime', $end);
        $qb->setParameter('searchStartEndTime', $start);
        $qb->setParameter('searchEndEndTime', $end);
        $qb->setParameter('numberOfInterval', $numberOfInterval);

        return $qb;
    }
}
