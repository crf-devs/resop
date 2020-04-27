<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\CommissionableAsset;
use App\Repository\CommissionableAssetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AssetParamConverter implements ParamConverterInterface
{
    private CommissionableAssetRepository $assetRepository;

    public function __construct(CommissionableAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();

        $asset = $this->assetRepository->findOneBy(
            [
                'id' => $assetId = $request->attributes->getInt($name),
                'organization' => $organizationId = $request->attributes->getInt('organization'),
            ]
        );

        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset with id "%d" and organization id "%d" does not exist.', $assetId, $organizationId));
        }

        $request->attributes->set($name, $asset);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration): bool
    {
        return CommissionableAsset::class === $configuration->getClass();
    }
}
