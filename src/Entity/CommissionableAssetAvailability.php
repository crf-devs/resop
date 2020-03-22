<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="asset_availability_slot_unique", columns={"asset_id", "start_time", "end_time"})
 *   }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CommissionableAssetAvailabilityRepository")
 */
class CommissionableAssetAvailability implements AvailabilityInterface
{
    use AvailabilitableTrait;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CommissionableAsset")
     * @ORM\JoinColumn(nullable=false)
     */
    public CommissionableAsset $asset;

    public function __construct(
        ?int $id,
        CommissionableAsset $asset,
        \DateTimeImmutable $startTime,
        \DateTimeImmutable $endTime,
        string $status = AvailabilityInterface::STATUS_LOCKED
    ) {
        $this->initialize($id, $startTime, $endTime, $status);
        $this->asset = $asset;
    }
}
