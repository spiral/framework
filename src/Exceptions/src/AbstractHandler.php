<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

/**
 * Provides common functionality for exception rendering.
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function getMessage(\Throwable $e): string
    {
        return sprintf('%s: %s in %s at line %s', get_class($e), $e->getMessage(), $e->getFile(), $e->getLine());
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
            array_unshift($stacktrace, $header);
        }

        return $stacktrace;
    }
}
