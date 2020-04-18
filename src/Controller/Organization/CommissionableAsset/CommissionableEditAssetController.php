<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use App\Form\Type\CommissionableAssetType;
use App\Security\Voter\CommissionableAssetVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("{organization}/commissionable-assets/{asset}/edit", name="app_organization_commissionable_edit_asset", methods={"GET", "POST"}, requirements={"id": "\d+"})
 * @IsGranted(CommissionableAssetVoter::CAN_EDIT, subject="asset")
 * @Security("asset.organization.id == organization")
 */
class CommissionableEditAssetController extends AbstractController
{
    public function __invoke(Request $request, CommissionableAsset $asset): Response
    {
        $form = $this->createForm(CommissionableAssetType::class, $asset);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', sprintf('Véhicule "%s" mis à jour avec succès', $asset));

            return $this->redirectToRoute('app_organization_commissionable_assets', ['id' => $asset->organization->getId()]);
        }

        return $this->render(
            'organization/commissionable_asset/form.html.twig',
            [
                'form' => $form->createView(),
                'asset' => $asset,
            ]
        );
    }
}
