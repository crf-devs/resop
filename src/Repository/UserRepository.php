<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AvailabilityInterface;
use App\Entity\User;
use App\Entity\UserAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, AvailabilitableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function loadUserByUsername(string $identifier): ?User
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->where($qb->expr()->eq('u.identificationNumber', ':identificationNumber'))
            ->orWhere($qb->expr()->eq('u.emailAddress', ':emailAddress'))
            ->setParameter('identificationNumber', User::normalizeIdentificationNumber($identifier))
            ->setParameter('emailAddress', User::normalizeEmailAddress($identifier))
            ->getQuery()
            ->getOneOrNullResult()
        ;
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
        $qb = $this->createQueryBuilder('u');

        if (count($formData['organizations'] ?? []) > 0) {
            $qb->andWhere('u.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        if ($formData['onlyFullyEquiped'] ?? false) {
            $qb->andWhere('u.fullyEquipped = TRUE');
        }

        if ($formData['displayVulnerables'] ?? false) {
            $qb->andWhere('u.vulnerable = FALSE');
        }

        if (count($formData['userSkills'] ?? []) > 0) {
            $skillsQueries = [];
            foreach (array_values($formData['userSkills']) as $key => $skill) {
                $skillsQueries[] = sprintf('CONTAINS(u.skillSet, ARRAY(:skill%d)) = TRUE', $key);
                $qb->setParameter(sprintf('skill%d', $key), $skill);
            }

            $qb->andWhere($qb->expr()->orX(...$skillsQueries));
        }

        if (!empty($formData['availableFrom']) && !empty($formData['availableTo'])) {
            $subQuery = $this->getEntityManager()->createQueryBuilder()
                ->select('IDENTITY(a.user)')
                ->from(UserAvailability::class, 'a')
                ->andWhere('a.status = :status')
                ->andWhere(':searchStartTime <= a.startTime')
                ->andWhere('a.startTime < :searchEndTime')
                ->andWhere(':searchStartEndTime < a.endTime')
                ->andWhere('a.endTime <= :searchEndEndTime')
                ->groupBy('a.user');

            $qb->andWhere($qb->expr()->in(
                'u.id',
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
