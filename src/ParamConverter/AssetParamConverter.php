<?php

declare(strict_types=1);

namespace App\ParamConverter;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Repository\CommissionableAssetRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AssetParamConverter implements ParamConverterInterface
{
    private CommissionableAssetRepository $assetRepository;
    private OrganizationParamConverter $organizationParamConverter;

    public function __construct(CommissionableAssetRepository $assetRepository, OrganizationParamConverter $organizationParamConverter)
    {
        $this->assetRepository = $assetRepository;
        $this->organizationParamConverter = $organizationParamConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $name = $configuration->getName();

        /** @var Organization|int|null $organization */
        $organization = $request->attributes->get('organization');

        // Force OrganizationParamConverter to retrieve the organization
        if (!\is_object($organization)) {
            $this->organizationParamConverter->apply($request, new ParamConverter(['name' => 'organization']));
            $organization = $request->attributes->get('organization');
        }

        $id = $request->attributes->getInt($name);
        $asset = $this->assetRepository->findOneByIdAndOrganization($id, $organization);

        if (null === $asset) {
            throw new NotFoundHttpException(sprintf('Asset with id "%d" does not exist.', $id));
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
