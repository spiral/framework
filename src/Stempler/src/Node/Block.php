<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\AttributeTrait;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Blocks used to extend and import templates. Block operate as template variable.
 *
 * @implements NodeInterface<Block>
 * @template TNode of NodeInterface
 */
final class Block implements NodeInterface, AttributedInterface
{
    use ContextTrait;
    use AttributeTrait;

    /** @var list<TNode> */
    public array $nodes = [];

    public function __construct(
        public ?string $name,
        Context $context = null
    ) {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield 'nodes' => $this->nodes;
    }
}
