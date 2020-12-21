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
 * @Route("/{organizationToEdit<\d+>}/edit", name="app_organization_edit", methods={"GET", "POST"})
 * @Security("organization.isParent() and !organizationToEdit.isParent() and is_granted('ROLE_PARENT_ORGANIZATION', organizationToEdit)")
 */
class EditController extends AbstractOrganizationController
{
    public function __invoke(Request $request, Organization $organizationToEdit): Response
    {
        $form = $this->createForm(OrganizationType::class, $organizationToEdit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $flashMessage = 'La structure a été mise à jour avec succès.';

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($organizationToEdit);
            $entityManager->flush();

            $this->addFlash('success', $flashMessage);

            return $this->redirectToRoute('app_organization_list');
        }

        return $this->render(
            'organization/edit.html.twig',
            [
                'organization' => $organizationToEdit,
                'form' => $form->createView(),
            ]
        )->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
