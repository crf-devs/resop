<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method Organization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organization[]    findAll()
 * @method Organization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername(string $username): ?Organization
    {
        return $this->createQueryBuilder('o')
            ->where('o.name = :value')
            ->setParameter('value', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return Organization[][]
     */
    public function loadActiveOrganizations(): array
    {
        /** @var Organization[] $items */
        $items = $this
            ->createActiveOrganizationQueryBuilder()
            ->getQuery()
            ->getResult()
        ;

        $result = [];
        // Return all organizations separated by parent
        foreach ($items as $item) {
            if ($item->isParent()) {
                // Insert parent structure at the beginning
                $result[$item->name] = $result[$item->name] ?? [];
                array_unshift($result[$item->name], $item);
            } else {
                $result[$item->getParentName()][] = $item;
            }
        }

        return $result;
    }

    public function createActiveOrganizationQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');

        return $qb
            ->leftJoin('o.parent', 'p')
            ->where($qb->expr()->isNotNull('o.password'))
            ->addOrderBy('p.name', 'ASC')
            ->addOrderBy('o.name', 'ASC')
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

    /**
     * @return Organization[]
     */
    public function findByParent(Organization $organization): iterable
    {
        return $this
            ->findByParentQueryBuilder($organization)
            ->getQuery()
            ->getResult();
    }

    public function findByParentQueryBuilder(Organization $organization): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->where($qb->expr()->orX('o.id = :orga', 'o.parent = :orga'))
            ->setParameter('orga', $organization->parent ?: $organization)
            ->addOrderBy('o.name', 'ASC');

        return $qb;
    }

    public function findChildrenQueryBuilder(Organization $organization): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->where('o.parent = :organization OR o.id = :organization')
            ->setParameter('organization', $organization)
            ->addOrderBy('o.name', 'ASC');
    }

    public function findByIdOrParentIdQueryBuilder(int $organizationId, QueryBuilder $qb = null): QueryBuilder
    {
        $alias = 'o';
        if (null === $qb) {
            $qb = $this->createQueryBuilder('o');
        } else {
            $alias = $qb->getRootAliases()[0];
            $qb->orderBy($alias.'.name', 'desc');
        }

        $qb
            ->where($qb->expr()->orX($alias.'.id = :orgId', $alias.'.parent = :orgId'))
            ->setParameter('orgId', $organizationId);

        return $qb;
    }
}
