<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Repository\UserRepository;
use App\Security\UserAutomaticLoginHandler;
use App\Security\UserLoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/user/new", name="app_user_create", methods={"GET", "POST"})
 */
final class CreateAccountController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private EntityManagerInterface $entityManager;
    private UserAutomaticLoginHandler $automaticLoginHandler;
    private UserRepository $userRepository;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        EntityManagerInterface $entityManager,
        UserAutomaticLoginHandler $automaticLoginHandler,
        UserRepository $userRepository
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->entityManager = $entityManager;
        $this->automaticLoginHandler = $automaticLoginHandler;
        $this->userRepository = $userRepository;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $this->redirectToRoute('app_user_home');
        }

        $user = User::bootstrap($this->authenticationUtils->getLastUsername());
        $bdate = $request->getSession()->get(UserLoginFormAuthenticator::SECURITY_LAST_BIRTHDAY);
        if (!empty($bdate)) {
            $request->getSession()->remove(UserLoginFormAuthenticator::SECURITY_LAST_BIRTHDAY);
            $user->birthday = $bdate;
        }

        $form = $this->createForm(UserType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userRepository->addUserRoles($user);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre compte utilisateur a été créé avec succès.');

            return $this->automaticLoginHandler->handleAuthentication($request, $user);
        }

        return $this->render('user/account-form.html.twig', [
            'form' => $form->createView(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
