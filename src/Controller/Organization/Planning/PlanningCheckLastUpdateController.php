<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

use App\Domain\PlanningDomain;
use App\Entity\Organization;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/last-update", name="app_organization_planning_last_update", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION', organization)")
 */
class PlanningCheckLastUpdateController
{
    private PlanningDomain $planningDomain;

    public function __construct(PlanningDomain $planningDomain)
    {
        $this->planningDomain = $planningDomain;
    }

    public function __invoke(Request $request, Organization $currentOrganization): JsonResponse
    {
        $form = $this->planningDomain->generateForm($currentOrganization);
        $filters = $this->planningDomain->generateFilters($form, $currentOrganization);

        return new JsonResponse($this->planningDomain->generateLastUpdateAndCount($filters));
    }
}
