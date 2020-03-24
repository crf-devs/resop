<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilitableInterface;
use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\CommissionableAssetAvailability;
use App\Entity\Organization;
use App\Entity\User;
use App\Entity\UserAvailability;
use App\Repository\AvailabilitableRepositoryInterface;
use App\Repository\AvailabilityRepositoryInterface;
use App\Repository\CommissionableAssetAvailabilityRepository;
use App\Repository\CommissionableAssetRepository;
use App\Repository\UserAvailabilityRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ObjectManager;

// Payload example
// {
//     "users": {
//         "1": [
//             ["2020-03-20 12:00", "2020-03-20 14:00"]
//         ],
//         "2": [
//             ["2020-03-21 12:00", "2020-03-20 14:00"]
//         ]
//     },
//     "commissionableAssets": {
//         "1": [
//             ["2020-03-24 12:00", "2020-03-20 14:00"]
//         ],
//         "2": [
//             ["2020-03-22 12:00", "2020-03-20 14:00"]
//         ]
//     }
// }
class PlanningUpdateDomain
{
    public const ACTION_BOOK = 'book'; // blue button
    public const ACTION_LOCK = 'lock'; // black button
    public const ACTION_ALLOW = 'allow'; // green button
    public const ACTION_DELETE = 'delete'; // white button

    public const ACTIONS = [
        self::ACTION_BOOK,
        self::ACTION_LOCK,
        self::ACTION_ALLOW,
        self::ACTION_DELETE,
    ];

    public const PAYLOAD_USERS_KEY = 'users';
    public const PAYLOAD_COMMISSIONABLE_ASSETS_KEY = 'assets';

    public const PAYLOAD_VALID_KEYS = [
        self::PAYLOAD_USERS_KEY,
        self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY,
    ];

    protected ObjectManager $om;
    protected string $action;
    protected array $payload;
    protected Organization $planningAgent;
    protected UserRepository $userRepository;
    protected CommissionableAssetRepository $assetRepository;
    protected UserAvailabilityRepository $userAvailabilityRepository;
    protected CommissionableAssetAvailabilityRepository $assetAvailabilityRepository;

    public function __construct(string $action, array $payload, Organization $planningAgent, ObjectManager $om, UserRepository $userRepository, CommissionableAssetRepository $assetRepository, UserAvailabilityRepository $userAvailabilityRepository, CommissionableAssetAvailabilityRepository $assetAvailabilityRepository)
    {
        $this->action = $action;
        $this->payload = $payload;
        $this->planningAgent = $planningAgent;
        $this->om = $om;
        $this->userRepository = $userRepository;
        $this->assetRepository = $assetRepository;
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->assetAvailabilityRepository = $assetAvailabilityRepository;
    }

    public function compute(): void
    {
        $this->validateAction();
        $this->validatePayload();

        if (isset($this->payload[self::PAYLOAD_USERS_KEY]) && !empty($this->payload[self::PAYLOAD_USERS_KEY])) {
            $this->process(User::class, UserAvailability::class, $this->payload[self::PAYLOAD_USERS_KEY]);
        }

        if (isset($this->payload[self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY]) && !empty($this->payload[self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY])) {
            $this->process(CommissionableAsset::class, CommissionableAssetAvailability::class, $this->payload[self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY]);
        }

        $this->om->flush();
    }

    protected function process(string $entityClass, string $availabilityClass, array $data): void
    {
        $entityRepository = $this->getOwnerRepository($entityClass);
        $availabilityRepository = $this->getAvailabilityRepository($entityClass);

        if (!$availabilityRepository instanceof AvailabilityRepositoryInterface) {
            throw new \LogicException('Bad entity name');
        }

        if (!$entityRepository instanceof AvailabilitableRepositoryInterface) {
            throw new \LogicException('Bad entity name');
        }

        list($startSearch, $endSearch) = $this->getSearchDates($data);

        $entities = $entityRepository->findByIds(array_keys($data));
        $availabilities = $availabilityRepository->findByOwnerAndDates($entities, new \DateTimeImmutable($startSearch), new \DateTimeImmutable($endSearch));

        foreach ($data as $entityId => $schedules) {
            $search = array_filter($entities, fn (AvailabilitableInterface $entity) => $entityId == $entity->getId());

            if (empty($search)) {
                throw new \InvalidArgumentException('Invalid entity');
            }

            /** @var AvailabilityInterface */
            $currentEntity = end($search);

            foreach ($schedules as $schedule) {
                list($scheduleStart, $scheduleEnd) = $schedule;
                $searchAvailabilities = array_filter($availabilities, function (AvailabilityInterface $availability) use ($entityId, $scheduleStart, $scheduleEnd) {
                    return $availability->getOwner()->getId() == $entityId && $availability->getStartTime() == new \DateTimeImmutable($scheduleStart) && $availability->getEndTime() == new \DateTimeImmutable($scheduleEnd);
                });
                $availability = end($searchAvailabilities);

                if (self::ACTION_DELETE == $this->action) {
                    if (!$availability) {
                        continue;
                    }

                    $this->om->remove($availability);
                } else {
                    $status = $this->getNewStatus($this->action);
                    if (!$availability) {
                        $availability = new $availabilityClass(null, $currentEntity, new \DateTimeImmutable($scheduleStart), new \DateTimeImmutable($scheduleEnd), $status);
                        $this->om->persist($availability);
                    }
                    switch ($status) {
                        case AvailabilityInterface::STATUS_BOOKED:
                            $availability->book($this->planningAgent);
                        break;
                        case AvailabilityInterface::STATUS_AVAILABLE:
                            $availability->declareAvailable();
                        break;
                        case AvailabilityInterface::STATUS_LOCKED:
                            $availability->lock();
                        break;
                    }
                }
            }
        }
    }

    protected function getNewStatus(string $action): string
    {
        switch ($action) {
            case self::ACTION_BOOK:
                return AvailabilityInterface::STATUS_BOOKED;
            case self::ACTION_ALLOW:
                return  AvailabilityInterface::STATUS_AVAILABLE;
            default:
                return AvailabilityInterface::STATUS_LOCKED;
        }
    }

    protected function getSearchDates(array $data): array
    {
        $firstUser = end($data);
        list($min, $max) = end($firstUser);
        foreach ($data as $schedules) {
            foreach ($schedules as list($start, $end)) {
                if ($start < $min) {
                    $min = $start;
                }

                if ($end > $max) {
                    $max = $end;
                }
            }
        }

        return [$min, $max];
    }

    protected function validateAction(): void
    {
        if (!in_array($this->action, self::ACTIONS)) {
            throw new \InvalidArgumentException('Invalid action : '.$this->action);
        }
    }

    protected function validatePayload(): void
    {
        $keys = array_keys($this->payload);
        for ($i = 0; $i < count($keys); ++$i) {
            if (!in_array($keys[$i], self::PAYLOAD_VALID_KEYS)) {
                throw new \InvalidArgumentException('Invalid key : '.$keys[$i]);
            }
        }

        foreach ($this->payload as $entityPayload) {
            foreach ($entityPayload as $entitySchedules) {
                foreach ($entitySchedules as list($start, $end)) {
                    try {
                        $start = new \DateTimeImmutable($start);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException('Invalid date : '.$start);
                    }

                    try {
                        $end = new \DateTimeImmutable($end);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException('Invalid date : '.$end);
                    }

                    if ($end <= $start) {
                        throw new \InvalidArgumentException('End date must be greater than start date');
                    }
                }
            }
        }
    }

    private function getOwnerRepository(string $class): ?AvailabilitableRepositoryInterface
    {
        return [
                User::class => $this->userRepository,
                CommissionableAsset::class => $this->assetRepository,
            ][$class] ?? null;
    }

    private function getAvailabilityRepository(string $class): ?AvailabilityRepositoryInterface
    {
        return [
                User::class => $this->userAvailabilityRepository,
                CommissionableAsset::class => $this->assetAvailabilityRepository,
            ][$class] ?? null;
    }
}
