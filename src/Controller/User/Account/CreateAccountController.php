<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Entity\User;
use App\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/user/new", name="user_new", methods={"GET", "POST"})
 */
final class CreateAccountController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        EntityManagerInterface $entityManager
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->entityManager = $entityManager;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $this->redirectToRoute('user_home');
        }

        $user = User::bootstrap($this->authenticationUtils->getLastUsername());

        $form = $this->createForm(UserType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre compte utilisateur a été créé avec succès.');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/create-account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
