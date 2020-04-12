<?php

namespace App\Fixtures\Faker\Provider;

class UserProvider
{
    private static int $count = 990000;

    public static function identificationNumber(): string
    {
        return str_pad(''.++self::$count.'', 10, '0', \STR_PAD_LEFT).'A';
    }

    public static function skillSet(array $skills): array
    {
        return (array) array_rand($skills, random_int(1, 3));
    }
}
