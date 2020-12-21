<?php

declare(strict_types=1);

namespace App\Entity;

use App\EntityListener\AddDependantSkillsEntityListener;
use App\EntityListener\UserPasswordEntityListener;
use App\Validator\Constraints\CurrentPassword;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use libphonenumber\PhoneNumber;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
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
 * }))
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("emailAddress")
 * @UniqueEntity("identificationNumber")
 * @CurrentPassword(groups={"Default", "user:password"})
 * @ORM\EntityListeners({AddDependantSkillsEntityListener::class, UserPasswordEntityListener::class})
 */
class User implements UserPasswordInterface, AvailabilitableInterface, UserSerializableInterface /*, \Serializable*/
{
    public const NIVOL_FORMAT = '#^\d+[A-Z]$#';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @Groups({"mission:ajax", "Default"})
     */
    public ?int $id = null;

    /**
     * @ORM\Column(unique=true)
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern=User::NIVOL_FORMAT,
     *     message="Le format est invalide, exemple : 0123456789A."
     * )
     */
    private string $identificationNumber = '';

    /**
     * @ORM\Column(unique=true)
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
     * @ORM\Column(type="phone_number", nullable=true)
     * @AssertPhoneNumber(defaultRegion="FR")
     */
    public ?PhoneNumber $phoneNumber = null;

    /**
     * @var string A "Y-m-d" formatted value
     *
     * @ORM\Column
     * @Assert\NotBlank
     * @Assert\Date
     */
    public string $birthday = '';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", fetch="EAGER")
     * @Assert\Expression("'ROLE_SUPER_ADMIN' in this.roles or value != null")
     */
    public ?Organization $organization = null;

    /**
     * Organizations whose user is admin.
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Organization", inversedBy="admins")
     * @ORM\OrderBy({"name"="ASC"})
     */
    public Collection $managedOrganizations;

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
     * @ORM\OneToMany(targetEntity="App\Entity\UserAvailability", mappedBy="user")
     */
    private iterable $availabilities = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ResetPasswordRequest", mappedBy="user", cascade={"remove"})
     */
    private iterable $resetPasswordRequests = []; // Used for cascade

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Mission", mappedBy="users")
     */
    public iterable $missions = [];

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    public array $properties = [];

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $password = null;

    /**
     * Not persisted in database, used to encode password.
     *
     * @Assert\NotBlank(groups={"user:password"})
     */
    public ?string $plainPassword = null;

    /**
     * Not persisted in database, used to update password.
     */
    public ?string $currentPassword = null;

    /**
     * @ORM\Column(type="array")
     */
    public array $roles = ['ROLE_USER'];

    public static function bootstrap(string $identifier = null): self
    {
        $user = new self();
        $user->birthday = '1990-01-01';

        if (empty($identifier)) {
            return $user;
        }

        if (filter_var($identifier, \FILTER_VALIDATE_EMAIL)) {
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

    public function __construct()
    {
        $this->managedOrganizations = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (null === $this->organization) {
            return $this->getFullName();
        }

        return $this->organization->name.' / '.$this->getFullName();
    }

    public function getNotNullOrganization(): Organization
    {
        if (null === $this->organization) {
            throw new \RuntimeException('Null user organization');
        }

        return $this->organization;
    }

    public function userSerialize(): array
    {
        return [
            'id' => $this->id,
            'identificationNumber' => $this->identificationNumber,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->identificationNumber,
            $this->emailAddress,
            $this->birthday,
            $this->password,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        [
            $this->id,
            $this->identificationNumber,
            $this->emailAddress,
            $this->birthday,
            $this->password,
        ] = unserialize($serialized, ['allowed_classes' => [__CLASS__]]);
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
        return $this->roles;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
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

    public function getAvailabilities(): iterable
    {
        return $this->availabilities;
    }

    /**
     * @return Collection|Organization[]
     */
    public function getManagedOrganizations(): Collection
    {
        return $this->managedOrganizations;
    }

    public function addManagedOrganization(Organization $organization): void
    {
        if (!$this->managedOrganizations->contains($organization)) {
            $this->managedOrganizations[] = $organization;
            $organization->addAdmin($this);
        }
    }

    public function removeManagedOrganization(Organization $organization): void
    {
        $this->managedOrganizations->removeElement($organization);
    }
}
