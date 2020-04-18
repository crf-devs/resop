<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Organization;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizationVoter extends Voter
{
    public const CAN_LIST_ASSETS = 'CAN_LIST_ASSETS';
    public const CAN_ADD_ASSET = 'CAN_ADD_ASSETS';
    public const CAN_LIST_USERS = 'CAN_LIST_USERS';

    protected function supports($attribute, $subject): bool
    {
        return \in_array($attribute, [self::CAN_LIST_ASSETS, self::CAN_ADD_ASSET, self::CAN_LIST_USERS], true)
            && $subject instanceof Organization;
    }

    /**
     * @param string       $attribute
     * @param Organization $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedOrganization = $token->getUser();

        if (!$loggedOrganization instanceof Organization) {
            return false;
        }

        return $this->canAccessOrganization($loggedOrganization, $subject);
    }

    private function canAccessOrganization(Organization $loggedOrganization, Organization $organization): bool
    {
        return $loggedOrganization === $organization || $loggedOrganization === $organization->parent;
    }
}
