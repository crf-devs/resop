<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
 */
class Organization
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id;

    /**
     * @ORM\Column
     */
    public string $name;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $password = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     */
    public ?self $parent;

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
}
