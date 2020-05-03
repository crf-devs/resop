<?php

declare(strict_types=1);

namespace App\Controller\User\Availability;

use App\Domain\AvailabilitiesDomain;
use App\Entity\User;
use App\Entity\UserAvailability;
use App\Form\Type\AvailabilitiesDomainType;
use App\Repository\UserAvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/availability/{week<\d{4}-W\d{2}>?}", name="user_availability", methods={"GET", "POST"})
 */
final class ManageAvailabilityController extends AbstractController
{
    use UserAvailabityControllerTrait;

    private EntityManagerInterface $entityManager;
    private UserAvailabilityRepository $userAvailabilityRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserAvailabilityRepository $userAvailabilityRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
    }

    public function __invoke(Request $request, string $slotInterval): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        [$start, $end] = $this->getDatesByWeek($request->attributes->get('week'));

        $blockedSlotsInterval = new \DateInterval('PT12H');
        $availabilitiesDomain = AvailabilitiesDomain::generate(
            $start,
            $end,
            $slotInterval,
            $this->userAvailabilityRepository->findBetweenDates($user, $start, $end),
            $blockedSlotsInterval
        );

        $form = $this
            ->createForm(AvailabilitiesDomainType::class, $availabilitiesDomain)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $availabilitiesDomain->compute($this->entityManager, UserAvailability::class, $user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Vos disponibilités ont été mises à jour avec succès.');

            return $this->redirectToRoute('app_user_home');
        }

        return $this->render('user/availability.html.twig', [
            'form' => $form->createView(),
            'blockedSlotsInterval' => $blockedSlotsInterval,
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK);
    }
}
