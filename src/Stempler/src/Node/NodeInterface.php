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
 * Defines an ability to represent AST node.
 */
interface NodeInterface extends \IteratorAggregate
{
    public function getContext(): ?Context;
}
