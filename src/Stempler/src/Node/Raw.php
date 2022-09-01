<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Plain text or comment. Might contain inclusion of other syntaxes within it.
 *
 * @implements NodeInterface<Raw>
 */
final class Raw implements NodeInterface
{
    use ContextTrait;

    public function __construct(
        public string|int|float $content,
        Context $context = null
    ) {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
