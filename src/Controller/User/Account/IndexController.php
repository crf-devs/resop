<?php

declare(strict_types=1);

namespace App\Controller\User\Account;

use App\Domain\AvailabilitiesHelper;
use App\Entity\User;
use App\Repository\MissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="app_user_home", methods={"GET", "POST"})
 */
final class IndexController extends AbstractController
{
    private AvailabilitiesHelper $availabilitiesHelper;
    private MissionRepository $missionRepository;

    public function __construct(AvailabilitiesHelper $availabilitiesHelper, MissionRepository $missionRepository)
    {
        $this->availabilitiesHelper = $availabilitiesHelper;
        $this->missionRepository = $missionRepository;
    }

    public function __invoke(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $monday = new \DateTimeImmutable('monday this week', new \DateTimeZone('Europe/Paris'));
        $mondayNextWeek = new \DateTimeImmutable('monday next week', new \DateTimeZone('Europe/Paris'));

        $currentWeekAvailabilities = $this->availabilitiesHelper->getUserWeeklyAvailabilities($user, $monday);
        $nextWeekAvailabilities = $this->availabilitiesHelper->getUserWeeklyAvailabilities($user, $mondayNextWeek);

        $currentWeekMissions = $this->missionRepository->findByPlanningFilters(['from' => $monday, 'to' => $monday->add(new \DateInterval('P7D'))], [[(int) $user->getId()], []]);
        $nextWeekMissions = $this->missionRepository->findByPlanningFilters(['from' => $mondayNextWeek, 'to' => $mondayNextWeek->add(new \DateInterval('P7D'))], [[(int) $user->getId()], []]);

        return $this->render('user/index.html.twig', [
            'currentWeekAvailabilities' => $currentWeekAvailabilities,
            'nextWeekAvailabilities' => $nextWeekAvailabilities,
            'currentWeekMissions' => $currentWeekMissions,
            'nextWeekMissions' => $nextWeekMissions,
        ]);
    }
}
