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

/** Payload example
 * {
 *     "users": {
 *         "1": [
 *             ["2020-03-20 12:00", "2020-03-20 14:00"]
 *         ],
 *         "2": [
 *             ["2020-03-21 12:00", "2020-03-20 14:00"]
 *         ]
 *     },
 *     "commissionableAssets": {
 *         "1": [
 *             ["2020-03-24 12:00", "2020-03-20 14:00"]
 *         ],
 *         "2": [
 *             ["2020-03-22 12:00", "2020-03-20 14:00"]
 *         ]
 *     }
 * }
 */
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
    public const PAYLOAD_COMMENT_KEY = 'comment';

    public const PAYLOAD_VALID_KEYS = [
        self::PAYLOAD_USERS_KEY,
        self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY,
        self::PAYLOAD_COMMENT_KEY,
    ];

    public const PAYLOAD_ENTITIES_KEYS = [
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

        $payloadAdditionalData = [
            self::PAYLOAD_COMMENT_KEY => $this->payload[self::PAYLOAD_COMMENT_KEY] ?? null ?: '',
        ];

        if (!empty($this->payload[self::PAYLOAD_USERS_KEY])) {
            $this->process(User::class, UserAvailability::class, $this->payload[self::PAYLOAD_USERS_KEY], $payloadAdditionalData);
        }

        if (!empty($this->payload[self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY])) {
            $this->process(CommissionableAsset::class, CommissionableAssetAvailability::class, $this->payload[self::PAYLOAD_COMMISSIONABLE_ASSETS_KEY], $payloadAdditionalData);
        }

        $this->om->flush();
    }

    protected function process(string $entityClass, string $availabilityClass, array $data, array $payloadAdditionalData): void
    {
        $entityRepository = $this->getOwnerRepository($entityClass);
        $availabilityRepository = $this->getAvailabilityRepository($entityClass);

        if (!$availabilityRepository instanceof AvailabilityRepositoryInterface) {
            throw new \LogicException('Bad entity name');
        }

        if (!$entityRepository instanceof AvailabilitableRepositoryInterface) {
            throw new \LogicException('Bad entity name');
        }

        [$startSearch, $endSearch] = $this->getSearchDates($data);

        $entities = $entityRepository->findByIds(array_keys($data));
        $availabilities = $availabilityRepository->findByOwnerAndDates($entities, new \DateTimeImmutable($startSearch), new \DateTimeImmutable($endSearch));

        foreach ($data as $entityId => $schedules) {
            $search = array_filter($entities, static fn (AvailabilitableInterface $entity) => (int) $entityId === $entity->getId());

            if (empty($search)) {
                throw new \InvalidArgumentException('Invalid entity');
            }

            /** @var AvailabilityInterface */
            $currentEntity = end($search);

            foreach ($schedules as [$scheduleStart, $scheduleEnd]) {
                $scheduleStartDate = new \DateTimeImmutable($scheduleStart);
                $scheduleEndDate = new \DateTimeImmutable($scheduleEnd);

                $searchAvailabilities = array_filter($availabilities, static function (AvailabilityInterface $availability) use ($entityId, $scheduleStartDate, $scheduleEndDate) {
                    // Caution: it's not possible to compare DateTimeImmutable with ====
                    return
                        $availability->getOwner()->getId() === (int) $entityId
                        && 0 === $availability->getStartTime()->getTimestamp() - $scheduleStartDate->getTimestamp()
                        && 0 === $availability->getEndTime()->getTimestamp() - $scheduleEndDate->getTimestamp();
                });
                /** @var AvailabilityInterface|false $availability */
                $availability = end($searchAvailabilities);

                // TODO Use workflow component
                if (self::ACTION_DELETE === $this->action) {
                    if (false === $availability) {
                        continue;
                    }

                    $this->om->remove($availability);
                } else {
                    $status = $this->getNewStatus($this->action);
                    if (false === $availability) {
                        $availability = new $availabilityClass(null, $currentEntity, new \DateTimeImmutable($scheduleStart), new \DateTimeImmutable($scheduleEnd));
                        $this->om->persist($availability);
                    }
                    switch ($status) {
                        case AvailabilityInterface::STATUS_BOOKED:
                            $availability->book($this->planningAgent, $payloadAdditionalData[self::PAYLOAD_COMMENT_KEY]);
                            break;
                        case AvailabilityInterface::STATUS_AVAILABLE:
                            $availability->declareAvailable($this->planningAgent);
                            break;
                        case AvailabilityInterface::STATUS_LOCKED:
                            $availability->lock($this->planningAgent, $payloadAdditionalData[self::PAYLOAD_COMMENT_KEY]);
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
        [$min, $max] = end($firstUser);
        foreach ($data as $schedules) {
            foreach ($schedules as [$start, $end]) {
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
        if (!\in_array($this->action, self::ACTIONS, true)) {
            throw new \InvalidArgumentException(sprintf('Invalid action : %s', $this->action));
        }
    }

    protected function validatePayload(): void
    {
        // TODO Use deserializer & validator
        $keys = array_keys($this->payload);
        foreach ($keys as $key) {
            if (!\in_array($key, self::PAYLOAD_VALID_KEYS, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid key : %s', $key));
            }
        }

        foreach ($this->payload as $key => $entityPayload) {
            if (!\in_array($key, self::PAYLOAD_ENTITIES_KEYS, true)) {
                continue;
            }

            foreach ($entityPayload as $entitySchedules) {
                foreach ($entitySchedules as [$start, $end]) {
                    try {
                        $start = new \DateTimeImmutable($start);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException(sprintf('Invalid date : %s', $start));
                    }

                    try {
                        $end = new \DateTimeImmutable($end);
                    } catch (\Exception $e) {
                        throw new \InvalidArgumentException(sprintf('Invalid date : %s', $end));
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
        return [User::class => $this->userRepository, CommissionableAsset::class => $this->assetRepository][$class] ?? null;
    }

    private function getAvailabilityRepository(string $class): ?AvailabilityRepositoryInterface
    {
        return [User::class => $this->userAvailabilityRepository, CommissionableAsset::class => $this->assetAvailabilityRepository][$class] ?? null;
    }
}
