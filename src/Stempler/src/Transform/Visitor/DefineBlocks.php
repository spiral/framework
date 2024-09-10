<?php

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
    private array $prefix = ['block:', 'define:', 'yield:', 'section:'];

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
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

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    /**
     * Tags like '<block:name></block:name>'.
     */
    private function makeBlock(Tag $node): ?NodeInterface
    {
        $name = null;
        foreach ($this->prefix as $prefix) {
            if (\str_starts_with($node->name, (string) $prefix)) {
                $name = \substr($node->name, \strlen((string) $prefix));
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
