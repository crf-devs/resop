<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Form\Type\CommissionableAssetType;
use App\Repository\AssetTypeRepository;
use App\Security\Voter\OrganizationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\InvalidParameterException;

/**
 * @Route("/add", name="app_organization_asset_add", methods={"GET", "POST"})
 * @IsGranted(OrganizationVoter::CAN_MANAGE, subject="organization")
 */
class AssetAddController extends AbstractController
{
    private AssetTypeRepository $assetTypeRepository;

    public function __construct(AssetTypeRepository $assetTypeRepository)
    {
        $this->assetTypeRepository = $assetTypeRepository;
    }

    public function __invoke(Request $request, Organization $organization): Response
    {
        $assetType = null;
        if ($request->query->has('type')) {
            $assetType = $this->assetTypeRepository->findByOrganizationAndId($organization, (int) $request->query->get('type'));
        }

        if (null === $assetType) {
            throw new InvalidParameterException('Invalid type');
        }

        $asset = new CommissionableAsset();
        $asset->organization = $organization;
        $asset->assetType = $assetType;

        $form = $this->createForm(CommissionableAssetType::class, $asset);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($asset);
            $entityManager->flush();

            $this->addFlash('success', 'Véhicule créé');

            return $this->redirectToRoute('app_organization_assets', ['organization' => $asset->organization->getId()]);
        }

        return $this->render(
            'organization/commissionable_asset/form.html.twig',
            [
                'organization' => $organization,
                'form' => $form->createView(),
                'asset' => $asset,
            ]
        )->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
