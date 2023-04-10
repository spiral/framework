<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\HTML;

use Spiral\Stempler\Node\AttributedInterface;
use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\AttributeTrait;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Tag or other control block.
 *
 * @implements NodeInterface<Tag>
 * @template TNode of NodeInterface
 */
final class Tag implements NodeInterface, AttributedInterface
{
    use ContextTrait;
    use AttributeTrait;

    public bool $void = false;

    public Mixin|string|null $name = null;

    /** @var Attr[] */
    public array $attrs = [];

    /**
     * @var list<TNode>
     */
    public array $nodes = [];

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    /**
     * @psalm-suppress ImplementedReturnTypeMismatch
     * @return \Generator<'attrs'|'name'|'nodes', Mixin|null|string|array<array-key, Attr>|array<array-key, TNode>, mixed, void>
     */
    public function getIterator(): \Generator
    {
        yield 'name' => $this->name;
        yield 'attrs' => $this->attrs;
        yield 'nodes' => $this->nodes;
    }
}
