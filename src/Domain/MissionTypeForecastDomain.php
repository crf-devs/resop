<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\MissionType;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserRepository;

class MissionTypeForecastDomain
{
    private UserRepository $userRepository;
    private CommissionableAssetRepository $assetsRepository;
    private SkillSetDomain $skillSetDomain;

    public function __construct(
        UserRepository $userRepository,
        CommissionableAssetRepository $assetsRepository,
        SkillSetDomain $skillSetDomain
    ) {
        $this->userRepository = $userRepository;
        $this->assetsRepository = $assetsRepository;
        $this->skillSetDomain = $skillSetDomain;
    }

    public function calculatePerMissionTypes(array $filters): array
    {
        if (empty($filters['availableFrom']) || empty($filters['availableTo'])) {
            return [];
        }

        $results = [];

        /** @var MissionType $missionType */
        foreach ($filters['missionTypes'] ?? [] as $missionType) {
            $results[$missionType->id] = $this->calculateHowMany($missionType, $filters);
            $results[$missionType->id]['potential_missions_number'] = 0;

            $allResources = array_merge($results[$missionType->id]['users'] ?? [], $results[$missionType->id]['assets'] ?? []);
            if (\count($allResources)) {
                $results[$missionType->id]['potential_missions_number'] = min(array_column($allResources, 'potential_missions_number'));
            }
        }

        return $results;
    }

    private function calculateHowMany(MissionType $missionType, array $filters): array
    {
        $usedIds = ['users' => [], 'assets' => []];
        $result = [];

        // TODO If 3 CH_VPSP and only 1 PSE2 are available, we should count some CH_VPSP as PSE2
        foreach ($this->getSortedSkills($missionType) as $skillRequirement) {
            $skill = $skillRequirement['skill'];
            $ids = $this->userRepository->findByFilters(array_merge($filters, ['userSkills' => [$skill]]), true);
            $ids = array_diff(array_column($ids, 'id'), $usedIds['users']);
            $result['users'][$skill]['ids'] = $ids;
            $result['users'][$skill]['potential_missions_number'] = ceil(\count($ids) / $skillRequirement['number']);

            $usedIds['users'] = array_merge($usedIds['users'], $ids);
        }

        foreach ($missionType->assetTypesRequirement as $assetRequirement) {
            $type = $assetRequirement['type'];
            $ids = $this->assetsRepository->findByFilters(array_merge($filters, ['assetTypes' => [$type]]), true);
            $ids = array_column($ids, 'id');
            $result['assets'][$type]['ids'] = array_diff($ids, $usedIds['assets']);
            $result['assets'][$type]['potential_missions_number'] = ceil(\count($ids) / $assetRequirement['number']);

            $usedIds['assets'] = array_merge($usedIds['assets'], $result['assets'][$type]['ids']);
        }

        return $result;
    }

    private function getSortedSkills(MissionType $missionType): array
    {
        $skillKeys = array_map(fn ($skillRequirement) => $skillRequirement['skill'] ?? null, $missionType->userSkillsRequirement);
        $skills = (array) array_combine($skillKeys, $missionType->userSkillsRequirement);

        $orderedSkills = $this->skillSetDomain->getSkillSetKeys();
        $skillKeys = array_intersect($orderedSkills, $skillKeys);

        return array_map(fn ($key) => $skills[$key], $skillKeys);
    }
}
