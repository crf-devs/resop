<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Organization;
use App\Entity\User;
use App\Entity\UserAvailability;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface, AvailabilitableRepositoryInterface, SearchableRepositoryInterface
{
    use AvailabilityQueryTrait;

    private string $slotInterval;

    public function __construct(ManagerRegistry $registry, string $slotInterval)
    {
        parent::__construct($registry, User::class);

        $this->slotInterval = $slotInterval;
    }

    public function findOneByIdAndOrganization(int $id, Organization $organization): ?User
    {
        $organization = $organization->getParentOrganization();
        $organizations = [
            $organization->getId(),
            ...$organization->getChildren()->map(fn (Organization $child) => $child->getId())->getValues(),
        ];

        $qb = $this->createQueryBuilder('u');
        $qb->where('u.id = :id')->setParameter('id', $id);
        $qb->andWhere($qb->expr()->in('u.organization', ':organizations'))
            ->setParameter('organizations', $organizations);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return User[]
     */
    public function search(Organization $organization, string $query): array
    {
        $words = explode(' ', $query);
        $qb = $this->createQueryBuilder('u');

        $qb->andWhere($qb->expr()->in('u.organization', 'SELECT o.id FROM App:Organization o WHERE o.id = :orgId OR o.parent = :orgId'));
        $qb->setParameter('orgId', $organization);

        foreach ($words as $i => $word) {
            $qb
                ->andWhere("LOWER(u.firstName) LIKE LOWER(?$i) OR LOWER(u.lastName) LIKE LOWER(?$i) OR LOWER(u.emailAddress) LIKE LOWER(?$i) OR LOWER(u.identificationNumber) LIKE LOWER(?$i)")
                ->setParameter($i, "%$word%");
        }

        return $qb->setMaxResults(10)->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername(string $identifier): ?User
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->where($qb->expr()->eq('u.identificationNumber', ':identificationNumber'))
            ->orWhere($qb->expr()->eq('u.emailAddress', ':emailAddress'))
            ->setParameter('identificationNumber', User::normalizeIdentificationNumber($identifier))
            ->setParameter('emailAddress', User::normalizeEmailAddress($identifier))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIds(array $ids): array
    {
        return $this->findBy(['id' => $ids]);
    }

    /**
     * @return User[]|int[]
     */
    public function findByFilters(array $formData, bool $onlyIds = false): array
    {
        $qb = $this
            ->createQueryBuilder('u')
            ->join('u.organization', 'o');

        if ($onlyIds) {
            $qb->select('u.id');
        }

        if (\count($formData['organizations'] ?? []) > 0) {
            $qb->andWhere('u.organization IN (:organisations)')->setParameter('organisations', $formData['organizations']);
        }

        foreach ($formData['userPropertyFilters'] ?? [] as $field => $value) {
            if (null === $value || '' === $value) {
                continue;
            }

            // $field is a user input, we must proceed to a sanity check
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_]*$/', $field)) {
                throw new \InvalidArgumentException("Possible SQL injection attempt.");
            }

            $qb->andWhere(
                sprintf(
                    'JSON_GET_FIELD_AS_TEXT(u.properties, %s) = :filter_%s',
                    // $field needs to be provided with quotes
                    $this->getEntityManager()->getConnection()->quote($field),
                    $field
                ))
                // the boolean values must be cast to string value
                ->setParameter("filter_$field", (bool) $value ? 'true' : 'false');
        }

        if (\count($formData['userSkills'] ?? []) > 0) {
            $skillsQueries = [];
            foreach (array_values($formData['userSkills']) as $key => $skill) {
                $skillsQueries[] = sprintf('CONTAINS(u.skillSet, ARRAY(:skill%d)) = TRUE', $key);
                $qb->setParameter(sprintf('skill%d', $key), $skill);
            }

            if ($formData['usersWithAllSkills'] ?? false) {
                $qb->andWhere($qb->expr()->andX(...$skillsQueries));
            } else {
                $qb->andWhere($qb->expr()->orX(...$skillsQueries));
            }
        }

        $qb = $this->addAvailabilityCondition($qb, $formData, UserAvailability::class, 'user');

        $qb->orderBy('o.name');
        $qb->addOrderBy('u.firstName');
        $qb->addOrderBy('u.lastName');

        return $qb
            ->getQuery()
            ->getResult($onlyIds ? AbstractQuery::HYDRATE_SCALAR : AbstractQuery::HYDRATE_OBJECT);
    }

    /**
     * @return User[]
     */
    public function findByOrganization(Organization $organization): array
    {
        return $this->findByOrganizationAndChildrenQb($organization)
            ->getQuery()
            ->getResult();
    }

    public function findByOrganizationAndChildrenQb(Organization $organization, bool $searchInChildren = false): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->join('u.organization', 'o');

        if ($searchInChildren) {
            $qb->andWhere('o = :organization OR o.parent = :organization');
        } else {
            $qb->andWhere('o = :organization');
        }

        $qb->setParameter('organization', $organization)
            ->addOrderBy('o.name', 'ASC')
            ->addOrderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC');

        return $qb;
    }
}
