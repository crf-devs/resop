<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Event\UserChangeVulnerabilityEvent;
use App\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    private AuthenticationUtils $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @Route("/home", name="user_home", methods={"GET", "POST"})
     */
    public function home(Request $request): Response
    {
        return $this->render('user/home.html.twig');
    }

    /**
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        if ($currentUser = $this->getUser()) {
            $this->addFlash('error', 'Vous possédez déjà un compte');

            return $this->redirectToRoute('user_home');
        }
        $user = new User();

        $lastIdentifier = $this->authenticationUtils->getLastUsername();
        if ('' !== $lastIdentifier && filter_var($lastIdentifier, FILTER_VALIDATE_EMAIL)) {
            $user->setEmailAddress($lastIdentifier);
        } elseif ('' !== $lastIdentifier) {
            $user->setIdentificationNumber($lastIdentifier);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Informations crées');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $originalUser = clone $user;

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            if ($user->vulnerable !== $originalUser->vulnerable) {
                $eventDispatcher->dispatch(new UserChangeVulnerabilityEvent($user), UserChangeVulnerabilityEvent::NAME);
            }

            $this->addFlash('success', 'Informations mises à jour');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView(),
            'identificationNumber' => $user->getIdentificationNumber(),
        ]);
    }
}
