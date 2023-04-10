<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Parser\Context;

/**
 * Allow traversing but do not render.
 *
 * @implements NodeInterface<Hidden>
 * @template TNode of NodeInterface
 */
final class Hidden implements NodeInterface
{
    /** @param TNode[] $nodes */
    public function __construct(
        public array $nodes
    ) {
    }

    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }

    public function getContext(): ?Context
    {
        return null;
    }
}
