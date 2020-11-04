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
        $user->skillSet = ['foo', 'bar'];

        self::assertSame(1, $user->id);
        self::assertSame('Alain', $user->firstName);
        self::assertSame('Proviste', $user->lastName);
        self::assertSame('Alain Proviste', $user->getFullName());
        self::assertSame('Alain P.', $user->getShortFullName());
        self::assertSame('DL7509 / Alain Proviste', (string) $user);
        self::assertSame('1752114V', $user->getIdentificationNumber());
        self::assertSame('user+alias@some-domain.tld', $user->getEmailAddress());
        self::assertInstanceOf(PhoneNumber::class, $user->phoneNumber);
        self::assertEquals('102030405', $user->phoneNumber->getNationalNumber());
        self::assertEquals('33', $user->phoneNumber->getCountryCode());
        self::assertSame('1990-02-28', $user->birthday);
        self::assertSame(['foo', 'bar'], $user->skillSet);
    }
}
