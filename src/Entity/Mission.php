<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MissionRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="mission_start_end_idx", columns={"start_time", "end_time"}),
 * })
 */
class Mission
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @Groups("mission:ajax")
     */
    public ?int $id = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Groups("mission:ajax")
     */
    public string $name = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @Assert\NotNull
     */
    public ?Organization $organization = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MissionType")
     * @Groups("mission:ajax")
     */
    public ?MissionType $type = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     * @Groups("mission:ajax")
     */
    public ?\DateTimeImmutable $startTime = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     * @Assert\GreaterThan(propertyPath="startTime")
     * @Groups("mission:ajax")
     */
    public ?\DateTimeImmutable $endTime = null;

    /**
     * @ORM\Column(type="text", options={"default": ""})
     */
    public string $comment = '';

    /**
     * @var User[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="missions")
     * @Groups("mission:ajax")
     */
    public Collection $users;

    /**
     * @var CommissionableAsset[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\CommissionableAsset")
     * @Groups("mission:ajax")
     */
    public Collection $assets;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->assets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
