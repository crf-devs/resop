<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @return ArrayCollection
     */
    public function findByFilters(array $formData)
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($formData['assetTypes'])) {
            $qb->andWhere('a.type IN (:types)')->setParameter('types', $formData['assetTypes']);
        }

        if (!empty($formData['organizations'])) {
            $qb->andWhere('a.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        if (!empty($formData['availableFrom']) && !empty($formData['availableTo'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder()
                ->select('IDENTITY(at.asset)')
                ->from(CommissionableAssetAvailability::class, 'at')
                ->andWhere('at.status = :status')
                ->andWhere(':searchStartTime <= at.startTime')
                ->andWhere('at.startTime < :searchEndTime')
                ->andWhere(':searchStartEndTime < at.endTime')
                ->andWhere('at.endTime <= :searchEndEndTime')
                ->groupBy('at.asset');

            $qb->andWhere($qb->expr()->in(
                'a.id',
                $subQuery->getDQL()
            ));

            $qb->setParameter('status', AvailabilityInterface::STATUS_AVAILABLE);
            $qb->setParameter('searchStartTime', $formData['availableFrom']);
            $qb->setParameter('searchEndTime', $formData['availableTo']);
            $qb->setParameter('searchStartEndTime', $formData['availableFrom']);
            $qb->setParameter('searchEndEndTime', $formData['availableTo']);
        }

        return $qb->getQuery()->getResult();
    }
}
