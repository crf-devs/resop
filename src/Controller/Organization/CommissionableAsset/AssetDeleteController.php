<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\CommissionableAsset;
use App\Repository\CommissionableAssetAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{asset<\d+>}/delete", name="app_organization_asset_delete", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', asset.organization)")
 */
class AssetDeleteController extends AbstractOrganizationController
{
    private CommissionableAssetAvailabilityRepository $commissionableAvailabilityRepository;

    public function __construct(CommissionableAssetAvailabilityRepository $commissionableAssetAvailabilityRepository)
    {
        $this->commissionableAvailabilityRepository = $commissionableAssetAvailabilityRepository;
    }

    public function __invoke(EntityManagerInterface $entityManager, CommissionableAsset $asset): RedirectResponse
    {
        $entityManager->beginTransaction();
        $this->commissionableAvailabilityRepository->deleteByOwner($asset);
        $entityManager->remove($asset);
        $entityManager->flush();
        $entityManager->commit();

        $this->addFlash('success', 'Le véhicule a été supprimé avec succès.');

        return $this->redirectToRoute('app_organization_assets', ['organization' => $asset->organization->getId()]);
    }
}
