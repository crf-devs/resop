<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\MissionType;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method MissionType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MissionType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MissionType[]    findAll()
 * @method MissionType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MissionTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MissionType::class);
    }

    public function findByOrganization(Organization $organization): array
    {
        return $this->findByOrganizationQb($organization)->getQuery()->getResult();
    }

    public function findByOrganizationQb(Organization $organization): QueryBuilder
    {
        $qb = $this->createQueryBuilder('mt');

        $qb
            ->join('mt.organization', 'o')
            ->where($qb->expr()->orX('o.id = :orga', 'o.parent = :orga'))
            ->setParameter('orga', $organization->parent ?: $organization)
            ->addOrderBy('mt.name', 'ASC');

        return $qb;
    }
}
