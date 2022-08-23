<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Visitor;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\Inline;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Replace specific tag with blocks which can be replaced by child template or redefined
 * during the import.
 */
final class DefineBlocks implements VisitorInterface
{
    /** @var array */
    private $prefix = ['block:', 'define:', 'yield:', 'section:'];

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx)
    {
        if ($node instanceof Tag) {
            return $this->makeBlock($node);
        }

        if ($node instanceof Inline) {
            $block = new Block($node->name, $node->getContext());
            if ($node->value !== null) {
                $block->nodes[] = new Raw($node->value);
            }

            return $block;
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx): void
    {
    }

    /**
     * Tags like '<block:name></block:name>'.
     */
    private function makeBlock(Tag $node): ?NodeInterface
    {
        $name = null;
        foreach ($this->prefix as $prefix) {
            if (strpos($node->name, $prefix) === 0) {
                $name = substr($node->name, strlen($prefix));
                break;
            }
        }

        if ($name === null) {
            return null;
        }

        $block = new Block($name, $node->getContext());
        $block->nodes = $node->nodes;

        return $block;
    }
}
