<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
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
     * @Assert\GreaterThan(propertyPath="startTime")
     */
    public \DateTimeImmutable $endTime;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Choice(choices=AvailabilityInterface::STATUSES)
     */
    public string $status = '';

    /**
     * @ORM\Column(type="text", options={"default": ""})
     */
    public string $comment = '';

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

    private function initialize(?int $id, \DateTimeImmutable $startTime, \DateTimeImmutable $endTime, string $status = AvailabilityInterface::STATUS_AVAILABLE): void
    {
        $this->id = $id;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->createdAt = self::createImmutableDateTime();
        $this->updateStatus($status);
    }

    public function book(Organization $planningAgent = null, string $comment = ''): void
    {
        $this->updateStatus(self::STATUS_BOOKED, $planningAgent, $comment);
        $this->bookedAt = self::createImmutableDateTime();
    }

    public function declareAvailable(Organization $planningAgent = null): void
    {
        $this->updateStatus(AvailabilityInterface::STATUS_AVAILABLE, $planningAgent);
    }

    public function lock(Organization $planningAgent = null, string $comment = ''): void
    {
        $this->updateStatus(self::STATUS_LOCKED, $planningAgent);
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

    public function getComment(): string
    {
        return $this->comment;
    }

    private function updateStatus(string $newStatus, Organization $planningAgent = null, string $comment = ''): void
    {
        $this->status = $newStatus;
        $this->planningAgent = $planningAgent;
        $this->updatedAt = self::createImmutableDateTime();
        $this->comment = $comment;
    }
}
