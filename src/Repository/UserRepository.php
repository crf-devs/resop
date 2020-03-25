<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    use AvailabilityQueryTrait;

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
     * @return User[]|array
     */
    public function findByFilters(array $formData): array
    {
        $qb = $this->createQueryBuilder('u')
        ->join('u.organization', 'o');

        if (count($formData['organizations'] ?? []) > 0) {
            $qb->andWhere('u.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        if ($formData['onlyFullyEquiped'] ?? false) {
            $qb->andWhere('u.fullyEquipped = TRUE');
        }

        if (!($formData['displayVulnerables'] ?? false)) {
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
            $qb = $this->addAvailabilityBetween($qb, $formData['availableFrom'], $formData['availableTo'], UserAvailability::class, 'user');
        }

        $qb->orderBy('o.name');
        $qb->addOrderBy('u.firstName');
        $qb->addOrderBy('u.lastName');

        return $qb->getQuery()->getResult();
    }
}
