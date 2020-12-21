<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Controller\Organization\AbstractOrganizationController;
use App\Entity\User;
use App\Form\Type\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{item<\d+>}/edit", name="app_organization_user_edit", methods={"GET", "POST"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', item.organization)")
 */
class UserEditController extends AbstractOrganizationController
{
    public function __invoke(Request $request, User $item): Response
    {
        $form = $this
            ->createForm(UserType::class, $item, ['display_type' => UserType::DISPLAY_ORGANIZATION])
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($item);
            $entityManager->flush();

            $this->addFlash('success', 'Les informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('app_organization_user_list', ['organization' => $item->getNotNullOrganization()->id]);
        }

        return $this->render('organization/user/edit.html.twig', [
            'user' => $item,
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
