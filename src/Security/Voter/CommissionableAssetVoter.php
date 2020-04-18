<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CommissionableAssetVoter extends Voter
{
    public const CAN_EDIT = 'CAN_EDIT_ASSET';

    protected function supports($attribute, $subject): bool
    {
        return self::CAN_EDIT === $attribute
            && $subject instanceof CommissionableAsset;
    }

    /**
     * @param string              $attribute
     * @param CommissionableAsset $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $loggedOrganization = $token->getUser();

        if (!$loggedOrganization instanceof Organization) {
            return false;
        }

        return $subject->organization === $loggedOrganization || $subject->organization->parent === $loggedOrganization;
    }
}
