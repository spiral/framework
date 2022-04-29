<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Plain text or comment. Might contain inclusion of other syntaxes within it.
 */
final class Raw implements NodeInterface
{
    use ContextTrait;

    public function __construct(
        public string $content,
        Context $context = null
    ) {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
