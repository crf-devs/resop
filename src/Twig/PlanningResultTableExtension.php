<?php

declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PlanningResultTableExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('renderPlanningTable', [$this, 'renderTable']),
        ];
    }

    public function renderTable(array $availabilities, bool $displayActions): string
    {
        $res = '';
        foreach ($availabilities as $slot) {
            $res .= sprintf(
                '<td class="slot-box %s" data-status="%s" data-day="%s" data-from="%s" data-to="%s">%s</td>',
                $slot['status'],
                $slot['status'],
                $slot['fromDay'],
                $slot['fromDate'],
                $slot['toDate'],
                $displayActions ? '<input type="checkbox">' : ''
            );
        }

        return $res;
    }
}
