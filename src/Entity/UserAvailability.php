<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="user_availability_slot_unique", columns={"user_id", "start_time", "end_time"})
 *   }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserAvailabilityRepository")
 */
class UserAvailability implements AvailabilityInterface
{
    use AvailabilitableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="availabilities")
     * @ORM\JoinColumn(nullable=false)
     */
    public User $user;

    public function __construct(
        ?int $id,
        User $user,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        string $status = self::STATUS_LOCKED
    ) {
        $this->initialize($id, $startTime, $endTime, $status);
        $this->user = $user;
    }

    public function getOwner(): AvailabilitableInterface
    {
        return $this->user;
    }
}
