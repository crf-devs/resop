<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    public const CAN_EDIT = 'CAN_EDIT_USER';

    protected function supports($attribute, $subject): bool
    {
        return self::CAN_EDIT === $attribute && $subject instanceof User;
    }

    /**
     * @param string $attribute
     * @param User   $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedOrganization = $token->getUser();

        if (!$loggedOrganization instanceof Organization || null === $subject->organization) {
            return false;
        }

        return $subject->organization === $loggedOrganization || $subject->getNotNullOrganization()->parent === $loggedOrganization;
    }
}
