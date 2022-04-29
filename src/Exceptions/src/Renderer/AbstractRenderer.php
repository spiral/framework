<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\Verbosity;

/**
 * Provides common functionality for exception rendering.
 */
abstract class AbstractRenderer implements ExceptionRendererInterface
{
    /** @var non-empty-string[] Lower case format string */
    protected const FORMATS = [];
    public Verbosity $defaultVerbosity = Verbosity::BASIC;

    public function canRender(string $format): bool
    {
        return \in_array(\strtolower($format), static::FORMATS, true);
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

        if ($stacktrace[0] !== $header) {
            \array_unshift($stacktrace, $header);
        }

        return $stacktrace;
    }
}
