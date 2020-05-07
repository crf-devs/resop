<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Domain\PlanningDomain;
use App\Entity\User;
use App\Form\Type\MissionsSearchType;
use App\Repository\MissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{userToAdd<\d+>}/missions/add/modal", name="app_organization_user_add_to_mission_modal", methods={"GET"})
 */
class AddToMissionModalController extends AbstractController
{
    private PlanningDomain $planningDomain;
    private MissionRepository $missionRepository;

    public function __construct(PlanningDomain $planningDomain, MissionRepository $missionRepository)
    {
        $this->planningDomain = $planningDomain;
        $this->missionRepository = $missionRepository;
    }

    public function __invoke(User $userToAdd): Response
    {
        $form = $this->planningDomain->generateForm(MissionsSearchType::class);
        $filters = $form->getData();

        return $this->render('organization/mission/add-to-mission-modal-content.html.twig', [
            'userToAdd' => $userToAdd,
            'filters' => $filters,
            'form' => $form->createView(),
            'missions' => $this->missionRepository->findByFilters($filters),
        ]);
    }
}
