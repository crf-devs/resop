<?php

declare(strict_types=1);

namespace App\Controller\Organization\Forecast;

use App\Domain\MissionTypeForecastDomain;
use App\Domain\PlanningDomain;
use App\Form\Type\PlanningForecastType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/forecast", name="app_organization_forecast", methods={"GET"})
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

        return $this->render('organization/forecast/forecast.html.twig', [
            'filters' => $filters,
            'form' => $form->createView(),
            'forecast' => $this->missionTypeForecastDomain->calculatePerMissionTypes($filters),
        ]);
    }
}
