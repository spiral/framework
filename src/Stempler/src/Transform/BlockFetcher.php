<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\HTML\Attr;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\NodeInterface;

/**
 * Fetches block definitions from Tag blocks and their children.
 */
final class BlockFetcher
{
    private const CONTEXT_KEY = 'context';

    /**
     * Extract "value" blocks from the import caller. Block values are always array of nodes.
     */
    public function fetchBlocks(Tag $caller): BlockClaims
    {
        $blocks = [self::CONTEXT_KEY => []];

        foreach ($caller->attrs as $attr) {
            if (!$attr instanceof Attr || $attr->name instanceof NodeInterface) {
                // ignore name when attribute is dynamic
                $blocks[\sprintf('attr-%s', \count($blocks))] = $attr;
                continue;
            }

            \assert($attr->name !== self::CONTEXT_KEY);

            // to identify that we are dealing with possibly quoted value
            $blocks[$attr->name] = new QuotedValue($attr->value);
        }


        foreach ($caller->nodes as $node) {
            if ($node instanceof Block) {
                $blocks[$node->name] = $node->nodes;
            } else {
                \assert(
                    \is_array($blocks[self::CONTEXT_KEY]) || $blocks[self::CONTEXT_KEY] instanceof \ArrayAccess
                );

                $blocks[self::CONTEXT_KEY][] = $node;
            }
        }

        if ($blocks[self::CONTEXT_KEY] === []) {
            unset($blocks[self::CONTEXT_KEY]);
        }

        return new BlockClaims($blocks);
    }
}
