<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Domain\PlanningUpdateDomain;
use App\Entity\Organization;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/planning/update/{action}", name="planning_update", methods={"POST"})
 */
class PlanningUpdateController extends AbstractController
{
    protected UserRepository $userRepository;
    protected CommissionableAssetRepository $assetRepository;
    protected UserAvailabilityRepository $userAvailabilityRepository;
    protected CommissionableAssetAvailabilityRepository $assetAvailabilityRepository;

    public function __construct(UserRepository $userRepository, CommissionableAssetRepository $assetRepository, UserAvailabilityRepository $userAvailabilityRepository, CommissionableAssetAvailabilityRepository $assetAvailabilityRepository)
    {
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->assetAvailabilityRepository = $assetAvailabilityRepository;
    }

    public function __invoke(Request $request, string $action): Response
    {
        $organization = $this->getUser();
        if (!($organization instanceof Organization) || !empty($organization->parent)) {
            throw new AccessDeniedException('Organization is required and must not have a parent');
        }

        $json = json_decode($request->getContent(), true);
        if (!$json) {
            throw new BadRequestHttpException('Invalid JSON Payload format');
        }

        try {
            $bulkUpdate = new PlanningUpdateDomain(
                $action,
                $json,
                $organization,
                $this->getDoctrine()->getManager(),
                $this->userRepository,
                $this->assetRepository,
                $this->userAvailabilityRepository,
                $this->assetAvailabilityRepository
            );
            $bulkUpdate->compute();
        } catch (\InvalidArgumentException | \LogicException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        return new JsonResponse(['success' => true]);
    }
}
