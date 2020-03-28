<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContextNormalizerProcessor implements ProcessorInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function __invoke(array $record): array
    {
        if (!is_array($record['context'])) {
            return $record;
        }

        foreach ($record['context'] as $key => $value) {
            if ($this->normalizer->supportsNormalization($value, 'json')) {
                try {
                    $record['context'][$key] = $this->normalizer->normalize($value, 'json', [
                        'circular_reference_handler' => static function ($object) {
                            return (string) $object;
                        },
                    ]);
                } catch (\Throwable $e) {
                    $record['context'][$key] = $e->getMessage();
                }
            }
        }

        return $record;
    }
}
