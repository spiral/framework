<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Visitor;

use Spiral\Stempler\Node\Aggregate;
use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\Raw;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Flatten all block, template and aggregate blocks from the template and inject their content to the parent template.
 * The visitor also merged multiple raw nodes together.
 *
 * This visitor is required to accurately calculate element indent level.
 */
final class FlattenNodes implements VisitorInterface
{
    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx): void
    {
        if (!$node instanceof Tag) {
            return;
        }

        $flatten = [];
        foreach ($node->nodes as $child) {
            if ($child instanceof Block || $child instanceof Template || $child instanceof Aggregate) {
                foreach ($child->nodes as $childNode) {
                    $flatten[] = $childNode;
                }
                continue;
            }

            $flatten[] = $child;
        }

        $node->nodes = $this->mergeRaw($flatten);
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx): void
    {
    }

    /**
     * @param array $nodes
     * @return array
     */
    private function mergeRaw(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            if (
                $node instanceof Raw
                && isset($result[count($result) - 1])
                && $result[count($result) - 1] instanceof Raw
            ) {
                $result[count($result) - 1]->content .= $node->content;
                continue;
            }

            $result[] = $node;
        }

        return $result;
    }
}
