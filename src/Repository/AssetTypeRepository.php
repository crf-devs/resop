<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AssetType;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class AssetTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AssetType::class);
    }

    public function findByOrganization(Organization $organization): iterable
    {
        return $this->findByOrganizationQB($organization)->getQuery()->getResult();
    }

    public function findByOrganizationQB(?Organization $organization): QueryBuilder
    {
        return $this
            ->createQueryBuilder('at')
            ->where('at.organization = :organization')
            ->setParameter('organization', $organization);
    }

    public function findByOrganizationAndId(Organization $organization, int $id): ?AssetType
    {
        return $this
            ->createQueryBuilder('at')
            ->where('at.organization = :organization AND at.id = :id')
            ->setParameter('organization', $organization)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
