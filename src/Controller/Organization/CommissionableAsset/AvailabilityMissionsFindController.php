<?php

declare(strict_types=1);

namespace App\Controller\Organization\CommissionableAsset;

use App\Controller\User\Availability\UserAvailabityControllerTrait;
use App\Entity\CommissionableAsset;
use App\Repository\MissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/{asset<\d+>}/availability/missions", name="app_organization_asset_availability_missions", methods={"GET"})
 * @Route("/{asset<\d+>}/availability/{week<\d{4}-W\d{2}>?}/missions", name="app_organization_asset_availability_missions_week", methods={"GET"})
 */
class AvailabilityMissionsFindController extends AbstractController
{
    use UserAvailabityControllerTrait;

    private MissionRepository $missionRepository;
    private SerializerInterface $serializer;

    public function __construct(MissionRepository $missionRepository, SerializerInterface $serializer)
    {
        $this->missionRepository = $missionRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request, CommissionableAsset $asset): JsonResponse
    {
        [$start, $end] = $this->getDatesByWeek($request->attributes->get('week'));

        $data = $this->missionRepository->findByPlanningFilters(['from' => $start, 'to' => $end], [[], [(int) $asset->getId()]]);

        return new JsonResponse($this->serializer->serialize($data, 'json', ['groups' => ['mission:ajax']]), 200, [], true);
    }
}
