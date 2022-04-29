<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Parser\Context;

/**
 * Allow traversing but do not render.
 */
final class Hidden implements NodeInterface
{
    /**
     * @param NodeInterface[] $nodes
     */
    public function __construct(
        public array $nodes
    ) {
    }

    /**
     * @return \Generator|\Traversable
     */
    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }

    public function getContext(): ?Context
    {
        return null;
    }
}
