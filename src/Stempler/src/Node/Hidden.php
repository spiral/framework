<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Parser\Context;

/**
 * Allow traversing but do not render.
 */
final class Hidden implements NodeInterface
{
    /** @var NodeInterface[] */
    public $nodes;

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
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
