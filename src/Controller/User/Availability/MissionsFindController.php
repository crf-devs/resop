<?php

declare(strict_types=1);

namespace App\Controller\User\Availability;

use App\Entity\User;
use App\Repository\MissionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/user/availability/missions", name="app_user_availability_missions", methods={"GET"})
 * @Route("/user/availability/{week<\d{4}-W\d{2}>?}/missions", name="app_user_availability_missions_week", methods={"GET"})
 */
class MissionsFindController extends AbstractController
{
    use UserAvailabityControllerTrait;

    private MissionRepository $missionRepository;
    private SerializerInterface $serializer;

    public function __construct(MissionRepository $missionRepository, SerializerInterface $serializer)
    {
        $this->missionRepository = $missionRepository;
        $this->serializer = $serializer;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        [$start, $end] = $this->getDatesByWeek($request->attributes->get('week'));

        $data = $this->missionRepository->findByPlanningFilters(['from' => $start, 'to' => $end], [[(int) $user->getId()], []]);

        return new JsonResponse($this->serializer->serialize($data, 'json', ['groups' => ['mission:ajax']]), 200, [], true);
    }
}
