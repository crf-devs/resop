<?php

declare(strict_types=1);

namespace App\Controller\Organization\Forecast;

use App\Domain\MissionTypeForecastDomain;
use App\Domain\PlanningDomain;
use App\Entity\Organization;
use App\Form\Type\PlanningForecastType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(name="app_organization_forecast", methods={"GET"})
 * @Security("is_granted('ROLE_PARENT_ORGANIZATION')")
 */
class PlanningForecastController extends AbstractController
{
    private PlanningDomain $planningDomain;
    private MissionTypeForecastDomain $missionTypeForecastDomain;

    public function __construct(PlanningDomain $planningDomain, MissionTypeForecastDomain $missionTypeForecastDomain)
    {
        $this->planningDomain = $planningDomain;
        $this->missionTypeForecastDomain = $missionTypeForecastDomain;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->planningDomain->generateForm(PlanningForecastType::class);
        $filters = $this->planningDomain->generateFilters($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $planningUrlOptions = array_filter(
                [
                    'from' => $filters['availableFrom']->modify('-1 day')->format('Y-m-d\\T00:00:00'),
                    'to' => $filters['availableTo']->modify('+1 day')->format('Y-m-d\\T00:00:00'),
                    'availableFrom' => $filters['availableFrom']->format('Y-m-d\\T00:00:00'),
                    'availableTo' => $filters['availableTo']->format('Y-m-d\\T00:00:00'),
                    'organizations' => array_map(fn (Organization $organization) => $organization->id, $filters['organizations']),
                    'displayAvailableWithBooked' => $filters['displayAvailableWithBooked'],
                    'userPropertyFilters' => array_filter($filters['userPropertyFilters'] ?? [], fn ($val) => null !== $val && '' !== $val),
                ],
                fn ($val) => null !== $val && '' !== $val
            );
        }

        return $this->render(
            'organization/forecast/forecast.html.twig',
            [
                'filters' => $filters,
                'form' => $form->createView(),
                'forecast' => $this->missionTypeForecastDomain->calculatePerMissionTypes($filters),
                'planningUrlOptions' => $planningUrlOptions ?? [],
            ]
        );
    }
}
