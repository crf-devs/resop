<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Organization;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class OrganizationVoter extends Voter
{
    public const ROLE_ORGANIZATION = 'ROLE_ORGANIZATION';
    public const ROLE_PARENT_ORGANIZATION = 'ROLE_PARENT_ORGANIZATION';

    private AccessDecisionManagerInterface $decisionManager;

    public function __construct(AccessDecisionManagerInterface $decisionManager)
    {
        $this->decisionManager = $decisionManager;
    }

    protected function supports($attribute, $subject): bool
    {
        return \in_array($attribute, [
            self::ROLE_ORGANIZATION,
            self::ROLE_PARENT_ORGANIZATION,
        ], true);
    }

    /**
     * @param string            $attribute
     * @param Organization|null $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User|null $user */
        $user = $token->getUser();

        if (!$user instanceof User || null === $user->getPassword()) {
            return false;
        }

        if (!$subject instanceof Organization) {
            return !$user->getManagedOrganizations()->isEmpty();
        }

        if ($this->decisionManager->decide($token, ['ROLE_SUPER_ADMIN'])) {
            return true;
        }

        if (self::ROLE_PARENT_ORGANIZATION === $attribute) {
            return $subject->getAdmins()->contains($user) || $subject->getParentOrganization()->getAdmins()->contains($user);
        }

        return $subject->getAdmins()->contains($user);
    }
}
