<?php

declare(strict_types=1);

namespace App\Controller\User\Availability;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

trait UserAvailabityControllerTrait
{
    private function getDatesByWeek(?string $week): array
    {
        try {
            $start = new \DateTimeImmutable($week ?: 'monday this week');
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Bad week', $e);
        }

        $interval = $start->diff(new \DateTimeImmutable());
        // edit current week and next week only
        if ($interval->days > 6) {
            throw new BadRequestHttpException('Bad interval');
        }

        $end = $start->add(new \DateInterval('P7D'));

        return [$start, $end];
    }
}
