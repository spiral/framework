<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Views\Processor;

use Spiral\Views\ContextInterface;
use Spiral\Views\ProcessorInterface;
use Spiral\Views\ViewSource;

/**
 * Replaces all context values in a view source based on given pattern (by default @{name|default}).
 */
final class ContextProcessor implements ProcessorInterface
{
    // Context injection pattern @{key|default}
    private const PATTERN = '/@\\{(?P<name>[a-z0-9_\\.\\-]+)(?: *\\| *(?P<default>[^}]+))?}/i';

    /** @var string */
    private $pattern = '';

    /**
     * @param string $pattern
     */
    public function __construct(string $pattern = null)
    {
        $this->pattern = $pattern ?? static::PATTERN;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ViewSource $source, ContextInterface $context): ViewSource
    {
        return $source->withCode(preg_replace_callback($this->pattern, function ($matches) use ($source, $context) {
            return $context->resolveValue($matches[1]);
        }, $source->getCode()));
    }
}
