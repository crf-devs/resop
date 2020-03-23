<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CommissionableAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommissionableAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommissionableAsset[]    findAll()
 * @method CommissionableAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissionableAssetAvailabilityRepository extends ServiceEntityRepository implements AvailabilityRepositoryInterface
{
    use AvailabilityRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommissionableAssetAvailability::class);
    }
}
