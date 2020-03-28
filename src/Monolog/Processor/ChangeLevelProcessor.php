<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

class ChangeLevelProcessor implements ProcessorInterface
{
    private static array $debugMessages = [
        'Matched route',
        'Populated the TokenStorage with an anonymous Token',
        'Guard authentication successful',
    ];

    public function __invoke(array $record): array
    {
        foreach (self::$debugMessages as $message) {
            if (false === \strpos($record['message'] ?? '', $message)) {
                continue;
            }

            $record['level'] = Logger::DEBUG;
            $record['level_name'] = Logger::getLevelName(Logger::DEBUG);

            return $record;
        }

        return $record;
    }
}
