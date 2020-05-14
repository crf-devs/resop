<?php

declare(strict_types=1);

namespace App\Controller\User\Availability;

use App\Entity\Mission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/availability/missions/{id<\d+>}/modal", name="app_user_availability_mission_modal", methods={"GET"}, options={"expose"=true})
 * @Security("mission.users.contains(user)")
 */
class MissionModalController extends AbstractController
{
    public function __invoke(Mission $mission): Response
    {
        return $this->render('organization/mission/show-modal-content.html.twig', [
            'mission' => $mission,
            'showLinks' => false,
        ]);
    }
}
