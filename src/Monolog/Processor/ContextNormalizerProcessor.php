<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use App\Entity\UserSerializableInterface;
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
        if (!\is_array($record['context'])) {
            return $record;
        }

        foreach ($record['context'] as $key => $value) {
            if (\is_object($value) && method_exists($value, '__toString')) {
                $record['context'][$key] = $value->__toString();
            } elseif ($value instanceof UserSerializableInterface) {
                $record['context'][$key] = json_encode($value->userSerialize()); // TODO use a custom normalizer
            } elseif ($this->normalizer->supportsNormalization($value, 'json')) {
                try {
                    $record['context'][$key] = $this->normalizer->normalize($value, 'json', [
                        'circular_reference_handler' => static function ($object) {
                            if (!\is_object($object)) {
                                return null;
                            }
                            if (method_exists($object, '__toString')) {
                                return $object->__toString();
                            }
                            if (!empty($object->id)) {
                                return (string) $object->id;
                            }

                            return null;
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
