<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform\Merge\Inject;

use Spiral\Stempler\Node\Block;
use Spiral\Stempler\Transform\BlockClaims;
use Spiral\Stempler\Transform\BlockFetcher;
use Spiral\Stempler\Transform\QuotedValue;
use Spiral\Stempler\Traverser;
use Spiral\Stempler\VisitorContext;
use Spiral\Stempler\VisitorInterface;

/**
 * Replaces blocks by name.
 */
final class InjectBlocks implements VisitorInterface
{
    /** @var BlockClaims */
    private $blocks;

    /** @var BlockFetcher */
    private $fetcher;

    public function __construct(BlockClaims $blocks)
    {
        $this->blocks = $blocks;
        $this->fetcher = new BlockFetcher();
    }

    /**
     * @inheritDoc
     */
    public function enterNode($node, VisitorContext $ctx): void
    {
        // nothing to do
    }

    /**
     * @inheritDoc
     */
    public function leaveNode($node, VisitorContext $ctx)
    {
        if (!$node instanceof Block || $node->name === null || !$this->blocks->has($node->name)) {
            return null;
        }

        $inject = $this->blocks->claim($node->name);

        if ($inject instanceof QuotedValue) {
            // exclude quotes
            $inject = $inject->trimvalue();
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
    }
}
