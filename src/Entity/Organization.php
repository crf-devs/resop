<?php

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     */
    public ?self $parent;

    public function __construct(?int $id, string $name, self $parent = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent = $parent;
    }
}
