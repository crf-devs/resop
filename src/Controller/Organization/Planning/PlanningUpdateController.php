<?php

declare(strict_types=1);

namespace App\Controller\Organization\Planning;

use App\Domain\PlanningUpdateDomain;
use App\Entity\Organization;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/update/{action}", name="app_organization_planning_update", methods={"POST"})
 * @Security("organization.isParent()")
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

    public function __invoke(Request $request, Organization $organization, string $action): JsonResponse
    {
        try {
            $json = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid JSON Payload format', $e);
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
        } catch (\Exception $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return new JsonResponse(['success' => true]);
    }
}
