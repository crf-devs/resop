<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findUserByIdentifier(string $identifier): ?User
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

    /**
     * @param string[]       $skills
     * @param Organization[] $organizations
     */
    public function findBySkillsAndEquippedAndVulnerableAndOrganizations(array $skills, bool $equipped, bool $vulnerable, array $organizations): iterable
    {
        $qb = $this->createQueryBuilder('u');

        $skillsQueries = [];
        foreach (array_values($skills) as $key => $skill) {
            $skillsQueries[] = sprintf('CONTAINS(u.skillSet, ARRAY(:skill%d)) = TRUE', $key);
            $qb->setParameter(sprintf('skill%d', $key), $skill);
        }

        $organizationIds = array_map(static function (Organization $organization) {
            return $organization->id;
        }, $organizations);

        $qb->where(
            $qb->expr()
                ->andX(
                    $qb->expr()->eq('u.fullyEquipped', $qb->expr()->literal($equipped)),
                    $qb->expr()->eq('u.vulnerable', $qb->expr()->literal($vulnerable)),
                    $qb->expr()->in('u.organization', $organizationIds),
                    $qb->expr()->orX(...$skillsQueries)
                )
            )
        ;

        return $qb->getQuery()->getResult();
    }
}
