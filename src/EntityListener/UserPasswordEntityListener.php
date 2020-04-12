<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\UserPasswordInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserPasswordEntityListener
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function prePersist(UserPasswordInterface $user): void
    {
        if (!$plainPassword = $user->getPlainPassword()) {
            return;
        }

        $user->setPassword($this->encoder->encodePassword($user, $plainPassword));
        $user->eraseCredentials();
    }

    public function preUpdate(UserPasswordInterface $user): void
    {
        $this->prePersist($user);
    }
}
