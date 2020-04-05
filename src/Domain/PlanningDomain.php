<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\Organization;
use App\Entity\User;
use App\Form\Type\PlanningSearchType;
use App\Repository\AvailabilityRepositoryInterface;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\OrganizationRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class PlanningDomain
{
    private UserRepository $userRepository;
    private CommissionableAssetRepository $assetRepository;
    private UserAvailabilityRepository $userAvailabilityRepository;
    private CommissionableAssetAvailabilityRepository $assetAvailabilityRepository;
    private OrganizationRepository $organizationRepository;
    private FormFactoryInterface $formFactory;
    private RequestStack $requestStack;
    private Security $security;
    private SkillSetDomain $skillSetDomain;

    public function __construct(
        UserRepository $userRepository,
        CommissionableAssetRepository $assetRepository,
        UserAvailabilityRepository $userAvailabilityRepository,
        CommissionableAssetAvailabilityRepository $assetAvailabilityRepository,
        OrganizationRepository $organizationRepository,
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        Security $security,
        SkillSetDomain $skillSetDomain
    ) {
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->assetAvailabilityRepository = $assetAvailabilityRepository;
        $this->organizationRepository = $organizationRepository;
        $this->formFactory = $formFactory;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->skillSetDomain = $skillSetDomain;
    }

    public function generateForm(): FormInterface
    {
        $organization = $this->security->getUser();
        $request = $this->requestStack->getCurrentRequest();

        if (!$organization instanceof Organization) {
            throw new \LogicException();
        }

        $form = $this->formFactory->createNamed('', PlanningSearchType::class, ['organization' => $organization], ['method' => 'GET', 'attr' => ['autocomplete' => 'off']]);
        $form->handleRequest($request);

        return $form;
    }

    public function generateFilters(FormInterface $form): array
    {
        $organization = $this->security->getUser();
        if (!$organization instanceof Organization) {
            throw new \LogicException('Bad user type');
        }

        $filters = $form->getData();
        if (!\array_key_exists('organizations', $filters) || !(\count($filters['organizations']) > 0)) {
            $filters['organizations'] = $this->organizationRepository->findByParent($organization);
        }

        return $filters;
    }

    public function generateAvailabilities(array $filters, \DatePeriod $datePeriod): array
    {
        $users = $filters['hideUsers'] ?? false ? [] : $this->userRepository->findByFilters($filters, false);
        $assets = $filters['hideAssets'] ?? false ? [] : $this->assetRepository->findByFilters($filters, false);

        return $this->splitAvailabilities(
            $this->prepareAvailabilities($this->userAvailabilityRepository, $users, $datePeriod),
            $this->prepareAvailabilities($this->assetAvailabilityRepository, $assets, $datePeriod)
        );
    }

    public function generateLastUpdateAndCount(array $filters): array
    {
        $users = $filters['hideUsers'] ?? false ? [] : $this->userRepository->findByFilters($filters, true);
        $assets = $filters['hideAssets'] ?? false ? [] : $this->assetRepository->findByFilters($filters, true);

        // TODO Handle deleted availabilities

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

        return ['lastUpdate' => (int) $lastUpdate, 'totalCount' => $availabilitiesCount];
    }

    protected function prepareAvailabilities(AvailabilityRepositoryInterface $repository, array $availabilitables, \DatePeriod $datePeriod): array
    {
        $slots = $this->parseRawSlots($repository->loadRawDataForEntity($availabilitables, $datePeriod->getStartDate(), $datePeriod->getEndDate()));

        $result = [];
        foreach ($availabilitables as $availabilitable) {
            $intervalAvailabilities = [];

            /** @var \DateTime $from */
            foreach ($datePeriod as $from) {
                $to = (clone $from)->add($datePeriod->getDateInterval());
                $existingSlot = $slots[$availabilitable->getId()][$from->format('Y-m-d H:i')] ?? [];
                // TODO Check the end time, just in case
                $intervalAvailabilities[] = [
                    'from' => $from,
                    'to' => $to,
                    'status' => $existingSlot['status'] ?? AvailabilityInterface::STATUS_UNKNOW,
                    // We format the date here in order to avoid many twig date filter call
                    'fromDay' => $from->format('Y-m-d'),
                    'fromDate' => $from->format('Y-m-d H:i'),
                    'toDate' => $to->format('Y-m-d H:i'),
                ];
            }

            $result[] = [
                'entity' => $availabilitable,
                'availabilities' => $intervalAvailabilities,
            ];
        }

        return $result;
    }

    protected function parseRawSlots(array $rawSlots): array
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

    protected function splitAvailabilities(array $usersAvailabilities, array $assetsAvailabilities): array
    {
        $result = []; // Ordered associative array

        // Assets
        foreach (CommissionableAsset::getTypesKeys() as $type) {
            $result[$type] = [];
        }

        /** @var CommissionableAsset[] $item */
        foreach ($assetsAvailabilities as $item) {
            $result[$item['entity']->type][] = $item;
        }

        // Users
        $importantSkills = $this->skillSetDomain->getImportantSkills();

        foreach ($importantSkills as $skill) {
            $result[$skill] = []; // Ordered associative array
        }

        /** @var User[] $item */
        foreach ($usersAvailabilities as $item) {
            foreach ($importantSkills as $skill) {
                if (\in_array($skill, $item['entity']->skillSet, true)) {
                    $result[$skill][] = $item;
                    continue 2;
                }
            }
            $result['others'][] = $item;
        }

        return array_filter($result);
    }
}
