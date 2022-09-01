<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Node\Traits\ContextTrait;
use Spiral\Stempler\Parser\Context;

/**
 * @implements NodeInterface<Inline>
 */
final class Inline implements NodeInterface
{
    use ContextTrait;

    public string $name;
    public mixed $value = null;

    public function __construct(Context $context = null)
    {
        $this->context = $context;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
