<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Entity\User;
use App\Event\UserChangeVulnerabilityEvent;
use App\Form\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/user/edit", name="user_edit", methods={"GET", "POST"})
 */
final class EditAccountController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $originalUser = clone $user;

        $form = $this->createForm(UserType::class, $user)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if ($user->vulnerable !== $originalUser->vulnerable) {
                $this->eventDispatcher->dispatch(new UserChangeVulnerabilityEvent($user), UserChangeVulnerabilityEvent::NAME);
            }

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/account-form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'identificationNumber' => $user->getIdentificationNumber(),
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
