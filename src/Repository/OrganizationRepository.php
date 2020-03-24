<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Organization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organization[]    findAll()
 * @method Organization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function loadUserByUsername(string $name): ?Organization
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @return Organization[]
     */
    public function loadActiveOrganizations(): array
    {
        return $this
            ->createActiveOrganizationQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }

    public function createActiveOrganizationQueryBuilder(string $alias = 'o'): QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias);

        return $qb
            ->where($qb->expr()->isNotNull($alias.'.password'))
            ->orderBy($alias.'.name', 'ASC')
        ;
    }

    public function findAllWithParent(): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('p')
            ->leftJoin('o.parent', 'p')
            ->getQuery()
            ->getResult();
    }
}
