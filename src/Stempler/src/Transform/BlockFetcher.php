<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /**
     * Extract "value" blocks from the import caller. Block values are always array of nodes.
     */
    public function fetchBlocks(Tag $caller): BlockClaims
    {
        $blocks = ['context' => []];

        foreach ($caller->attrs as $attr) {
            if (!$attr instanceof Attr || $attr->name instanceof NodeInterface) {
                // ignore name when attribute is dynamic
                $blocks[sprintf('attr-%s', count($blocks))] = $attr;
                continue;
            }

            // to identify that we are dealing with possibly quoted value
            $blocks[$attr->name] = new QuotedValue($attr->value);
        }

        foreach ($caller->nodes as $node) {
            if ($node instanceof Block) {
                $blocks[$node->name] = $node->nodes;
            } else {
                $blocks['context'][] = $node;
            }
        }

        if ($blocks['context'] === []) {
            unset($blocks['context']);
        }

        return new BlockClaims($blocks);
    }
}
