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

    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        $this->originalMessage = $message;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @internal
     */
    public static function createWithTrace(
        string $message,
        array $trace = [],
        ?\Throwable $previous = null,
    ): static {
        $class = static::class;
        // Merge traces
        if ($previous instanceof self) {
            $merge = $previous->containerTrace;
            if ($trace !== [] && $merge !== []) {
                // merge lat element of $traces with first element of $merge
                \array_push($trace[\count($trace) - 1], ...$merge[0]);
                unset($merge[0]);
            }

            $trace = \array_merge($trace, $merge);
            $message = "$message\n{$previous->originalMessage}";
            $class = $previous::class;
        }

        $result = $class::createStatic(
            $message . ($trace === [] ? '' : "\nResolving trace:\n" . Tracer::renderTraceList($trace)),
            $previous,
        );
        $result->originalMessage = $message;
        $result->containerTrace = $trace;
        return $result;
    }

    protected static function createStatic(string $message, ?\Throwable $previous): static
    {
        return new static($message, previous: $previous);
    }
}
