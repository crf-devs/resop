<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MissionTypeRepository")
 */
class MissionType
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization")
     * @Assert\NotNull
     */
    public ?Organization $organization = null;

    /**
     * @var int|null allow to count resources as available even if they are not available on the full date range
     * @ORM\Column(type="integer", nullable=true)
     */
    public ?int $minimumAvailableHours = null;

    /**
     * @var array Skills and number of required users.
     *
     * Example: [ [ 'skill' => 'ci_bspp', 'number' => 1 ], [ 'skill' => 'ch_vpsp', 'number' => 1 ], [ 'skill' => 'pse2', 'number' => 2 ] ]
     *
     * @ORM\Column(type="json")
     * @Assert\Type(type="array")
     * @Assert\All({
     *     @Assert\Collection(
     *         fields = {
     *              "skill" = {
     *                  @Assert\Type(type="string"),
     *                  @Assert\NotBlank,
     *              },
     *              "number" = {
     *                  @Assert\Type(type="integer"),
     *                  @Assert\Range(min="1"),
     *              },
     *         }
     *     )
     * })
     *
     * @todo Validate the skills name
     */
    public array $userSkillsRequirement = [];

    /**
     * @var array Type and number of required assets.
     *
     * Example: [ [ 'type' => 'VPSP', 'number' => 1 ] ]
     *
     * @ORM\Column(type="json")
     * @Assert\Type(type="array")
     * @Assert\All({
     *     @Assert\Collection(
     *         fields={
     *              "type" = {
     *                  @Assert\Type(type="integer"),
     *                  @Assert\NotBlank,
     *              },
     *              "number" = {
     *                  @Assert\Type(type="int"),
     *                  @Assert\Range(min="1"),
     *              },
     *         }
     *     )
     * })
     */
    public array $assetTypesRequirement = [];
}
