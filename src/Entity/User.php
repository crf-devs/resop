<?php

namespace App\Entity;

use Assert\Assertion;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="users", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="user_identification_number_unique", columns={"identification_number"}),
 *   @ORM\UniqueConstraint(name="user_email_address_unique", columns={"email_address"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User
{
    /**
     * @ORM\Column
     */
    private ?string $identificationNumber = null;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

    /**
     * @ORM\Column
     */
    public ?string $emailAddress = null;

    /**
     * @ORM\Column
     */
    public ?string $firstName = null;

    /**
     * @ORM\Column
     */
    public ?string $lastName = null;

    /**
     * @ORM\Column
     */
    public ?string $phoneNumber = null;

    /**
     * @ORM\Column
     */
    public string $occupation = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     */
    public ?Organization $organization = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $organizationOccupation = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    public array $skillSet = [];

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $vulnerable = false;

    /**
     * @ORM\Column(type="boolean")
     */
    public bool $fullyEquipped = false;

    public function __toString(): string
    {
        return $this->organization->name.' / '.$this->getFullName();
    }

    public function setIdentificationNumber(string $identificationNumber): void
    {
        $identificationNumber = ltrim($identificationNumber, '0');

        Assertion::notEmpty($identificationNumber);

        $this->identificationNumber = $identificationNumber;
    }

    public function getIdentificationNumber(): string
    {
        return $this->identificationNumber;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        Assertion::email($emailAddress);

        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function getFullName(): string
    {
        return $this->firstName.' '.$this->lastName;
    }

    public function getShortFullName(): string
    {
        return $this->firstName.' '.$this->lastName[0].'.';
    }
}
