<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\User;
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
        $user->occupation = 'Pharmacien';
        $user->organizationOccupation = 'Secouriste';
        $user->skillSet = ['CI Alpha', 'CI Réseau'];
        $user->vulnerable = true;
        $user->fullyEquipped = true;

        $this->users[$user->getIdentificationNumber()] = $user;

        $manager->persist($user);
    }
}
