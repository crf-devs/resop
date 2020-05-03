<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityListener\UserPasswordEntityListener;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *   indexes={
 *     @ORM\Index(name="organization_name_idx", columns={"name"}),
 *   }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
 * @ORM\EntityListeners({UserPasswordEntityListener::class})
 */
class Organization implements UserPasswordInterface, UserSerializableInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    public string $name = '';

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $password = null;

    /**
     * Not persisted in database, used to encode password.
     */
    public ?string $plainPassword = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     */
    public ?self $parent = null;

    public function __toString(): string
    {
        if ($this->parent) {
            return $this->parent->name.' - '.$this->name;
        }

        return $this->name;
    }

    public function userSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->__toString(),
        ];
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new \LogicException('Id must be defined');
        }

        return $this->id;
    }

    public function getRoles(): array
    {
        $roles = ['ROLE_ORGANIZATION'];

        if ($this->isParent()) {
            $roles[] = 'ROLE_PARENT_ORGANIZATION';
        }

        return $roles;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->name;
    }

    public function eraseCredentials(): void
    {
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

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}
