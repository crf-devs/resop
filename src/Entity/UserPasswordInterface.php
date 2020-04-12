<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserPasswordInterface extends UserInterface
{
    public function getPlainPassword(): ?string;

    public function setPassword(string $password);
}
