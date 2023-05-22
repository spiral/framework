<?php

declare(strict_types=1);

namespace Spiral\Stempler\Node;

use Spiral\Stempler\Parser\Context;

/**
 * Defines an ability to represent AST node.
 *
 * @template TNode
 * @extends \IteratorAggregate<array-key, TNode[]>
 */
interface NodeInterface extends \IteratorAggregate
{
    public function getContext(): ?Context;
}
