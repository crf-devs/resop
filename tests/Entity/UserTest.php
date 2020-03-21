<?php

namespace App\Tests\Entity;

use App\Entity\Organization;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user = new User();
        $user->id = 1;
        $user->firstName = 'Alain';
        $user->lastName = 'Proviste';
        $user->organization = new Organization(1, 'DL7509');
        $user->setIdentificationNumber('00001752114V');
        $user->setEmailAddress('user+alias@some-domain.tld');
        $user->phoneNumber = '+33102030405';
        $user->occupation = 'Pharmacien';
        $user->organizationOccupation = 'Secouriste';
        $user->skillSet = ['CI Alpha', 'CI Réseau'];
        $user->vulnerable = true;
        $user->fullyEquipped = true;

        $this->assertSame(1, $user->id);
        $this->assertSame('Alain', $user->firstName);
        $this->assertSame('Proviste', $user->lastName);
        $this->assertSame('Alain Proviste', $user->getFullName());
        $this->assertSame('Alain P.', $user->getShortFullName());
        $this->assertSame('DL7509 / Alain Proviste', (string) $user);
        $this->assertSame('1752114V', $user->getIdentificationNumber());
        $this->assertSame('user+alias@some-domain.tld', $user->getEmailAddress());
        $this->assertSame('+33102030405', $user->phoneNumber);
        $this->assertSame('Pharmacien', $user->occupation);
        $this->assertSame('Secouriste', $user->organizationOccupation);
        $this->assertSame(['CI Alpha', 'CI Réseau'], $user->skillSet);
        $this->assertTrue($user->vulnerable);
        $this->assertTrue($user->fullyEquipped);
    }
}
