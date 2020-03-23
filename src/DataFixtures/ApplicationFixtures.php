<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
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

    /** @var CommissionableAsset[] */
    private array $assets = [];

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
        $this->loadAvailabilities($manager, $this->assets, CommissionableAssetAvailability::class);
        $this->loadUsers($manager);
        $this->loadAvailabilities($manager, $this->users, UserAvailability::class);

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
        $combinations = [
            ['VPSP', '2'],
            ['VPSP', '4'],
            ['VL', '6'],
        ];

        foreach ($this->organizations as $organization) {
            if ('DT75' == $organization->name) {
                $ulId = '99';
            } else {
                $ulId = substr(str_replace(' ', '', $organization->name), 1, 2);
            }

            foreach ($combinations as list($type, $suffix)) {
                $asset = new CommissionableAsset(
                    null,
                    $organization,
                    'VPSP',
                    '75'.$ulId.$suffix
                );

                $this->validateAndPersist($manager, $asset);
                $this->assets[] = $asset;
            }
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $startIdNumber = 990000;
        $firstNames = ['Philippe', 'Bastien', 'Hugo', 'Michel', 'Mathias', 'Florian', 'Fabien', 'Nassim', 'Mathieu', 'Francis', 'Thomas'];
        $lastNames = ['Skywalker', 'Merkel', 'Johnson', 'Trump', 'Macron', 'Musk', 'Jones', 'Diesel', 'Walker'];
        $occupations = ['Pharmacien', 'Pompier', 'Ambulancier.e', 'Logisticien', 'Infirmier.e'];
        $lettersRange = range('A', 'Z');
        $yearsRange = range(1950, 2005);
        $monthsRange = range(1, 12);
        $daysRange = range(1, 28);

        $x = 1;
        foreach ($this->organizations as $organization) {
            for ($i = 0; $i < rand(5, 15); ++$i) {
                $user = new User();
                $user->id = $i + 1;
                $user->firstName = $firstNames[array_rand($firstNames)];
                $user->lastName = $lastNames[array_rand($lastNames)];
                $user->organization = $this->organizations[array_rand($this->organizations)];
                $user->setIdentificationNumber(str_pad(''.++$startIdNumber.'', 10, '0', STR_PAD_LEFT).$lettersRange[array_rand($lettersRange)]);
                $user->setEmailAddress($user->firstName.'.'.$user->lastName.$x.'@some-domain.tld');
                $user->phoneNumber = '0102030405';
                $user->birthday = implode('-', [
                    $yearsRange[array_rand($yearsRange)],
                    str_pad($monthsRange[array_rand($monthsRange)].'', 2, '0', STR_PAD_LEFT),
                    str_pad($daysRange[array_rand($daysRange)].'', 2, '0', STR_PAD_LEFT),
                ]);
                $user->occupation = $occupations[array_rand($occupations)];
                $user->organizationOccupation = 'Secouriste';
                $user->skillSet = (array) array_rand($this->availableSkillSets, random_int(2, 4));
                $user->vulnerable = (bool) random_int(0, 1);
                $user->fullyEquipped = (bool) random_int(0, 1);

                $this->users[$user->getIdentificationNumber()] = $user;

                $this->validateAndPersist($manager, $user);
                ++$x;
            }
        }
    }

    private function loadAvailabilities(ObjectManager $manager, array $owners, string $availabilityClass): void
    {
        $thisWeek = (new \DateTimeImmutable('monday this week'));

        $dateIntervals = [];
        for ($d = 0; $d <= 10; ++$d) {
            for ($t = 0; $t < 22; $t = $t + 2) {
                $dateIntervals[] = 'P'.$d.'DT'.$t.'H';
            }
        }

        foreach ($owners as $owner) {
            $currentIntervals = $dateIntervals;
            for ($i = 0; $i < 40; ++$i) {
                $key = array_rand($currentIntervals);

                $startTime = $thisWeek->add(new \DateInterval($currentIntervals[$key]));

                $availability = new $availabilityClass(
                    null,
                    $owner,
                    $startTime,
                    $startTime->add(new \DateInterval('PT2H')),
                    AvailabilityInterface::STATUSES[array_rand(AvailabilityInterface::STATUSES)]
                );

                if (AvailabilityInterface::STATUS_BOOKED == $availability->status) {
                    $availability->planningAgent = $this->organizations[array_rand($this->organizations)];
                }

                $manager->persist($availability);

                unset($currentIntervals[$key]);
            }

            $manager->flush();
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
