<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

use App\Domain\AvailabilitiesDomain;
use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningDomain;
use App\Domain\SkillSetDomain;
use App\Entity\CommissionableAsset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="planning", methods={"GET"})
 */
class PlanningController extends AbstractController
{
    private SkillSetDomain $skillSetDomain;
    private PlanningDomain $planningDomain;

    public function __construct(
        SkillSetDomain $skillSetDomain,
        PlanningDomain $planningDomain
    ) {
        $this->skillSetDomain = $skillSetDomain;
        $this->planningDomain = $planningDomain;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->planningDomain->generateForm();
        $filters = $this->planningDomain->generateFilters($form);

        if (!isset($filters['from'], $filters['to'])) {
            // This may happen if the passed date is invalid. TODO check it before, the format must be 2020-03-30T00:00:00
            throw $this->createNotFoundException();
        }

        $periodCalculator = DatePeriodCalculator::createRoundedToDay(
            $filters['from'],
            new \DateInterval(AvailabilitiesDomain::SLOT_INTERVAL),
            $filters['to']
        );

        return $this->render('organization/planning/planning.html.twig', [
            'filters' => $filters,
            'form' => $form->createView(),
            'periodCalculator' => $periodCalculator,
            'assetsTypes' => CommissionableAsset::TYPES,
            'usersSkills' => $this->skillSetDomain->getSkillSet(),
            'importantSkills' => $this->skillSetDomain->getImportantSkills(),
            'importantSkillsToDisplay' => $this->skillSetDomain->getSkillsToDisplay(),
        ]);
    }
}
