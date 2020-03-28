<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="organisation_name_unique", columns={"name"})
 *   }, indexes={
 *     @ORM\Index(name="organization_name_idx", columns={"name"}),
 *   }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
 */
class Organization implements UserInterface, \JsonSerializable
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     */
    public ?self $parent = null;

    public function __construct(?int $id, string $name, self $parent = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent = $parent;
    }

    public function __toString(): string
    {
        if ($this->parent) {
            return $this->parent->name.' - '.$this->name;
        }

        return $this->name;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->__toString(),
        ];
    }

    public function getRoles(): array
    {
        return ['ROLE_ORGANIZATION'];
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

    public function getParentName(): ?string
    {
        if (null === $this->parent) {
            return  null;
        }

        return $this->parent->getName();
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }
}
