<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\Formatter\JsonFormatter;
use Monolog\Processor\ProcessorInterface;

class ExceptionProcessor implements ProcessorInterface
{
    public function __invoke(array $record)
    {
        if (isset($record['context']['exception']) && ($e = $record['context']['exception']) instanceof \Throwable) {
            $jsonFormatter = new JsonFormatter();
            $jsonFormatter->includeStacktraces();

            $formatedException = json_decode($jsonFormatter->format(['e' => $e]), true, 512, JSON_THROW_ON_ERROR);
            $formatedException['e']['trace_string'] = json_encode($formatedException['e']['trace'] ?? [], JSON_UNESCAPED_SLASHES + JSON_THROW_ON_ERROR);
            unset($formatedException['e']['trace']); // The default trace format is not compliant with kibana

            $record['context']['exception'] = $formatedException['e'];
        }

        return $record;
    }
}
