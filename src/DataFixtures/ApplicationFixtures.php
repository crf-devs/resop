<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Entity\User;
use App\Entity\UserAvailability;
use App\Exception\ConstraintViolationListException;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ApplicationFixtures extends Fixture
{
    private const ORGANIZATIONS = [
        'UL 01-02',
        'UL 03-10',
        'UL 04',
        'UL 05',
        'UL 06',
        'UL 07',
        'UL 08',
        'UL 09',
        'UL 11',
        'UL 12',
        'UL 13',
        'UL 14',
        'UL 15',
        'UL 16',
        'UL 17',
        'UL 18',
        'UL 19',
        'UL 20',
    ];

    private ValidatorInterface $validator;

    private EncoderFactoryInterface $encoders;

    /** @var Organization[] */
    private array $organizations = [];

    /** @var User[] */
    private array $users = [];

    private array $availableSkillSets;

    public function __construct(
        EncoderFactoryInterface $encoders,
        ValidatorInterface $validator,
        array $availableSkillSets = []
    ) {
        $this->encoders = $encoders;
        $this->validator = $validator;
        $this->availableSkillSets = $availableSkillSets;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadOrganizations($manager);
        $this->loadCommissionableAssets($manager);
        $this->loadUsers($manager);
        $this->loadUserAvailabilities($manager);

        $manager->flush();
    }

    private function loadOrganizations(ObjectManager $manager): void
    {
        // Yield same password for all organizations.
        // Password generation can be expensive and time consuming.
        $encoder = $this->encoders->getEncoder(Organization::class);
        $password = $encoder->encodePassword('covid19', null);

        $this->addOrganization($this->makeOrganization('INACTIVE_ORG'));
        $this->addOrganization($this->makeOrganization('DT75', $password));

        foreach (self::ORGANIZATIONS as $name) {
            $this->addOrganization($this->makeOrganization($name, $password, $this->organizations['DT75']));
        }

        // Persist all organizations
        foreach ($this->organizations as $organization) {
            $this->validateAndPersist($manager, $organization);
        }
    }

    private function loadCommissionableAssets(ObjectManager $manager): void
    {
        foreach ($this->organizations as $organization) {

            $asset = new CommissionableAsset(
                null,
                $organization,
                'VPSP',
                '750'.random_int(10, 20)
            );

            $this->validateAndPersist($manager, $asset);

            $asset = new CommissionableAsset(
                null,
                $organization,
                'VPSP',
                '750'.random_int(20, 30)
            );

            $this->validateAndPersist($manager, $asset);

            $asset = new CommissionableAsset(
                null,
                $organization,
                'VL',
                '750'.random_int(30, 40)
            );

            $this->validateAndPersist($manager, $asset);
        }

    }

    private function loadUsers(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; $i ++) {
            $user = new User();
            $user->id = $i + 1;
            $user->firstName = 'Alain';
            $user->lastName = 'Proviste';
            $user->organization = $this->organizations[array_rand($this->organizations)];
            $user->setIdentificationNumber('0000999999'.$i.'V');
            $user->setEmailAddress('user+alias'.$i.'@some-domain.tld');
            $user->phoneNumber = '0102030405';
            $user->birthday = '1990-02-28';
            $user->occupation = 'Pharmacien';
            $user->organizationOccupation = 'Secouriste';
            $user->skillSet = array_rand($this->availableSkillSets, random_int(2, 4));
            $user->vulnerable = (bool) random_int(0, 1);
            $user->fullyEquipped = (bool) random_int(0, 1);

            $this->users[$user->getIdentificationNumber()] = $user;

            $this->validateAndPersist($manager, $user);
        }
    }

    private function loadUserAvailabilities(ObjectManager $manager): void
    {
        $thisWeek = (new \DateTimeImmutable('monday this week'));

        $availabilities = [
            '9999990V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999991V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999992V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999993V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999995V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999996V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999997V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999998V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
            '9999999V' => [
                'PT0H',
                'P2DT10H',
                'P2DT12H',
                'P7DT22H',
                'P8DT16H',
                'P9DT20H',
                'P9DT22H',
                'P10DT8H',
                'P10DT10H',
            ],
        ];

        foreach ($availabilities as $user => $periods) {
            foreach ($periods as $period) {
                $startTime = $thisWeek->add(new \DateInterval($period));

                $manager->persist(new UserAvailability(
                    null,
                    $this->users[$user],
                    $startTime,
                    $startTime->add(new \DateInterval('PT2H')),
                    UserAvailability::STATUS_AVAILABLE
                ));
            }
        }
    }

    private function makeOrganization(string $name, string $password = null, Organization $parent = null): Organization
    {
        $organization = new Organization(null, $name, $parent);

        if ($password) {
            $organization->password = $password;
        }

        return $organization;
    }

    private function addOrganization(Organization $organization): void
    {
        $this->organizations[$organization->name] = $organization;
    }

    private function validateAndPersist(ObjectManager $manager, object $object): void
    {
        $violations = $this->validator->validate($object);

        if (\count($violations)) {
            throw new ConstraintViolationListException($violations);
        }

        $manager->persist($object);
    }
}
