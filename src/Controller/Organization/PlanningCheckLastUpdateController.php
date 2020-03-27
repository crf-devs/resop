<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Domain\PlanningUtils;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/planning/lastUpdate", name="planning_last_update", methods={"GET"})
 */
class PlanningCheckLastUpdateController
{
    private UserRepository $userRepository;
    private CommissionableAssetRepository $assetRepository;
    private UserAvailabilityRepository $userAvailabilityRepository;
    private CommissionableAssetAvailabilityRepository $assetAvailabilityRepository;
    private FormFactoryInterface $formFactory;

    public function __construct(
        UserRepository $userRepository,
        CommissionableAssetRepository $assetRepository,
        UserAvailabilityRepository $userAvailabilityRepository,
        CommissionableAssetAvailabilityRepository $assetAvailabilityRepository,
        FormFactoryInterface $formFactory
    ) {
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->assetAvailabilityRepository = $assetAvailabilityRepository;
        $this->formFactory = $formFactory;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $form = PlanningUtils::getFormFromRequest($this->formFactory, $request);
        $data = $form->getData();

        $users = $data['hideUsers'] ?? false ? [] : $this->userRepository->findByFilters($data, true);
        $assets = $data['hideAssets'] ?? false ? [] : $this->assetRepository->findByFilters($data, true);

        $userLastUpdate = $this->userAvailabilityRepository->findLastUpdatedForEntities($users);
        $assetLastUpdate = $this->assetAvailabilityRepository->findLastUpdatedForEntities($assets);

        $lastUpdate = max((new \DateTime($userLastUpdate))->format('U'), (new \DateTime($assetLastUpdate))->format('U'));

        return new JsonResponse(['lastUpdate' => (int) $lastUpdate]);
    }
}
