<?php

declare(strict_types=1);

namespace App\Controller\Organization\Mission;

use App\Entity\Mission;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/missions/{id<\d+>}/modal", name="app_organization_mission_modal", methods={"GET"})
 */
class MissionModalController extends AbstractController
{
    public function __invoke(Mission $mission): Response
    {
        return $this->render('organization/mission/show-modal-content.html.twig', [
            'mission' => $mission,
        ]);
    }
}
