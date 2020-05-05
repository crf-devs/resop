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

        $end = $start->add(new \DateInterval('P7D'));

        return [$start, $end];
    }
}
