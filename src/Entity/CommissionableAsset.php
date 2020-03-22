<?php

declare(strict_types=1);

namespace App\Entity;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommissionableAssetRepository")
 */
class CommissionableAsset
{
    private const TYPES = [
        'Véhicule léger' => 'VL',
        'Véhicule de premiers secours' => 'VPSP',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private ?int $id;

    /**
     * @ORM\Column
     */
    public string $type;

    /**
     * @ORM\Column
     */
    public string $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @ORM\JoinColumn(nullable=false)
     */
    private Organization $organization;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    public ?\DateTimeImmutable $lastCommissionDate;

    public function __construct(
        ?int $id,
        Organization $organization,
        string $type,
        string $name
    ) {
        Assertion::inArray($type, self::TYPES);

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
        return $this->type . ' - ' . $this->name;
    }
}
