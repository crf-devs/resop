<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Organization;
use App\Entity\User;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testCreateUser(): void
    {
        $user = new User();
        $user->id = 1;
        $user->firstName = 'Alain';
        $user->lastName = 'Proviste';
        $user->organization = new Organization();
        $user->organization->id = 1;
        $user->organization->name = 'DL7509';
        $user->setIdentificationNumber('00001752114V');
        $user->setEmailAddress('user+ALIAS@some-domain.tld');
        $user->phoneNumber = PhoneNumberUtil::getInstance()->parse('+33102030405', 'FR');
        $user->birthday = '1990-02-28';
        $user->occupation = 'Pharmacien';
        $user->skillSet = ['foo', 'bar'];
        $user->vulnerable = true;

        $this->assertSame(1, $user->id);
        $this->assertSame('Alain', $user->firstName);
        $this->assertSame('Proviste', $user->lastName);
        $this->assertSame('Alain Proviste', $user->getFullName());
        $this->assertSame('Alain P.', $user->getShortFullName());
        $this->assertSame('DL7509 / Alain Proviste', (string) $user);
        $this->assertSame('1752114V', $user->getIdentificationNumber());
        $this->assertSame('user+alias@some-domain.tld', $user->getEmailAddress());
        $this->assertInstanceOf(PhoneNumber::class, $user->phoneNumber);
        $this->assertEquals('102030405', $user->phoneNumber->getNationalNumber());
        $this->assertEquals('33', $user->phoneNumber->getCountryCode());
        $this->assertSame('1990-02-28', $user->birthday);
        $this->assertSame('Pharmacien', $user->occupation);
        $this->assertSame(['foo', 'bar'], $user->skillSet);
        $this->assertTrue($user->vulnerable);
    }
}
