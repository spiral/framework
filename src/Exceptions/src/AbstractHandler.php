<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

/**
 * Provides common functionality for exception rendering.
 */
abstract class AbstractHandler implements HandlerInterface
{
    public function getMessage(\Throwable $e): string
    {
        return \sprintf('%s: %s in %s at line %s', $e::class, $e->getMessage(), $e->getFile(), $e->getLine());
    }

    /**
     * Normalized exception stacktrace.
     */
    protected function getStacktrace(\Throwable $e): array
    {
        $stacktrace = $e->getTrace();
        if (empty($stacktrace)) {
            return [];
        }

        //Let's let's clarify exception location
        $header = [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ] + $stacktrace[0];

        if ($stacktrace[0] != $header) {
            \array_unshift($stacktrace, $header);
        }

        return $stacktrace;
    }
}
