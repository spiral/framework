<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Spiral\Core\ScopeInterface;

/**
 * @internal The component is under development.
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
final class LogTracer extends AbstractTracer
{
    private array $context = [];

    public function __construct(
        ScopeInterface $scope,
        private readonly ClockInterface $clock,
        private readonly LoggerInterface $logger,
        private readonly UuidFactoryInterface $uuidFactory
    ) {
        parent::__construct($scope);
    }

    public function trace(
        string $name,
        callable $callback,
        array $attributes = [],
        bool $scoped = false,
        ?TraceKind $traceKind = null,
        ?int $startTime = null
    ): mixed {
        $span = new Span($name, $attributes);

        $this->context['telemetry'] = $this->uuidFactory->uuid4()->toString();

        $startTime ??= $this->clock->now();

        $result = $this->runScope($span, $callback);

        $elapsed = $this->clock->now() - $startTime;

        $this->logger->debug(\sprintf('Trace [%s] - [%01.4f ms.]', $name, $elapsed / 1_000_000), [
            'attributes' => $span->getAttributes(),
            'status' => $span->getStatus(),
            'scoped' => $scoped,
            'trace_kind' => $traceKind,
            'elapsed' => $elapsed,
            'id' => $this->context['telemetry'],
        ]);

        return $result;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
