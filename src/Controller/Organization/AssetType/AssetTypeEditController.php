<?php

declare(strict_types=1);

namespace App\Controller\Organization\AssetType;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\AssetType;
use App\Entity\Organization;
use App\Form\Type\AssetTypeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/new", name="app_organization_assetType_new", methods={"GET", "POST"})
 * @Route("/{assetType<\d+>}/edit", name="app_organization_assetType_edit", methods={"GET", "POST"})
 * @Security("organization.isParent() and (null === assetType or is_granted('ROLE_PARENT_ORGANIZATION', assetType.organization))")
 */
class AssetTypeEditController extends AbstractOrganizationController
{
    public function __invoke(Request $request, Organization $organization, ?AssetType $assetType): Response
    {
        if (null === $assetType) {
            $assetType = new AssetType();
            $assetType->organization = $organization;
        }

        $persistedKeys = array_map(static fn (array $properties) => $properties['key'], $assetType->properties);

        $form = $this->createForm(AssetTypeType::class, $assetType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($assetType);
            $entityManager->flush();

            return $this->redirectToRoute('app_organization_assetType_list');
        }

        return $this->render('organization/assetType/edit.html.twig', [
            'persistedKeys' => $persistedKeys,
            'form' => $form->createView(),
            'organization' => $organization,
        ]);
    }
}
