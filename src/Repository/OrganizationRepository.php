<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->where($qb->expr()->isNotNull('o.password'))
            ->orderBy('o.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
