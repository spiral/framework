<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

use Spiral\Core\Internal\Tracer;

/**
 * Exception that contains trace of resolving.
 */
class TracedContainerException extends ContainerException
{
    protected array $containerTrace = [];
    protected string $originalMessage = '';

    public function __construct(string $message = "", int $code = 0, ?\Throwable $previous = null)
    {
        $this->originalMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    public static function createWithTrace(
        string $message,
        array $traces,
        ?\Throwable $previous = null,
    ): static {
        $result = new static(
            $message . ($traces === [] ? '' : "\nResolving trace:\n" . Tracer::renderTraceList($traces)),
            previous: $previous,
        );
        $result->originalMessage = $message;
        $result->containerTrace = $traces;
        return $result;
    }

    public static function extendTracedException(string $message, array $traces, self $exception): static
    {
        $merge = $exception->containerTrace;
        if ($traces !== [] && $merge !== []) {
            // merge lat element of $traces with first element of $merge
            \array_push($traces[\count($traces) - 1], ...$merge[0]);
            unset($merge[0]);
        }

        $traces = \array_merge($traces, $merge);
        return static::createWithTrace("$message\n{$exception->originalMessage}", $traces, previous: $exception);
    }
}
