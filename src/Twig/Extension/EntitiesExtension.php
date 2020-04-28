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
     * @param int|string $id
     * @param int        $lenght Truncates or fills the array until its size matches this value if > 0
     */
    public function assetTypeProperties($id, int $lenght = 0): array
    {
        $assetType = $this->getAssetType((int) $id);
        $result = array_fill(0, $lenght, null);

        if (null === $assetType) {
            return $result;
        }

        if (!$lenght) {
            return $assetType->properties;
        }

        return \array_slice($assetType->properties, 0, $lenght) + $result;
    }

    private function getAssetType(int $id): ?AssetType
    {
        if (!isset($this->cachedAssetTypes[$id])) {
            $this->cachedAssetTypes[$id] = $this->assetTypeRepository->find($id);
        }

        return $this->cachedAssetTypes[$id];
    }
}
