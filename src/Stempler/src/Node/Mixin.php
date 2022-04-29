<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Combines
 */
final class Mixin implements NodeInterface
{
    use ContextTrait;

    /**
     * @param NodeInterface[] $nodes
     */
    public function __construct(
        public array $nodes = [],
        Context $context = null
    ) {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
