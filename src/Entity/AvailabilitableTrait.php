<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

trait AvailabilitableTrait
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

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
     * @Assert\NotBlank
     * @Assert\Choice(choices=AvailabilityInterface::STATUSES)
     */
    public string $status = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @ORM\JoinColumn(nullable=true)
     */
    public ?Organization $planningAgent = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    public ?\DateTimeImmutable $bookedAt = null;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    public \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    public ?\DateTimeImmutable $updatedAt = null;

    public static function createImmutableDateTime(): \DateTimeImmutable
    {
        $date = \DateTimeImmutable::createFromFormat('U', (string) time());
        if (false === $date) {
            throw new \RuntimeException('Unable to create the datetime');
        }

        return $date;
    }

    private function initialize(?int $id, \DateTimeImmutable $startTime, \DateTimeImmutable $endTime, string $status = self::STATUS_LOCKED): void
    {
        $this->id = $id;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->status = $status;
        $this->createdAt = self::createImmutableDateTime();
    }

    public function book(Organization $planningAgent, \DateTimeImmutable $bookedAt = null): void
    {
        $this->planningAgent = $planningAgent;
        $this->bookedAt = $bookedAt ?: self::createImmutableDateTime();
        $this->updatedAt = $bookedAt ?: self::createImmutableDateTime();
        $this->status = self::STATUS_BOOKED;
    }

    public function declareAvailable(\DateTimeImmutable $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt ?: self::createImmutableDateTime();
        $this->status = self::STATUS_AVAILABLE;
    }

    public function lock(\DateTimeImmutable $updatedAt = null): void
    {
        $this->updatedAt = $updatedAt ?: self::createImmutableDateTime();
        $this->status = self::STATUS_LOCKED;
    }

    public function getStartTime(): \DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getEndTime(): \DateTimeImmutable
    {
        return $this->endTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
