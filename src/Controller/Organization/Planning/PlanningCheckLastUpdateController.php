<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

use App\Domain\PlanningDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/last-update", name="app_organization_planning_last_update", methods={"GET"})
 */
class PlanningCheckLastUpdateController
{
    private PlanningDomain $planningDomain;

    public function __construct(PlanningDomain $planningDomain)
    {
        $this->planningDomain = $planningDomain;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $form = $this->planningDomain->generateForm();
        $filters = $this->planningDomain->generateFilters($form);

        return new JsonResponse($this->planningDomain->generateLastUpdateAndCount($filters));
    }
}
