<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Form\Type\OrganizationType;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/new", name="app_organization_new", methods={"GET", "POST"})
 * @Route("/edit/{id}", name="app_organization_edit", methods={"GET", "POST"})
 */
class OrganizationEditController extends AbstractController
{
    protected FormFactoryInterface $formFactory;
    protected OrganizationRepository $organizationRepository;

    public function __construct(FormFactoryInterface $formFactory, OrganizationRepository $organizationRepository)
    {
        $this->formFactory = $formFactory;
        $this->organizationRepository = $organizationRepository;
    }

    public function __invoke(Request $request, ?Organization $organization = null): Response
    {
        $currentOrganization = $this->getUser();
        if (!($currentOrganization instanceof Organization) || null !== $currentOrganization->parent) {
            throw new AccessDeniedException();
        }

        if (null === $organization) {
            $organization = new Organization(null, '', $currentOrganization);
        }

        $form = $this->formFactory->create(OrganizationType::class, $organization)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $flashMessage = 'La structure a été mise à jour avec succès.';
            if (null === $organization->id) {
                $flashMessage = 'La structure a été ajoutée avec succès.';
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($organization);
            $entityManager->flush();

            $this->addFlash('success', $flashMessage);

            return $this->redirectToRoute('app_organization_list');
        }

        return $this->render('organization/edit.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }
}
