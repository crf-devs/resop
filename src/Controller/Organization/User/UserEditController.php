<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\Organization;
use App\Entity\User;
use App\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/{organization}/users/{userToEdit}/edit", name="organization_user_edit", methods={"GET", "POST"})
 * @Security("userToEdit.organization.id == organization")
 */
class UserEditController extends AbstractController
{
    public function __invoke(Request $request, User $userToEdit): Response
    {
        $organization = $this->getUser();

        if (!$organization instanceof Organization) {
            throw new AccessDeniedException();
        }

        $form = $this
            ->createForm(UserType::class, $userToEdit, ['display_type' => UserType::DISPLAY_ORGANIZATION])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userToEdit);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('app_organization_user_list', ['organization' => ($userToEdit->organization ?: $organization)->id]);
        }

        return $this->render('organization/user/user-edit.html.twig', [
            'user' => $userToEdit,
            'form' => $form->createView(),
        ]);
    }
}
