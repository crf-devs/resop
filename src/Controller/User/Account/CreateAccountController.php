<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Entity\User;
use App\Form\Type\UserType;
use App\Security\UserLoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/user/new", name="user_new", methods={"GET", "POST"})
 */
final class CreateAccountController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;
    private EntityManagerInterface $entityManager;
    private GuardAuthenticatorHandler $guardHandler;
    private UserLoginFormAuthenticator $formAuthenticator;

    public function __construct(
        AuthenticationUtils $authenticationUtils,
        EntityManagerInterface $entityManager,
        GuardAuthenticatorHandler $guardHandler,
        UserLoginFormAuthenticator $formAuthenticator
    ) {
        $this->authenticationUtils = $authenticationUtils;
        $this->entityManager = $entityManager;
        $this->guardHandler = $guardHandler;
        $this->formAuthenticator = $formAuthenticator;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $this->redirectToRoute('user_home');
        }

        $user = User::bootstrap($this->authenticationUtils->getLastUsername());
        $user->setBirthday($request->getSession()->get(UserLoginFormAuthenticator::SECURITY_LAST_BIRTHDAY));

        $form = $this->createForm(UserType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre compte utilisateur a été créé avec succès.');

            return $this->guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $this->formAuthenticator,
                'main'
            );
        }

        return $this->render('user/create-account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
