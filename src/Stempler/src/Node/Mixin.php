<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Combines
 *
 * @implements NodeInterface<Mixin>
 * @template TNode of NodeInterface
 */
final class Mixin implements NodeInterface
{
    use ContextTrait;

    /**
     * @param TNode[] $nodes
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
