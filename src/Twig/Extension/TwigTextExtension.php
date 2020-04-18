<?php

declare(strict_types=1);

namespace App\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigTextExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('truncate', [$this, 'truncate']),
        ];
    }

    public function truncate(?string $text, int $maxLen = 50, string $ellipsis = '...'): string
    {
        if (!\is_string($text)) {
            return '';
        }

        if (\strlen($text) <= $maxLen) {
            return $text;
        }

        return substr($text, 0, $maxLen - 3).$ellipsis;
    }
}
