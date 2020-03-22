<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\AvailabilitiesDomain;
use App\Entity\User;
use App\Event\UserChangeVulnerabilityEvent;
use App\Form\Type\AvailabilitiesDomainType;
use App\Form\Type\UserType;
use App\Repository\UserAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @Route("/user")
 */
final class UserController extends AbstractController
{
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
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        AuthenticationUtils $authenticationUtils
    ): Response {
        if ($currentUser = $this->getUser()) {
            $this->addFlash('error', 'Vous possédez déjà un compte');

            return $this->redirectToRoute('user_home');
        }
        $user = new User();

        $lastIdentifier = $authenticationUtils->getLastUsername();
        if ('' !== $lastIdentifier && filter_var($lastIdentifier, FILTER_VALIDATE_EMAIL)) {
            $user->setEmailAddress($lastIdentifier);
        } elseif ('' !== $lastIdentifier) {
            $user->setIdentificationNumber($lastIdentifier);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $originalUser = clone $user;

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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

    /**
     * @Route("/availability/{week<\d{4}-W\d{2}>?}", name="user_availability", methods={"GET", "POST"})
     */
    public function availability(
        Request $request,
        EntityManagerInterface $entityManager,
        UserAvailabilityRepository $userAvailabilityRepository,
        ?string $week
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            $start = new \DateTimeImmutable($week ?: 'monday this week');
        } catch (\Exception $e) {
            return $this->redirectToRoute('user_home');
        }

        $interval = $start->diff(new \DateTimeImmutable());
        // edit current week and next week only
        if ($interval->days > 6) {
            return $this->redirectToRoute('user_home');
        }

        $end = $start->add(new \DateInterval('P7D'));

        $availabilitiesDomain = AvailabilitiesDomain::generate(
            $start->format('Y-m-d H:i'),
            $end->format('Y-m-d H:i'),
            $userAvailabilityRepository->findBetweenDates($user, $start, $end)
        );

        $form = $this
            ->createForm(AvailabilitiesDomainType::class, $availabilitiesDomain)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $availabilitiesDomain->compute($entityManager, $user);
            $entityManager->flush();

            $this->addFlash('success', 'Vos disponibilités ont été mises à jour');

            return $this->redirectToRoute('user_home');
        }

        return $this->render('user/availability.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
