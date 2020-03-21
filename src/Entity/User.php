<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={
 *   @ORM\UniqueConstraint(name="user_identification_number_unique", columns={"identification_number"}),
 *   @ORM\UniqueConstraint(name="user_email_address_unique", columns={"email_address"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    private $id;

    /**
     * @ORM\Column
     */
    private $identificationNumber;

    /**
     * @ORM\Column
     */
    private $emailAddress;

    /**
     * @ORM\Column
     */
    private $firstName;

    /**
     * @ORM\Column
     */
    private $lastName;

    /**
     * @ORM\Column
     */
    private $phoneNumber;

    /**
     * @ORM\Column
     */
    private $occupation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     */
    private $organization;

    /**
     * @ORM\Column(nullable=true)
     */
    private $organizationOccupation;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $skillSet = [];

    /**
     * @ORM\Column(type="boolean")
     */
    private $vulnerable;

    /**
     * @ORM\Column(type="boolean")
     */
    private $fullyEquipped;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentificationNumber(): ?string
    {
        return $this->identificationNumber;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getOrganizationOccupation(): ?string
    {
        return $this->organizationOccupation;
    }

    public function getSkillSet(): array
    {
        return $this->skillSet;
    }

    public function isVulnerable(): bool
    {
        return $this->vulnerable;
    }

    public function isFullyEquipped(): bool
    {
        return $this->fullyEquipped;
    }
}
