<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommissionableAssetRepository")
 * @ORM\Table(indexes={
 *   @ORM\Index(name="commissionable_asset_name_idx", columns={"name"})
 * })
 */
class CommissionableAsset implements AvailabilitableInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\AssetType")
     * @ORM\JoinColumn(nullable=false)
     */
    public AssetType $assetType;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    public string $name = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @ORM\JoinColumn(nullable=false)
     */
    public Organization $organization;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CommissionableAssetAvailability", mappedBy="asset")
     */
    public iterable $availabilities = [];

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    public array $properties = [];

    public function __toString(): string
    {
        return $this->assetType->name.' - '.$this->name;
    }

    public function getAvailabilities(): iterable
    {
        return $this->availabilities;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
