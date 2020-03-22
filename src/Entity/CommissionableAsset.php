<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommissionableAssetRepository")
 */
class CommissionableAsset implements AvailabilitableInterface
{
    public const TYPES = [
        'Véhicule léger' => 'VL',
        'Véhicule de premiers secours' => 'VPSP',
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
     * @Assert\Choice(choices=CommissionableAsset::TYPES)
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
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    public ?\DateTimeImmutable $lastCommissionDate = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\CommissionableAssetAvailability", mappedBy="asset")
     */
    public iterable $availabilities = [];

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

    public function commission(\DateTimeImmutable $date = null): void
    {
        $this->lastCommissionDate = $date ?: UserAvailability::createImmutableDateTime();
    }

    public function __toString(): string
    {
        return $this->type.' - '.$this->name;
    }

    public function getAvailabilities(): iterable
    {
        return $this->availabilities;
    }
}
