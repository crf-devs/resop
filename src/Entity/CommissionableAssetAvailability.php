<?php

namespace App\Entity;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="asset_availability_slot_unique", columns={"asset_id", "start_time", "end_time"})
 *   }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommissionableAssetAvailabilityRepository")
 */
class CommissionableAssetAvailability
{
    private const STATUS_AVAILABLE = 'available';
    private const STATUS_BOOKED = 'booked';
    private const STATUS_LOCKED = 'locked';

    private const STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_BOOKED,
        self::STATUS_LOCKED,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    public \DateTimeImmutable $startTime;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    public \DateTimeImmutable $endTime;

    /**
     * @ORM\Column
     */
    public string $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CommissionableAsset")
     * @ORM\JoinColumn(nullable=false)
     */
    public CommissionableAsset $asset;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    public ?User $planningAgent;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    public ?\DateTimeImmutable $bookedAt;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    public \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    public ?\DateTimeImmutable $updatedAt;

    public function __construct(
        ?int $id,
        CommissionableAsset $asset,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        string $status = self::STATUS_LOCKED
    ) {
        Assertion::inArray($status, self::STATUSES);
        Assertion::same($startTime->format('Y-m-d'), $endTime->format('Y-m-d'));

        $this->id = $id;
        $this->asset = $asset;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->status = $status;
        $this->createdAt = \DateTimeImmutable::createFromFormat('U', time());
    }

    public function book(User $planningAgent, \DateTimeImmutable $bookedAt = null): void
    {
        Assertion::eq($this->status, self::STATUS_AVAILABLE);

        $this->planningAgent = $planningAgent;
        $this->bookedAt = $bookedAt ?: \DateTimeImmutable::createFromFormat('U', time());
        $this->updatedAt = $bookedAt ?: \DateTimeImmutable::createFromFormat('U', time());
        $this->status = self::STATUS_BOOKED;
    }

    public function declareAvailable(\DateTimeImmutable $updatedAt = null): void
    {
        Assertion::eq($this->status, self::STATUS_LOCKED);

        $this->updatedAt = $updatedAt ?: \DateTimeImmutable::createFromFormat('U', time());
        $this->status = self::STATUS_AVAILABLE;
    }
}
