<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Security\Voter\UserVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{userToEdit<\d+>}/edit", name="app_organization_user_edit", methods={"GET", "POST"})
 * @IsGranted(UserVoter::CAN_EDIT, subject="userToEdit")
 */
class UserEditController extends AbstractController
{
    public function __invoke(Request $request, User $userToEdit): Response
    {
        $form = $this
            ->createForm(UserType::class, $userToEdit, ['display_type' => UserType::DISPLAY_ORGANIZATION])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($userToEdit);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('app_organization_user_list', ['organization' => $userToEdit->getNotNullOrganization()->id]);
        }

        return $this->render('organization/user/user-edit.html.twig', [
            'user' => $userToEdit,
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
