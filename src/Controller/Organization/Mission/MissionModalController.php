<?php

declare(strict_types=1);

namespace App\Controller\Organization\Mission;

use App\Entity\Mission;
use App\Entity\Organization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{mission<\d+>}/modal", name="app_organization_mission_modal", methods={"GET"}, options={"expose"=true})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', organization)")
 */
class MissionModalController extends AbstractController
{
    public function __invoke(Organization $organization, Mission $mission): Response
    {
        return $this->render('organization/mission/show-modal-content.html.twig', [
            'mission' => $mission,
            'organization' => $organization,
        ]);
    }
}
