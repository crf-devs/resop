<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

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
 * @Route("/last-update", name="planning_last_update", methods={"GET"})
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

        // TODO Handle deleted availabities

        $availabilitiesCount = 0;
        $userLastUpdate = 0;
        $assetLastUpdate = 0;

        $userLastUpdateData = $this->userAvailabilityRepository->findLastUpdatedForEntities($users);
        if (null !== $userLastUpdateData) {
            if (null !== $userLastUpdateData['last_update']) {
                $userLastUpdate = (int) (new \DateTimeImmutable($userLastUpdateData['last_update']))->format('U');
            }
            $availabilitiesCount += (int) $userLastUpdateData['total_count'];
        }

        $assetLastUpdateData = $this->assetAvailabilityRepository->findLastUpdatedForEntities($assets);
        if (null !== $assetLastUpdateData) {
            if (null !== $assetLastUpdateData['last_update']) {
                $assetLastUpdate = (int) (new \DateTimeImmutable($assetLastUpdateData['last_update']))->format('U');
            }
            $availabilitiesCount += (int) $assetLastUpdateData['total_count'];
        }

        $lastUpdate = max($userLastUpdate, $assetLastUpdate);

        return new JsonResponse(['lastUpdate' => (int) $lastUpdate, 'totalCount' => $availabilitiesCount]);
    }
}
