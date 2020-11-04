<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\Organization;
use App\Entity\User;
use App\Security\Voter\OrganizationVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OrganizationVoterTest extends TestCase
{
    /** @dataProvider voteProvider */
    public function testVote(string $attribute, Organization $subject, int $expectedResult, ?UserInterface $user): void
    {
        $voter = new OrganizationVoter();
        $token = $this->createMock(TokenInterface::class);

        $token->expects(self::exactly($expectedResult ? 1 : 0))->method('getUser')->willReturn($user);

        self::assertSame($expectedResult, $voter->vote($token, $subject, [$attribute]));
    }

    public function voteProvider(): array
    {
        $organization = new Organization();
        $child = new Organization();
        $child->parent = $organization;

        return [
            'wrong attribute abstains' => [
                'attribute' => 'foo',
                'subject' => $organization,
                'expectedResult' => VoterInterface::ACCESS_ABSTAIN,
                'loggedOrganization' => null,
            ],
            'denies access for plain user' => [
                'attribute' => OrganizationVoter::CAN_MANAGE,
                'subject' => $organization,
                'expectedResult' => VoterInterface::ACCESS_DENIED,
                'loggedOrganization' => new User(),
            ],
            'denies access for different organization' => [
                'attribute' => OrganizationVoter::CAN_MANAGE,
                'subject' => $organization,
                'expectedResult' => VoterInterface::ACCESS_DENIED,
                'loggedOrganization' => new Organization(),
            ],
            'grants access for same organization' => [
                'attribute' => OrganizationVoter::CAN_MANAGE,
                'subject' => $organization,
                'expectedResult' => VoterInterface::ACCESS_GRANTED,
                'loggedOrganization' => $organization,
            ],
            'denies access for child organization' => [
                'attribute' => OrganizationVoter::CAN_MANAGE,
                'subject' => $organization,
                'expectedResult' => VoterInterface::ACCESS_DENIED,
                'loggedOrganization' => $child,
            ],
            'grants access for parent organization' => [
                'attribute' => OrganizationVoter::CAN_MANAGE,
                'subject' => $child,
                'expectedResult' => VoterInterface::ACCESS_GRANTED,
                'loggedOrganization' => $organization,
            ],
        ];
    }
}
