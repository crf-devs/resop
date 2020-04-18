<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Repository\CommissionableAssetAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{organization}/commissionable-assets/{asset}/delete", name="app_commissionable_asset_delete", methods={"GET"})
 * @Security("asset.organization.id == organization")
 */
class CommissionableAssetDeleteController extends AbstractController
{
    private CommissionableAssetAvailabilityRepository $commissionableAvailabilityRepository;

    public function __construct(CommissionableAssetAvailabilityRepository $commissionableAssetAvailabilityRepository)
    {
        $this->commissionableAvailabilityRepository = $commissionableAssetAvailabilityRepository;
    }

    public function __invoke(EntityManagerInterface $entityManager, CommissionableAsset $asset): RedirectResponse
    {
        $organization = $this->getUser();
        if (!$organization instanceof Organization || false === $organization->isParent()) {
            throw new AccessDeniedException();
        }

        $entityManager->beginTransaction();
        $this->commissionableAvailabilityRepository->deleteByOwner($asset);
        $entityManager->remove($asset);
        $entityManager->flush();
        $entityManager->commit();

        $this->addFlash('success', 'Le véhicule a été supprimé avec succès.');

        return $this->redirectToRoute('app_organization_commissionable_assets', ['organization' => $asset->organization->getId()]);
    }
}
