<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Form\Type\OrganizationType;
use App\Security\Voter\OrganizationVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/new", name="app_organization_new", methods={"GET", "POST"})
 * @IsGranted(OrganizationVoter::CAN_CREATE)
 */
class OrganizationNewController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        /** @var Organization $currentOrganization */
        $currentOrganization = $this->getUser();

        $organization = new Organization();
        $organization->parent = $currentOrganization;

        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($organization);
            $entityManager->flush();

            $this->addFlash('success', 'La structure a été ajoutée avec succès.');

            return $this->redirectToRoute('app_organization_list');
        }

        return $this->render(
            'organization/edit.html.twig',
            [
                'organization' => $organization,
                'form' => $form->createView(),
            ]
        )->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
