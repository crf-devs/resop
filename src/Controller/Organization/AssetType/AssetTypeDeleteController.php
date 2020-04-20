<?php

declare(strict_types=1);

namespace App\Controller\Organization\AssetType;

use App\Entity\AssetType;
use App\Entity\Organization;
use App\Repository\AssetTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/assetType/delete/{id}", name="app_organization_assetType_delete", methods={"GET"})
 */
class AssetTypeDeleteController extends AbstractController
{
    private AssetTypeRepository $assetTypeRepository;

    public function __construct(AssetTypeRepository $assetTypeRepository)
    {
        $this->assetTypeRepository = $assetTypeRepository;
    }

    public function __invoke(AssetType $assetType): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();

        return $this->render('organization/assetType/list.html.twig', [
            'assetTypes' => $this->assetTypeRepository->findByOrganization($organization),
        ]);
    }
}
