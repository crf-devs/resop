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
}
