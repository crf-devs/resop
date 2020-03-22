<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\User;
use App\Entity\UserAvailability;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

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

    /** @var Organization[] */
    private $organizations = [];

    /** @var User[] */
    private $users = [];

    public function load(ObjectManager $manager): void
    {
        $this->loadOrganizations($manager);
        $this->loadUsers($manager);
        $this->loadUserAvailabilities($manager);

        $manager->flush();
    }

    private function loadOrganizations(ObjectManager $manager): void
    {
        $this->organizations['DT75'] = $main = new Organization(null, 'DT75');

        $manager->persist($main);

        foreach (self::ORGANIZATIONS as $name) {
            $this->organizations[$name] = $organization = new Organization(null, $name, $main);
            $manager->persist($organization);
        }
    }

    private function loadUsers(ObjectManager $manager): void
    {
        $user = new User();
        $user->id = 1;
        $user->firstName = 'Alain';
        $user->lastName = 'Proviste';
        $user->organization = $this->organizations['UL 09'];
        $user->setIdentificationNumber('00009999999V');
        $user->setEmailAddress('user+alias@some-domain.tld');
        $user->phoneNumber = '+33102030405';
        $user->birthday = '1990-02-28';
        $user->occupation = 'Pharmacien';
        $user->organizationOccupation = 'Secouriste';
        $user->skillSet = ['CI Alpha', 'CI Réseau'];
        $user->vulnerable = true;
        $user->fullyEquipped = true;

        $this->users[$user->getIdentificationNumber()] = $user;

        $manager->persist($user);
    }

    private function loadUserAvailabilities(ObjectManager $manager): void
    {
        $thisWeek = (new \DateTimeImmutable('previous monday'))->setTime(0, 0, 0);

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('PT0H')),
            $thisWeek->add(new \DateInterval('PT2H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P2DT10H')),
            $thisWeek->add(new \DateInterval('P2DT12H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P2DT12H')),
            $thisWeek->add(new \DateInterval('P2DT14H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P7DT22H')),
            $thisWeek->add(new \DateInterval('P7DT24H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P8DT16H')),
            $thisWeek->add(new \DateInterval('P8DT18H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P9DT20H')),
            $thisWeek->add(new \DateInterval('P9DT22H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P9DT22H')),
            $thisWeek->add(new \DateInterval('P9DT24H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P10DT8H')),
            $thisWeek->add(new \DateInterval('P10DT10H')),
            UserAvailability::STATUS_AVAILABLE
        ));

        $manager->persist(new UserAvailability(
            null,
            $this->users['9999999V'],
            $thisWeek->add(new \DateInterval('P10DT10H')),
            $thisWeek->add(new \DateInterval('P10DT12H')),
            UserAvailability::STATUS_AVAILABLE
        ));
    }
}
