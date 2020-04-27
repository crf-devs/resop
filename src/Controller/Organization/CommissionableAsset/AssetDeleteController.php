<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Security\Voter\CommissionableAssetVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{asset<\d+>}/delete", name="app_organization_asset_delete", methods={"GET"})
 * @IsGranted(CommissionableAssetVoter::CAN_EDIT, subject="asset")
 */
class AssetDeleteController extends AbstractController
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
