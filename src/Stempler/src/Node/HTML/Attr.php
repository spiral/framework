<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\HTML;

use Spiral\Stempler\Node\Mixin;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * Represents single node/tag attribute and it's value.
 *
 * @implements NodeInterface<Attr>
 */
final class Attr implements NodeInterface
{
    use ContextTrait;

    public function __construct(
        public Mixin|string $name,
        public mixed $value,
        Context $context = null
    ) {
        $this->context = $context;
    }

    /**
     * @return \Generator<string, Mixin|Nil|string, mixed, void>
     *
     * @psalm-return \Generator<'name'|'value', Mixin|Nil|string, mixed, void>
     */
    public function getIterator(): \Generator
    {
        yield 'name' => $this->name;
        yield 'value' => $this->value;
    }
}
