<?php

declare(strict_types=1);

namespace App\Domain;

use App\Entity\AvailabilityInterface;
use App\Entity\CommissionableAsset;
use App\Entity\User;
use App\Form\Type\PlanningSearchType;
use App\Repository\AvailabilityRepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPlanningUtils
{
    private const DEFAULT_DISPLAYED_INTERVAL = 'P2D';

    public static function getFormFromRequest(FormFactoryInterface $formFactory, Request $request): FormInterface
    {
        if (!$request->query->has('from')) {
            $request->query->set('from', (new \DateTimeImmutable('now'))->format('Y-m-d\T00:00:00'));
        }

        if (!$request->query->has('to')) {
            $from = new \DateTimeImmutable($request->query->get('from', 'now'));
            $request->query->set('to', $from->add(new \DateInterval(self::DEFAULT_DISPLAYED_INTERVAL))->format('Y-m-d\T00:00:00'));
        }

        $form = $formFactory->createNamed('', PlanningSearchType::class, [], ['method' => 'GET', 'attr' => ['autocomplete' => 'off']]);
        $form->handleRequest($request);

        return $form;
    }

    public static function prepareAvailabilities(AvailabilityRepositoryInterface $repository, array $availabilitables, DatePeriodCalculator $periodCalculator): array
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

    public static function parseRawSlots(array $rawSlots): array
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

    public static function splitAvailabilities(SkillSetDomain $skillSetDomain, array $usersAvailabilities, array $assetsAvailabilities): array
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
        $importantSkills = $skillSetDomain->getImportantSkills();

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
