<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContext;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="assetType_unique_org_name", columns={"organization_id", "name"})})
 * @ORM\Entity(repositoryClass="App\Repository\AssetTypeRepository")
 * @UniqueEntity({"organization", "name"})
 */
class AssetType
{
    public const TYPE_NUMBER = 'number';
    public const TYPE_BOOLEAN = 'boolean';
    public const TYPE_SMALL_TEXT = 'smallText';
    public const TYPE_TEXT = 'text';

    public const TYPES = [self::TYPE_NUMBER, self::TYPE_BOOLEAN, self::TYPE_SMALL_TEXT, self::TYPE_TEXT];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", options={"unsigned": true})
     */
    public ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @Assert\NotNull
     */
    public ?Organization $organization = null;

    /**
     * @ORM\Column
     * @Assert\NotBlank
     */
    public string $name = '';

    /**
     * @var array Assets properties.
     *
     * Example: [ [ 'key' => 'generatedKey1', 'label' => 1, 'type' => 'number|boolean|choices|smallText|text', ?default => '', ?choices => [] ] ]
     *
     * @ORM\Column(type="json")
     * @Assert\Type(type="array")
     * @Assert\All({
     *     @Assert\Collection(
     *         fields = {
     *              "key" = {
     *                  @Assert\Type(type="string"),
     *                  @Assert\NotBlank,
     *              },
     *              "type" = {
     *                  @Assert\Type(type="string"),
     *                  @Assert\NotBlank,
     *              },
     *              "label" = {
     *                  @Assert\Type(type="string"),
     *                  @Assert\NotBlank,
     *              },
     *              "help" = {
     *                  @Assert\Type(type="string")
     *              },
     *              "required" = {
     *                  @Assert\Type(type="boolean")
     *              },
     *              "hidden" = {
     *                  @Assert\Type(type="boolean")
     *              },
     *         }
     *     )
     * })
     */
    public array $properties = [];

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @Assert\Callback()
     */
    public function validateProperties(ExecutionContext $context): void
    {
        $propertiesKeys = array_column($this->properties, 'key');
        if (\count(array_unique($propertiesKeys)) !== \count($propertiesKeys)) {
            $context
                ->buildViolation('assetType.propertyUniqueError')
                ->atPath('properties')
                ->addViolation();
        }
    }
}
