<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="organization_name_idx", columns={"name"}),
 *   }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
 */
class Organization
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
     */
    public string $name = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="children")
     */
    public ?self $parent = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization", mappedBy="parent", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"name"="ASC"})
     */
    public Collection $children;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="organizations")
     */
    public Collection $admins;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->admins = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->parent) {
            return $this->parent->name.' - '.$this->name;
        }

        return $this->name;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new \LogicException('Id must be defined');
        }

        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isParent(): bool
    {
        return null === $this->parent;
    }

    public function getParentOrganization(): Organization
    {
        if ($this->isParent()) {
            return $this;
        }

        /** @var Organization $parent */
        $parent = $this->parent;

        return $parent;
    }

    public function getParentName(): ?string
    {
        if (null === $this->parent) {
            return null;
        }

        return $this->parent->getName();
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $organization): void
    {
        if (!$this->children->contains($organization)) {
            $this->children[] = $organization;
        }
    }

    public function removeChild(self $organization): void
    {
        $this->children->removeElement($organization);
    }

    /**
     * @return Collection|User[]
     */
    public function getAdmins(): Collection
    {
        return $this->admins;
    }

    public function addAdmin(User $admin): void
    {
        if (!$this->admins->contains($admin)) {
            $this->admins[] = $admin;
            $admin->addOrganization($this);
        }
    }

    public function removeAdmin(User $admin): void
    {
        $this->admins->removeElement($admin);
    }
}
