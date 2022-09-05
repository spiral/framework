<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node\HTML;

use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Parser\Context;

/**
 * Attribute without any value.
 *
 * @implements NodeInterface<Nil>
 */
final class Nil implements NodeInterface
{
    public function getContext(): ?Context
    {
        return null;
    }

    public function getIterator(): \Generator
    {
        yield from [];
    }
}
