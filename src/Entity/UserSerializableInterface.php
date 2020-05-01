<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserSerializableInterface extends UserInterface
{
    public function userSerialize(): array;
}
