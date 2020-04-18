<?php

declare(strict_types=1);

namespace App\Tests\Security\Voter;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Entity\User;
use App\Security\Voter\CommissionableAssetVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CommissionableAssetVoterTest extends TestCase
{
    /** @dataProvider voteProvider */
    public function testVote(string $attribute, $subject, int $expectedResult, ?UserInterface $user): void
    {
        $voter = new CommissionableAssetVoter();
        $token = $this->createMock(TokenInterface::class);

        $token->expects($this->exactly($expectedResult ? 1 : 0))->method('getUser')->willReturn($user);

        $this->assertSame($expectedResult, $voter->vote($token, $subject, [$attribute]));
    }

    public function voteProvider(): array
    {
        $parentOrganization = new Organization();
        $childOrganization = new Organization();
        $childOrganization->parent = $parentOrganization;

        $parentAsset = new CommissionableAsset();
        $parentAsset->organization = $parentOrganization;

        $childAsset = new CommissionableAsset();
        $childAsset->organization = $childOrganization;

        return [
            'wrong attribute abstains' => [
                'attribute' => 'foo',
                'subject' => $parentAsset,
                'expectedResult' => VoterInterface::ACCESS_ABSTAIN,
                'loggedOrganization' => null,
            ],
            'wrong subject type abstains' => [
                'attribute' => CommissionableAssetVoter::CAN_EDIT,
                'subject' => new \stdClass(),
                'expectedResult' => VoterInterface::ACCESS_ABSTAIN,
                'loggedOrganization' => null,
            ],
            'denies access for plain user' => [
                'attribute' => CommissionableAssetVoter::CAN_EDIT,
                'subject' => $parentAsset,
                'expectedResult' => VoterInterface::ACCESS_DENIED,
                'loggedOrganization' => new User(),
            ],
            'denies access for different organization' => [
                'attribute' => CommissionableAssetVoter::CAN_EDIT,
                'subject' => $parentAsset,
                'expectedResult' => VoterInterface::ACCESS_DENIED,
                'loggedOrganization' => new Organization(),
            ],
            'grants access for same organization' => [
                'attribute' => CommissionableAssetVoter::CAN_EDIT,
                'subject' => $parentAsset,
                'expectedResult' => VoterInterface::ACCESS_GRANTED,
                'loggedOrganization' => $parentOrganization,
            ],
            'denies access for child organization' => [
                'attribute' => CommissionableAssetVoter::CAN_EDIT,
                'subject' => $parentAsset,
                'expectedResult' => VoterInterface::ACCESS_DENIED,
                'loggedOrganization' => $childOrganization,
            ],
            'grants access for parent organization' => [
                'attribute' => CommissionableAssetVoter::CAN_EDIT,
                'subject' => $childAsset,
                'expectedResult' => VoterInterface::ACCESS_GRANTED,
                'loggedOrganization' => $parentOrganization,
            ],
        ];
    }
}
