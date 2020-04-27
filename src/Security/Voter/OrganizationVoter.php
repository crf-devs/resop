<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Organization;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class OrganizationVoter extends Voter
{
    public const CAN_MANAGE = 'CAN_MANAGE_ORGANIZATION';

    protected function supports($attribute, $subject): bool
    {
        return self::CAN_MANAGE === $attribute && $subject instanceof Organization;
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
