<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Mission;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Mission|null find($id, $lockMode = null, $lockVersion = null)
 * @method Mission|null findOneBy(array $criteria, array $orderBy = null)
 * @method Mission[]    findAll()
 * @method Mission[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mission::class);
    }

    public function findByOrganization(Organization $organization): array
    {
        return $this->findByOrganizationQb($organization)->getQuery()->getResult();
    }

    public function findByOrganizationQb(Organization $organization): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');

        $qb
            ->join('m.organization', 'o')
            ->where($qb->expr()->orX('o.id = :orga', 'o.parent = :orga'))
            ->setParameter('orga', $organization->parent ?: $organization)
            ->addOrderBy('m.id', 'DESC');

        return $qb;
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->findByOrganizationQb($filters['organization']);
        $qb = $this->addFromToFilter($qb, $filters);

        if (\count($filters['missionTypes'] ?? []) > 0) {
            $qb->andWhere('m.type IN (:types)');
            $qb->setParameter('types', $filters['missionTypes']);
        }

        $qb->orderBy('m.startTime', 'DESC');
        $qb->setMaxResults(50); // TODO Paginate

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int[][] $resourcesIds
     *
     * @return Mission[]
     */
    public function findByPlanningFilters(array $filters, array $resourcesIds): array
    {
        if (2 !== \count($resourcesIds)) {
            throw new \LogicException('Bad resources ids');
        }

        [$users, $assets] = $resourcesIds;

        $qb = $this->createQueryBuilder('m');

        $qb
            ->select('DISTINCT m')
            ->leftJoin('m.users', 'mu', 'WITH', 'mu.id IN (:users)')
            ->leftJoin('m.assets', 'ma', 'WITH', 'ma.id IN (:assets)')
            ->where($qb->expr()->orX('mu.id IS NOT NULL', 'ma.id IS NOT NULL'))
            ->setParameter('users', $users)
            ->setParameter('assets', $assets);

        $qb = $this->addFromToFilter($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    private function addFromToFilter(QueryBuilder $qb, array $filters): QueryBuilder
    {
        if (empty($filters['from']) || empty($filters['to'])) {
            return $qb;
        }

        return $qb
            ->andWhere($qb->expr()->orX(
                'm.startTime IS NULL and m.endTime IS NULL',
                'm.startTime >= :start and m.endTime <= :end',
                'm.startTime <= :start and m.endTime >= :start',
                'm.startTime <= :end and m.endTime >= :end',
            ))
            ->setParameter('start', $filters['from'])
            ->setParameter('end', $filters['to']->modify('tomorrow'));
    }
}
