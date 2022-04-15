<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Renderer;

use Spiral\Exceptions\Verbosity;

final class JsonRenderer extends AbstractRenderer
{
    protected const FORMATS = ['application/json', 'json'];

    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = Verbosity::BASIC,
        string $format = null,
    ): string {
        $verbosity ??= $this->defaultVerbosity;
        return \json_encode([
            'error'      => \sprintf(
                '[%s] %s as %s:%s',
                $exception::class,
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ),
            'stacktrace' => \iterator_to_array($this->renderTrace($exception->getTrace(), $verbosity)),
        ]);
    }

    private function renderTrace(array $trace, Verbosity $verbosity): \Generator
    {
        foreach ($trace as $item) {
            $result = [];

            if (isset($item['class'])) {
                $result['function'] = \sprintf(
                    '%s%s%s()',
                    $item['class'],
                    $item['type'],
                    $item['function']
                );
            } else {
                $result['function'] = \sprintf(
                    '%s()',
                    $item['function']
                );
            }

            if ($verbosity->value >= Verbosity::VERBOSE->value && isset($item['file'])) {
                $result['at'] = [
                    'file' => $item['file'] ?? null,
                    'line' => $item['line'] ?? null,
                ];
            }

            yield $result;
        }
    }
}
