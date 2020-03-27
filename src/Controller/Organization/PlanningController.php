<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Domain\AvailabilitiesDomain;
use App\Domain\DatePeriodCalculator;
use App\Domain\PlanningUtils;
use App\Domain\SkillSetDomain;
use App\Entity\CommissionableAsset;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/planning", name="planning", methods={"GET"})
 */
class PlanningController extends AbstractController
{
    private UserRepository $userRepository;
    private CommissionableAssetRepository $assetRepository;
    private UserAvailabilityRepository $userAvailabilityRepository;
    private CommissionableAssetAvailabilityRepository $assetAvailabilityRepository;
    private SkillSetDomain $skillSetDomain;

    public function __construct(
        UserRepository $userRepository,
        CommissionableAssetRepository $assetRepository,
        UserAvailabilityRepository $userAvailabilityRepository,
        CommissionableAssetAvailabilityRepository $assetAvailabilityRepository,
        SkillSetDomain $skillSetDomain
    ) {
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->assetAvailabilityRepository = $assetAvailabilityRepository;
        $this->skillSetDomain = $skillSetDomain;
    }

    public function __invoke(Request $request): Response
    {
        $form = PlanningUtils::getFormFromRequest($this->container->get('form.factory'), $request);
        $data = $form->getData();

        if (!isset($data['from'], $data['to'])) {
            // This may happen if the passed date is invalid. TODO check it before, the format must be 2020-03-30T00:00:00
            throw $this->createNotFoundException();
        }

        $periodCalculator = DatePeriodCalculator::createRoundedToDay($data['from'], new \DateInterval(AvailabilitiesDomain::SLOT_INTERVAL), $data['to']);

        $users = $data['hideUsers'] ?? false ? [] : $this->userRepository->findByFilters($data);
        $assets = $data['hideAssets'] ?? false ? [] : $this->assetRepository->findByFilters($data);
        $usersAvailabilities = PlanningUtils::prepareAvailabilities($this->userAvailabilityRepository, $users, $periodCalculator);
        $assetsAvailabilities = PlanningUtils::prepareAvailabilities($this->assetAvailabilityRepository, $assets, $periodCalculator);

        return $this->render('organization/planning/planning.html.twig', [
            'form' => $form->createView(),
            'periodCalculator' => $periodCalculator,
            'availabilities' => PlanningUtils::splitAvailabilities($this->skillSetDomain, $usersAvailabilities, $assetsAvailabilities),
            'assetsTypes' => CommissionableAsset::TYPES,
            'usersSkills' => $this->skillSetDomain->getSkillSet(),
            'importantSkills' => $this->skillSetDomain->getImportantSkills(),
            'importantSkillsToDisplay' => $this->skillSetDomain->getSkillsToDisplay(),
        ]);
    }
}
