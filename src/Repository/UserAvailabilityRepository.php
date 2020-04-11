<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserAvailability;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @method UserAvailability|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserAvailability|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserAvailability[]    findAll()
 * @method UserAvailability[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserAvailabilityRepository extends ServiceEntityRepository implements AvailabilityRepositoryInterface
{
    use AvailabilityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAvailability::class);
    }

    public function loadRawDataForEntity(array $availabilitables, DateTimeInterface $from, DateTimeInterface $to): array
    {
        $qb = $this->createQueryBuilder('ua');
        $qb->where($qb->expr()->in('ua.user', ':users'))
            ->setParameter('users', $availabilitables);

        return $this->getRawSlots($qb, $from, $to);
    }

    public function findBetweenDates(User $user, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('ua')
            ->where('ua.user = :user')
            ->andWhere('ua.startTime >= :start')
            ->andWhere('ua.endTime <= :end')
            ->setParameters([
                'user' => $user,
                'start' => $start,
                'end' => $end,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findByOwnerAndDates(array $owners, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('ua')
            ->where('ua.user IN (:owners)')
            ->andWhere('ua.startTime >= :start')
            ->andWhere('ua.endTime <= :end')
            ->setParameters([
                'owners' => $owners,
                'start' => $start,
                'end' => $end,
            ])
            ->getQuery()
            ->getResult();
    }

    public function findLastUpdatedForEntities(array $availabilitables): ?array
    {
        $qb = $this->createQueryBuilder('ua');
        $qb
            ->where($qb->expr()->in('ua.user', ':owners'))
            ->setParameter('owners', $availabilitables);

        return $this->findLastUpdatesForEntities($qb);
    }

    public function deleteByOwner(User $user): int
    {
        return $this
            ->createQueryBuilder('ua')
            ->delete()
            ->where('ua.user = :owner')
            ->setParameter('owner', $user)
            ->getQuery()
            ->execute();
    }

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
