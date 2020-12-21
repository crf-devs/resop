<?php

declare(strict_types=1);

namespace App\Controller\Organization\User;

use App\Domain\PlanningDomain;
use App\Entity\Organization;
use App\Entity\User;
use App\Form\Type\MissionsSearchType;
use App\Repository\MissionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{item<\d+>}/missions/add/modal", name="app_organization_user_add_to_mission_modal", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', item.organization)")
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

    public function __invoke(User $item, Organization $organization): Response
    {
        $form = $this->planningDomain->generateForm($organization, MissionsSearchType::class);
        $filters = $form->getData();

        return $this->render('organization/mission/add-to-mission-modal-content.html.twig', [
            'userToAdd' => $item,
            'filters' => $filters,
            'form' => $form->createView(),
            'missions' => $this->missionRepository->findByFilters($filters),
        ]);
    }
}
