<?php

declare(strict_types=1);

namespace App\Controller\Organization\AssetType;

use App\Entity\AssetType;
use App\Entity\Organization;
use App\Form\Type\AssetTypeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/assetType/new", name="app_organization_assetType_new", methods={"GET", "POST"})
 * @Route("/assetType/edit/{id}", name="app_organization_assetType_edit", methods={"GET", "POST"})
 */
class AssetTypeEditController extends AbstractController
{
    public function __invoke(Request $request, ?AssetType $assetType): Response
    {
        /** @var Organization $organization */
        $organization = $this->getUser();

        if (null === $assetType) {
            $assetType = new AssetType();
            $assetType->organization = $organization;
        }

        $form = $this->createForm(AssetTypeType::class, $assetType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $entityManager->persist($assetType);
            $entityManager->flush();

            return $this->redirectToRoute('app_organization_assetType_list');
        }

        return $this->render('organization/assetType/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
