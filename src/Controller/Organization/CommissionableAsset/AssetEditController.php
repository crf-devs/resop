<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\CommissionableAsset;
use App\Form\Type\CommissionableAssetType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{asset<\d+>}/edit", name="app_organization_asset_edit", methods={"GET", "POST"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', asset.organization)")
 */
class AssetEditController extends AbstractOrganizationController
{
    public function __invoke(Request $request, CommissionableAsset $asset): Response
    {
        $form = $this->createForm(CommissionableAssetType::class, $asset);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            $this->addFlash('success', sprintf('Véhicule "%s" mis à jour avec succès', $asset));

            return $this->redirectToRoute('app_organization_assets', ['organization' => $asset->organization->getId()]);
        }

        return $this->render(
            'organization/commissionable_asset/form.html.twig',
            [
                'form' => $form->createView(),
                'asset' => $asset,
            ]
        )->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
