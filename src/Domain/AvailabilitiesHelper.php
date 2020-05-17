<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\User;
use App\Repository\UserAvailabilityRepository;

class AvailabilitiesHelper
{
    protected UserAvailabilityRepository $userAvailabilityRepository;
    protected string $slotInterval;

    public function __construct(UserAvailabilityRepository $userAvailabilityRepository, string $slotInterval)
    {
        $this->userAvailabilityRepository = $userAvailabilityRepository;
        $this->slotInterval = $slotInterval;
    }

    public function getUserWeeklyAvailabilities(User $user, \DateTimeImmutable $start): iterable
    {
        $end = $start->add(new \DateInterval('P7D'));

        $availabilitiesDomain = AvailabilitiesDomain::generate(
            $start,
            $end,
            $this->slotInterval,
            $this->userAvailabilityRepository->findBetweenDates($user, $start, $end)
        );

        $currentDay = $start;
        $availabilitiesPerDay = [];
        while ($currentDay < $end) {
            $availabilitiesPerDay[$currentDay->format('Ymd')] = ['date' => $currentDay, 'availabilities' => []];
            $currentDay = $currentDay->add(new \DateInterval('P1D'));
        }

        foreach ($availabilitiesDomain->availabilityDomains as $availabilityDomain) {
            $availability = $availabilityDomain->availability;
            $currentDate = $availabilityDomain->date->format('Ymd');
            if (null === $availability) {
                continue;
            }

            $lastKey = array_key_last($availabilitiesPerDay[$currentDate]['availabilities']);
            $lastItem = null;
            if (null !== $lastKey) {
                $lastItem = $availabilitiesPerDay[$currentDate]['availabilities'][$lastKey];
            }

            if ($lastItem && $lastItem['comment'] === $availability->getComment() && $lastItem['status'] === $availability->getStatus()) {
                $availabilitiesPerDay[$currentDate]['availabilities'][$lastKey]['endTime'] = $availability->getEndTime();
            } else {
                $availabilitiesPerDay[$currentDate]['availabilities'][] = [
                    'comment' => $availability->getComment(),
                    'startTime' => $availability->getStartTime(),
                    'endTime' => $availability->getEndTime(),
                    'status' => $availability->getStatus(),
                ];
            }
        }

        return $availabilitiesPerDay;
    }
}
