<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization;
use App\Entity\User;
use App\Event\UserChangeVulnerabilityEvent;
use App\Form\Type\OrganizationUserType;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/edit/{id}", name="organization_user_edit", methods={"GET", "POST"})
 */
class UserEditController extends AbstractController
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;;
    }

    public function __invoke(Request $request, User $user): Response
    {
        $organization = $this->getUser();

        if (!$organization instanceof Organization) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(UserType::class, $user, ['display_type' => UserType::DISPLAY_ORGANIZATION])->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Les informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('organization_user_list');
        }

        return $this->render('organization/user-edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
