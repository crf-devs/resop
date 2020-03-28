<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="users", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="user_identification_number_unique", columns={"identification_number"}),
 *   @ORM\UniqueConstraint(name="user_email_address_unique", columns={"email_address"})
 * }, indexes={
 *   @ORM\Index(name="user_firstname_idx", columns={"first_name"}),
 *   @ORM\Index(name="user_lastname_idx", columns={"last_name"}),
 *   @ORM\Index(name="user_skill_set_idx", columns={"skill_set"}),
 *   @ORM\Index(name="user_vulnerable_idx", columns={"vulnerable"}),
 *   @ORM\Index(name="user_fully_equipped_idx", columns={"fully_equipped"}),
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("emailAddress")
 * @UniqueEntity("identificationNumber")
 */
class User implements UserInterface, AvailabilitableInterface, JsonSerializable
{
    const NIVOL_FORMAT = '#^\d+[A-Z]$#';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern=User::NIVOL_FORMAT,
     *     message="Le format est invalide, exemple : 0123456789A."
     * )
     */
    private string $identificationNumber = '';

    /**
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Email
     */
    public string $emailAddress = '';

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    public string $firstName = '';

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    public string $lastName = '';

    /**
     * @ORM\Column
     * @Assert\Regex(
     *     pattern="#^0[1-9][0-9]{8}$#",
     *     message="Le format est invalide, exemple : 0601020304."
     * )
     */
    public string $phoneNumber = '';

    /**
     * @var string A "Y-m-d" formatted value
     *
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Date
     */
    public string $birthday = '';

    /**
     * @ORM\Column
     */
    public string $occupation = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @Assert\NotNull()
     */
    public ?Organization $organization = null;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $organizationOccupation = null;

    /**
     * @ORM\Column(type="text[]", nullable=true)
     * @Assert\NotBlank
     * @Assert\All({
     *     @Assert\NotBlank
     * })
     * TODO Add a custom contraint in order to validate skills
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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserAvailability", mappedBy="user")
     */
    private iterable $availabilities = [];

    public static function bootstrap(string $identifier = null): self
    {
        $user = new self();

        if (empty($identifier)) {
            return $user;
        }

        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            $user->setEmailAddress($identifier);

            return $user;
        }

        $user->setIdentificationNumber($identifier);

        return $user;
    }

    public static function normalizeIdentificationNumber(string $identificationNumber): string
    {
        return u($identificationNumber)->trimStart('0')->toString();
    }

    public static function normalizeEmailAddress(string $emailAddress): string
    {
        return u($emailAddress)->trim()->lower()->toString();
    }

    public function __toString(): string
    {
        if (null === $this->organization) {
            return $this->getFullName();
        }

        return $this->organization->name.' / '.$this->getFullName();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'identificationNumber' => $this->identificationNumber,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setIdentificationNumber(string $identificationNumber): void
    {
        $this->identificationNumber = self::normalizeIdentificationNumber($identificationNumber);
    }

    public function getIdentificationNumber(): string
    {
        return $this->identificationNumber;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = self::normalizeEmailAddress($emailAddress);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getShortFullName(): string
    {
        return sprintf('%s %s.', $this->firstName, substr($this->lastName ?: '', 0, 1));
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->identificationNumber;
    }

    public function eraseCredentials(): void
    {
    }

    public function getBirthday(): ?string
    {
        return $this->birthday;
    }

    public function setBirthday(string $birthday): void
    {
        $this->birthday = $birthday;
    }

    public function getAvailabilities(): iterable
    {
        return $this->availabilities;
    }
}
