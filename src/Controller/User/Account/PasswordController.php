<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Entity\User;
use App\Form\Type\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user/password", name="app_user_password", methods={"GET", "POST"})
 */
final class PasswordController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $encoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(UserPasswordType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $user->plainPassword;
            $user->setPassword($this->encoder->encodePassword($user, $plainPassword));
            $user->eraseCredentials();

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');

            return $this->redirectToRoute('app_user_home');
        }

        return $this->render('user/password-form.html.twig', [
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
