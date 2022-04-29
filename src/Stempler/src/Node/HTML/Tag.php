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
 */
final class Tag implements NodeInterface, AttributedInterface
{
    use ContextTrait;
    use AttributeTrait;

    public bool $void = false;

    public Mixin|string|null $name = null;

    /** @var Attr[] */
    public array $attrs = [];

    /** @var NodeInterface[] */
    public array $nodes = [];

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield 'name' => $this->name;
        yield 'attrs' => $this->attrs;
        yield 'nodes' => $this->nodes;
    }
}
