<?php

declare(strict_types=1);

namespace App\DataFixtures\Faker\Provider;

use App\Domain\SkillSetDomain;
use App\Entity\AssetType;
use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Faker\Provider\Base as Faker;

class MissionTypeProvider
{
    private SkillSetDomain $skillSetDomain;
    private AssetTypeRepository $assetTypeRepository;

    public function __construct(SkillSetDomain $skillSetDomain, AssetTypeRepository $assetTypeRepository)
    {
        $this->skillSetDomain = $skillSetDomain;
        $this->assetTypeRepository = $assetTypeRepository;
    }

    public function randomSkillRequirement(): array
    {
        return array_map(
            static function (string $skill) {
                return [
                    'skill' => $skill,
                    'number' => Faker::numberBetween(1, 5),
                ];
            },
            Faker::randomElements($this->skillSetDomain->getSkillSetKeys(), random_int(1, 3))
        );
    }

    public function randomAssetTypeRequirement(AssetType ...$assetTypes): array
    {
        return array_map(
            static function (AssetType $assetType) {
                return [
                    'type' => $assetType->id,
                    'number' => Faker::numberBetween(1, 5),
                ];
            },
            Faker::randomElements($assetTypes, random_int(1, 2))
        );
    }
}
