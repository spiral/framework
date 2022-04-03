<?php

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge\Inject;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Transform\BlockClaims;
use Spiral\Stempler\Transform\QuotedValue;
use Spiral\Stempler\Traverser;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Replaces blocks by name.
 */
final class InjectBlocks implements VisitorInterface
{
    public function __construct(
        private readonly BlockClaims $blocks
    ) {
    }

    public function enterNode(mixed $node, VisitorContext $ctx): mixed
    {
        return null;
    }

    public function leaveNode(mixed $node, VisitorContext $ctx): mixed
    {
        if (!$node instanceof Block || $node->name === null || !$this->blocks->has($node->name)) {
            return null;
        }

        $inject = $this->blocks->claim($node->name);

        if ($inject instanceof QuotedValue) {
            // exclude quotes
            $inject = $inject->trimValue();
        }

        // mount block:parent content
        if ($node->name !== 'parent') {
            $traverser = new Traverser();
            $traverser->addVisitor(new InjectBlocks(new BlockClaims([
                'parent' => $node->nodes,
            ])));

            $inject = $traverser->traverse($inject);
        }

        $node->nodes = $inject;

        return null;
    }
}
