<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommissionableAssetRepository")
 * @ORM\Table(indexes={
 *   @ORM\Index(name="commissionable_asset_name_idx", columns={"name"}),
 *   @ORM\Index(name="commissionable_asset_type_idx", columns={"type"}),
 * })
 */
class CommissionableAsset implements AvailabilitableInterface
{
    // TODO Use a parameter
    public const TYPES = [
        'VPSP' => 'Véhicule de premiers secours',
        'VL' => 'Véhicule léger',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Choice(callback={CommissionableAsset::class, "getTypesKeys"})
     */
    public string $type = '';

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
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    public bool $hasMobileRadio = false;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\Type("bool")
     */
    public bool $hasFirstAidKit = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $parkingLocation = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    public ?string $contact = null;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Positive
     */
    public int $seatingCapacity = 1;

    public function __construct(
        ?int $id,
        Organization $organization,
        string $type,
        string $name
    ) {
        $this->id = $id;
        $this->organization = $organization;
        $this->type = $type;
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->type.' - '.$this->name;
    }

    public function getAvailabilities(): iterable
    {
        return $this->availabilities;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public static function getTypesKeys(): array
    {
        return array_keys(self::TYPES);
    }
}
