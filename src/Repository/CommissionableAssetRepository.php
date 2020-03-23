<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CommissionableAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommissionableAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommissionableAsset[]    findAll()
 * @method CommissionableAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissionableAssetRepository extends ServiceEntityRepository implements AvailabilitableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommissionableAsset::class);
    }

    public function findByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids]);
    }

    /**
     * @param string[]       $assetTypes
     * @param Organization[] $organizations
     */
    public function findByTypesAndOrganizations(array $assetTypes, array $organizations): iterable
    {
        $organizationIds = array_map(static function (Organization $organization) {
            return $organization->id;
        }, $organizations);

        $qb = $this->createQueryBuilder('a');
        $qb->where(
            $qb->expr()->andX(
                $qb->expr()->in('a.type', $assetTypes),
                $qb->expr()->in('a.organization', $organizationIds)
            )
        );

        return $qb->getQuery()->getResult();
    }
}
