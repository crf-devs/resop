<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use App\Entity\AssetType;
use App\Repository\AssetTypeRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class EntitiesExtension extends AbstractExtension
{
    private AssetTypeRepository $assetTypeRepository;

    private array $cachedAssetTypes = [];

    public function __construct(AssetTypeRepository $assetTypeRepository)
    {
        $this->assetTypeRepository = $assetTypeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('assetTypeName', [$this, 'assetTypeName']),
            new TwigFilter('assetTypeProperties', [$this, 'assetTypeProperties']),
        ];
    }

    /**
     * Returns the asset type name by its ID. Caution: result is cached.
     *
     * @param int|string $id
     */
    public function assetTypeName($id): string
    {
        $assetType = $this->getAssetType((int) $id);

        if (null === $assetType) {
            return 'Unknow asset type';
        }

        return $assetType->name;
    }

    /**
     * Returns the asset type properties by its ID. Caution: result is cached.
     *
     * @param int|string|AssetType $assetType
     * @param int                  $length    Truncates or fills the array until its size matches this value if > 0
     */
    public function assetTypeProperties($assetType, int $length = 0): array
    {
        if (!$assetType instanceof AssetType) {
            $assetType = $this->getAssetType((int) $assetType);
        }

        $result = array_fill(0, $length, null);

        if (null === $assetType) {
            return $result;
        }

        if (!$length) {
            return $assetType->properties;
        }

        return \array_slice($assetType->properties, 0, $length) + $result;
    }

    private function getAssetType(int $id): ?AssetType
    {
        if (!isset($this->cachedAssetTypes[$id])) {
            $this->cachedAssetTypes[$id] = $this->assetTypeRepository->find($id);
        }

        return $this->cachedAssetTypes[$id];
    }
}
