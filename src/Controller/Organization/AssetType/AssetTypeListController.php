<?php

declare(strict_types=1);

namespace App\Controller\Organization\AssetType;

use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_assetType_list", methods={"GET"})
 * @Security("organization.isParent()")
 */
class AssetTypeListController extends AbstractController
{
    private AssetTypeRepository $assetTypeRepository;

    public function __construct(AssetTypeRepository $assetTypeRepository)
    {
        $this->assetTypeRepository = $assetTypeRepository;
    }

    public function __invoke(Organization $organization): Response
    {
        return $this->render('organization/assetType/list.html.twig', [
            'assetTypes' => $this->assetTypeRepository->findByOrganization($organization),
            'organization' => $organization,
        ]);
    }
}
