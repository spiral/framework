<?php

declare(strict_types=1);

namespace Spiral\Views\Processor;

use Spiral\Views\ContextInterface;
use Spiral\Views\Exception\ContextException;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * Replaces all context values in a view source based on given pattern (by default @{name|default}).
 */
final class ContextProcessor implements ProcessorInterface
{
    // Context injection pattern @{key|default}
    private const PATTERN = '/@\\{(?P<name>[a-z0-9_\\.\\-]+)(?: *\\| *(?P<default>[^}]+))?}/i';

    private readonly string $pattern;

    public function __construct(string $pattern = null)
    {
        $this->pattern = $pattern ?? self::PATTERN;
    }

    public function process(ViewSource $source, ContextInterface $context): ViewSource
    {
        return $source->withCode(
            \preg_replace_callback(
                $this->pattern,
                static function (array $matches) use ($context) {
                    try {
                        return $context->resolveValue($matches['name']) ?? $matches['default'] ?? null;
                    } catch (ContextException $e) {
                        return $matches['default'] ?? throw $e;
                    }
                },
                $source->getCode()
            ),
        );
    }
}
