<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

final class JsonHandler extends AbstractHandler
{
    public function renderException(\Throwable $e, int $verbosity = self::VERBOSITY_VERBOSE): string
    {
        return json_encode([
            'error'      => sprintf(
                '[%s] %s as %s:%s',
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ),
            'stacktrace' => iterator_to_array($this->renderTrace($e->getTrace(), $verbosity)),
        ]);
    }

    private function renderTrace(array $trace, int $verbosity): \Generator
    {
        foreach ($trace as $item) {
            $result = [];

            if (isset($item['class'])) {
                $result['function'] = sprintf(
                    '%s%s%s()',
                    $item['class'],
                    $item['type'],
                    $item['function']
                );
            } else {
                $result['function'] = sprintf(
                    '%s()',
                    $item['function']
                );
            }

            if ($verbosity >= self::VERBOSITY_VERBOSE && isset($item['file'])) {
                $result['at'] = [
                    'file' => $item['file'] ?? null,
                    'line' => $item['line'] ?? null,
                ];
            }

            yield$result;
        }
    }
}
