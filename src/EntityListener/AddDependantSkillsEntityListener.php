<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Domain\SkillSetDomain;
use App\Entity\User;

class AddDependantSkillsEntityListener
{
    private SkillSetDomain $skillSetDomain;

    public function __construct(SkillSetDomain $skillSetDomain)
    {
        $this->skillSetDomain = $skillSetDomain;
    }

    public function prePersist(User $user): void
    {
        $user->skillSet = $this->skillSetDomain->getDependantSkillsFromSkillSet($user->skillSet);
    }

    public function preUpdate(User $user): void
    {
        $this->prePersist($user);
    }
}
