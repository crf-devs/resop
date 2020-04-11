<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;

/**
 * @method CommissionableAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommissionableAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommissionableAsset[]    findAll()
 * @method CommissionableAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissionableAssetRepository extends ServiceEntityRepository implements AvailabilitableRepositoryInterface, SearchableRepositoryInterface
{
    use AvailabilityQueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommissionableAsset::class);
    }

    /**
     * @return CommissionableAsset[]
     */
    public function search(Organization $organization, string $query): array
    {
        $words = explode(' ', $query);
        $qb = $this->createQueryBuilder('ca');

        if ($organization->isParent()) {
            $qb
                ->andWhere($qb->expr()->in('ca.organization', 'SELECT o.id FROM App:Organization o WHERE o.id = :orgId OR o.parent = :orgId'));
        } else {
            $qb->andWhere('ca.organization = :orgId');
        }
        $qb->setParameter('orgId', $organization);

        foreach ($words as $i => $word) {
            $qb
                ->andWhere("LOWER(CONCAT(ca.type, ca.name)) LIKE LOWER(?$i) OR LOWER(ca.contact) LIKE LOWER(?$i)")
                ->setParameter($i, "%$word%");
        }

        return $qb->setMaxResults(10)->getQuery()->getResult();
    }

    public function findByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids]);
    }

    public function findByOrganization(Organization $organization): iterable
    {
        return $this
            ->createQueryBuilder('ca')
            ->where('ca.organization = :organization')
            ->setParameter('organization', $organization)
            ->orderBy('ca.name', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return CommissionableAsset[]|int[]
     */
    public function findByFilters(array $formData, bool $onlyIds = false): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($onlyIds) {
            $qb->select('a.id');
        }

        if (\count($formData['assetTypes'] ?? []) > 0) {
            $qb->andWhere('a.type IN (:types)')->setParameter('types', $formData['assetTypes']);
        }

        if (\count($formData['organizations'] ?? []) > 0) {
            $qb->andWhere('a.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        if (!empty($formData['availableFrom']) && !empty($formData['availableTo'])) {
            $qb = $this->addAvailabilityBetween($qb, $formData['availableFrom'], $formData['availableTo'], CommissionableAssetAvailability::class, 'asset');
        }

        $qb->orderBy('a.name');

        return $qb
            ->getQuery()
            ->getResult($onlyIds ? AbstractQuery::HYDRATE_SCALAR : AbstractQuery::HYDRATE_OBJECT);
    }
}
