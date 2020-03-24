<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Domain\DatePeriodCalculator;
use App\Entity\AvailabilitableInterface;
use App\Entity\AvailabilityInterface;
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
        $data = [
            'from' => new \DateTimeImmutable('monday this week 00:00:00'),
            'to' => new \DateTimeImmutable('sunday this week 23:59:59'),
            'volunteer' => true,
            'volunteerEquipped' => true,
            'volunteerHideVulnerable' => true,
            'asset' => true,
        ];

        if ($request->query->has('preselect')) {
            $data['organizations'] = new ArrayCollection();
            $data['organizations']->add($this->getUser());

            if (2 === $request->query->getInt('preselect')) {
                $data['from'] = new \DateTimeImmutable('monday next week 00:00:00');
                $data['to'] = new \DateTimeImmutable('sunday next week 23:59:59');
            }
        }

        $form = $this->container->get('form.factory')->createNamed('', PlanningSearchType::class, $data, ['method' => 'GET']);
        $form->handleRequest($request);

        $periodCalculator = DatePeriodCalculator::createRoundedToDay(
            $form->get('from')->getData(),
            new \DateInterval('PT2H'),
            $form->get('to')->getData()
        );

        if ($form->isSubmitted() && $form->isValid()) {
            [$users, $assets] = $this->searchEntities($form->getData());
            $usersAvailabilities = $this->prepareAvailabilities($this->userAvailabilityRepository, $users, $periodCalculator);
            $assetsAvailabilities = $this->prepareAvailabilities($this->assetAvailabilityRepository, $assets, $periodCalculator);
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
        $users = $this->userRepository->findByFilters($formData);
        $assets = $this->assetRepository->findByFilters($formData);

        return [$users, $assets];
    }

    /**
     * @param AvailabilitableInterface[] $availabilitables
     */
    private function prepareAvailabilities(AvailabilityRepositoryInterface $repository, array $availabilitables, DatePeriodCalculator $periodCalculator): array
    {
        $slots = self::parseRawSlots($repository->loadRawDataForEntity($availabilitables, $periodCalculator->getFrom(), $periodCalculator->getTo()));

        $result = [];
        foreach ($availabilitables as $availabilitable) {
            $intervalAvailabilities = [];

            /** @var \DateTime $from */
            foreach ($periodCalculator->getPeriod() as $from) {
                $to = (clone $from)->add($periodCalculator->getPeriod()->interval);
                $existingSlot = $slots[$availabilitable->getId()][$from->format('Y-m-d H:i')] ?? [];
                // TODO Check the end time, just in case
                $intervalAvailabilities[] = [
                    'from' => $from,
                    'to' => $to,
                    'status' => $existingSlot['status'] ?? AvailabilityInterface::STATUS_UNKNOW,
                ];
            }

            $result[] = [
                'entity' => $availabilitable,
                'availabilities' => $intervalAvailabilities,
            ];
        }

        return $result;
    }

    private static function parseRawSlots(array $rawSlots): array
    {
        $slots = [];
        foreach ($rawSlots as $slot) {
            $slotStart = $slot['startTime'] ?? null;
            if (!$slotStart instanceof \DateTimeInterface) {
                continue;
            }
            $slots[$slot['user_id'] ?? $slot['asset_id'] ?? 0][$slotStart->format('Y-m-d H:i')] = $slot;
        }

        return $slots;
    }
}
