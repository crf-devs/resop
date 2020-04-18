<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Form\Type\CommissionableAssetType;
use App\Security\Voter\OrganizationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{organization}/commissionable-assets/add", name="app_organization_commissionable_add_asset", methods={"GET", "POST"})
 * @IsGranted(OrganizationVoter::CAN_ADD_ASSET, subject="organization")
 */
class CommissionableAddAssetController extends AbstractController
{
    public function __invoke(Request $request, Organization $organization): Response
    {
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

            return $this->redirectToRoute('app_organization_commissionable_assets', ['organization' => $asset->organization->getId()]);
        }

        return $this->render(
            'organization/commissionable_asset/form.html.twig',
            [
                'organization' => $organization,
                'form' => $form->createView(),
                'asset' => $asset,
            ]
        );
    }
}
