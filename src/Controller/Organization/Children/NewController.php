<?php

declare(strict_types=1);

namespace App\Controller\Organization\Children;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\Organization;
use App\Form\Type\OrganizationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/new", name="app_organization_new", methods={"GET", "POST"})
 * @Security("organization.isParent()")
 */
class NewController extends AbstractOrganizationController
{
    public function __invoke(Request $request, Organization $organization): Response
    {
        $child = new Organization();
        $child->parent = $organization;

        $form = $this->createForm(OrganizationType::class, $child);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($child);
            $entityManager->flush();

            $this->addFlash('success', 'La structure a été ajoutée avec succès.');

            return $this->redirectToRoute('app_organization_list');
        }

        return $this->render(
            'organization/edit.html.twig',
            [
                'organization' => $child,
                'form' => $form->createView(),
            ]
        )->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
