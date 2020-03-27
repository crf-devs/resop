<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\Processor\ProcessorInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestIdProcessor implements ProcessorInterface
{
    private const HEADER = 'X-Request-Id';

    private RequestStack $requestStack;
    private ?string $currentRequestId = null;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(array $record): array
    {
        if (null !== $requestId = $this->getCurrentRequestId()) {
            $record['extra']['request_id'] = $requestId;
        }

        if (isset($record['context']['correlation_id'])) {
            $record['extra']['request_id'] = $record['context']['correlation_id'];
        }

        return $record;
    }

    public function setCurrentRequestId(?string $currentRequestId): void
    {
        $this->currentRequestId = $currentRequestId;
    }

    public function getCurrentRequestId(): ?string
    {
        // Should be null for HTTP requests, but set for the consume command
        if (!empty($this->currentRequestId)) {
            return $this->currentRequestId;
        }

        try {
            return self::getRequestId($this->requestStack);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public static function getRequestId(RequestStack $requestStack): string
    {
        $request = $requestStack->getCurrentRequest();
        if (null === $request) {
            throw new \InvalidArgumentException('No request found');
        }

        if (!$request->headers->has(self::HEADER)) {
            $request->headers->set(self::HEADER, 'sf-'.\bin2hex(\random_bytes(20)));
        }

        return (string) $request->headers->get(self::HEADER);
    }
}
