<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use Symfony\Bridge\Monolog\Logger;

class ErrorProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        if (($record['level'] ?? 0) >= Logger::ERROR && !isset($record['context']['error'])) {
            $record['context']['error'] = $record['message'] ?? '';
        }

        return $record;
    }
}
