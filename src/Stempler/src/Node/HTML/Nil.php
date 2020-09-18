<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node\HTML;

use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Parser\Context;

/**
 * Attribute without any value.
 */
final class Nil implements NodeInterface
{
    /**
     * @inheritDoc
     */
    public function getContext(): ?Context
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getIterator(): \Generator
    {
        yield from [];
    }
}
