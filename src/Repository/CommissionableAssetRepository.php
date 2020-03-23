<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommissionableAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method CommissionableAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommissionableAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommissionableAsset[]    findAll()
 * @method CommissionableAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissionableAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommissionableAsset::class);
    }

    /**
     * @return ArrayCollection
     */
    public function findByFilters(array $formData)
    {
        $qb = $this->createQueryBuilder('a');

        if (0 < count($formData['assetTypes'])) {
            $qb->andWhere('a.type IN (:types)')->setParameter('types', $formData['assetTypes']);
        }

        if (0 < $formData['organizations']->count()) {
            $qb->andWhere('a.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        return $qb->getQuery()->getResult();
    }
}
