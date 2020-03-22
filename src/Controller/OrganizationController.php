<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Form\Type\CommissionableAssetType;
use App\Repository\CommissionableAssetRepository;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/organization")
 */
class OrganizationController extends AbstractController
{
    private OrganizationRepository $organizationRepository;

    private CommissionableAssetRepository $assetRepository;

    public function __construct(
        OrganizationRepository $organizationRepository,
        CommissionableAssetRepository $assetRepository
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @Route("/home", name="organization_home", methods={"GET"})
     */
    public function home(): Response
    {
        $organization = $this->organizationRepository->find(1);

        return $this->render('organization/home.html.twig', [
            'organization' => $organization,
        ]);
    }

    /**
     * @Route("/commissionable-assets", name="organization_commissionable_assets", methods={"GET"})
     */
    public function assets(): Response
    {
        $organization = $this->organizationRepository->find(1);
        $assets = $this->assetRepository->findBy([
            'organization' => $organization,
        ]);

        return $this->render('organization/commissionable_assets_list.html.twig', [
            'organization' => $organization,
            'assets' => $assets,
        ]);
    }

    /**
     * @Route("/commissionable-assets/add", name="organization_commissionable_add_asset", methods={"GET", "POST"})
     */
    public function addAsset(Request $request): Response
    {
        /** @var Organization $organization */
        $organization = $this->organizationRepository->find(1);
        $asset = new CommissionableAsset(null, $organization, 'VL', '');

        $form = $this->createForm(CommissionableAssetType::class, $asset);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($asset);
            $entityManager->flush();

            $this->addFlash('success', 'Véhicule créé');

            return $this->redirectToRoute('organization_commissionable_assets');
        }

        return $this->render('organization/commissionable_assets_add.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }
}
