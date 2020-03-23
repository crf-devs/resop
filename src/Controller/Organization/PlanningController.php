<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Domain\DatePeriodCalculator;
use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\User;
use App\Form\Type\PlanningSearchType;
use App\Repository\AvailabilityRepositoryInterface;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function __construct(UserRepository $userRepository, CommissionableAssetRepository $assetRepository, UserAvailabilityRepository $userAvailabilityRepository, CommissionableAssetAvailabilityRepository $assetAvailabilityRepository)
    {
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->assetAvailabilityRepository = $assetAvailabilityRepository;
    }

    public function __invoke(Request $request): Response
    {
        $form = $this->container->get('form.factory')->createNamed('', PlanningSearchType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        $from = $form->get('from')->getData();
        $to = $form->get('to')->getData();

        $periodCalculator = new DatePeriodCalculator($from, new \DateInterval('PT2H'), $to);

        if ($form->isSubmitted() && $form->isValid()) {
            [$users, $assets] = $this->searchEntities($form->getData());
            $usersAvailabilities = $this->prepareAvailabilities(User::class, $users, $periodCalculator);
            $assetsAvailabilities = $this->prepareAvailabilities(CommissionableAsset::class, $assets, $periodCalculator);
        }

        return $this->render('organization/planning.html.twig', [
            'form' => $form->createView(),
            'periodCalculator' => $periodCalculator,
            'usersAvailabilities' => $usersAvailabilities ?? [],
            'assetsAvailabilities' => $assetsAvailabilities ?? [],
        ]);
    }

    private function searchEntities(array $formData): array
    {
        $organizations = $formData['organizations'] instanceof ArrayCollection ? $formData['organizations']->toArray() : [];

        // TODO Filter by availabilty on a given date
        $users = $formData['volunteer'] ? $this->userRepository->findBySkillsAndEquippedAndVulnerableAndOrganizations(
            $formData['volunteerSkills'],
            $formData['volunteerEquipped'],
            !$formData['volunteerHideVulnerable'],
            $organizations
        ) : [];

        $assets = $formData['asset'] ? $this->assetRepository->findByTypesAndOrganizations($formData['assetTypes'], $organizations) : [];

        return [$users, $assets];
    }

    private function prepareAvailabilities(string $class, iterable $availabilitables, DatePeriodCalculator $periodCalculator): array
    {
        $availabilityRepository = $this->getAvailabilityRepository($class);
        if (!$availabilityRepository instanceof AvailabilityRepositoryInterface) {
            throw new \LogicException('Bad entity name');
        }

        $result = [];
        foreach ($availabilitables as $availabilitable) {
            $intervalAvailabilities = [];

            /** @var \DateTime $from */
            foreach ($periodCalculator->getPeriod() as $from) {
                $to = (clone $from)->add($periodCalculator->getPeriod()->interval);
                $intervalAvailability = $availabilityRepository->findOneByInterval($from, $to); // TODO use only one sql request

                $intervalAvailabilities[] = [
                    'from' => $from,
                    'to' => $to,
                    'status' => $intervalAvailability ? $intervalAvailability->getStatus() : AvailabilityInterface::STATUS_UNKNOW,
                ];
            }

            $result[] = [
                'entity' => $availabilitable,
                'availabilities' => $intervalAvailabilities,
            ];
        }

        return $result;
    }

    private function getAvailabilityRepository(string $class): ?AvailabilityRepositoryInterface
    {
        return [
                User::class => $this->userAvailabilityRepository,
                CommissionableAsset::class => $this->assetAvailabilityRepository,
            ][$class] ?? null;
    }
}
