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
        $data = [];
        if (!$request->query->get('from') instanceof \DateTimeImmutable) {
            $data['from'] = new \DateTimeImmutable('monday');
        }

        if (!$request->query->get('to') instanceof \DateTimeImmutable) {
            $data['to'] = (new \DateTimeImmutable('monday'))->add(new \DateInterval('P1W'));
        }

        if (0 === $request->query->count()) {
            $data = [
                'from' => new \DateTimeImmutable('monday'),
                'to' => (new \DateTimeImmutable('monday'))->add(new \DateInterval('P1W')),
                'volunteer' => true,
                'volunteerEquipped' => true,
                'volunteerHideVulnerable' => true,
                'asset' => true,
            ];
        }

        $form = $this->container->get('form.factory')->createNamed('', PlanningSearchType::class, $data, ['method' => 'GET', 'action' => $this->generateUrl('planning'), 'attr' => ['autocomplete' => 'off']]);
        $form->handleRequest($request);

        $from = $form->get('from')->getData();
        $to = $form->get('to')->getData();

        $periodCalculator = DatePeriodCalculator::createRoundedToDay($from, new \DateInterval('PT2H'), $to);

        [$users, $assets] = $this->searchEntities($form->getData());
        $usersAvailabilities = $this->prepareAvailabilities($this->userAvailabilityRepository, $users, $periodCalculator);
        $assetsAvailabilities = $this->prepareAvailabilities($this->assetAvailabilityRepository, $assets, $periodCalculator);

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
