<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Form\Type\CommissionableAssetType;
use App\Repository\CommissionableAssetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CommissionableAssetsController extends AbstractController
{
    private CommissionableAssetRepository $assetRepository;

    public function __construct(CommissionableAssetRepository $assetRepository)
    {
        $this->assetRepository = $assetRepository;
    }

    /**
     * @Route("/", name="app_organization_commissionable_assets", methods={"GET"})
     */
    public function assets(): Response
    {
        $organization = $this->getUser();
        if (!$organization instanceof Organization) {
            throw new AccessDeniedException();
        }

        return $this->render('organization/commissionable_asset/list.html.twig', [
            'assets' => $this->assetRepository->findByOrganization($organization),
        ]);
    }

    /**
     * @Route("add", name="app_organization_commissionable_add_asset", methods={"GET", "POST"})
     */
    public function addAsset(Request $request): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();
        $asset = new CommissionableAsset();
        $asset->organization = $organization;
        $asset->type = 'VL';

        $form = $this->createForm(CommissionableAssetType::class, $asset);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($asset);
            $entityManager->flush();

            $this->addFlash('success', 'Véhicule créé');

            return $this->redirectToRoute('app_organization_commissionable_assets');
        }

        return $this->render('organization/commissionable_asset/form.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
            'asset' => $asset,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_organization_commissionable_edit_asset", methods={"GET", "POST"}, requirements={"id": "\d+"})
     */
    public function editAsset(Request $request, CommissionableAsset $asset): Response
    {
        $form = $this->createForm(CommissionableAssetType::class, $asset);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', sprintf('Véhicule "%s" mis à jour avec succès', $asset));

            return $this->redirectToRoute('app_organization_commissionable_assets');
        }

        return $this->render('organization/commissionable_asset/form.html.twig', [
            'form' => $form->createView(),
            'asset' => $asset,
        ]);
    }
}
