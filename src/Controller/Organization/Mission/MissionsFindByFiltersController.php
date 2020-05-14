<?php

declare(strict_types=1);

namespace App\Controller\Organization\Mission;

use App\Domain\PlanningDomain;
use App\Repository\MissionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/find", name="app_organization_mission_find_by_filters", methods={"GET"}, options={"expose"=true})
 */
class MissionsFindByFiltersController
{
    private PlanningDomain $planningDomain;
    private MissionRepository $missionRepository;
    private SerializerInterface $serializer;

    public function __construct(PlanningDomain $planningDomain, MissionRepository $missionRepository, SerializerInterface $serializer)
    {
        $this->planningDomain = $planningDomain;
        $this->missionRepository = $missionRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(): JsonResponse
    {
        $form = $this->planningDomain->generateForm();
        $filters = $this->planningDomain->generateFilters($form);

        $data = $this->missionRepository->findByPlanningFilters($filters, $this->planningDomain->getAvailableResources($filters, true));

        // TODO Paginate
        return new JsonResponse($this->serializer->serialize($data, 'json', ['groups' => ['mission:ajax']]), 200, [], true);
    }
}
