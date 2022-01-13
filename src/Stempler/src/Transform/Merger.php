<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Transform;

use DeepCopy\DeepCopy;
use Spiral\Stempler\Node\HTML\Tag;
use Spiral\Stempler\Node\NodeInterface;
use Spiral\Stempler\Node\Template;
use Spiral\Stempler\Transform\Finalizer\IsolateBlocks;
use Spiral\Stempler\Transform\Finalizer\IsolatePHPBlocks;
use Spiral\Stempler\Transform\Merge\Inject;
use Spiral\Stempler\Traverser;
use Spiral\Stempler\VisitorInterface;

/**
 * Merges two ASTs. Used by extend and import blocks. Called inside ResolveImports,
 * ExtendsParent visitors.
 */
final class Merger
{
    /** @var DeepCopy */
    private $deepCopy;

    /** @var BlockFetcher */
    private $fetcher;

    /**
     * Merger constructor.
     */
    public function __construct()
    {
        $this->deepCopy = new DeepCopy();
        $this->fetcher = new BlockFetcher();
    }

    public function getFetcher(): BlockFetcher
    {
        return $this->fetcher;
    }

    /**
     * Merge given template with array of blocks.
     */
    public function merge(Template $target, Tag $source): Template
    {
        $blocks = $this->fetcher->fetchBlocks($source);

        // to avoid issues caused by shared nodes
        $target = $this->deepCopy->copy($target);
        $target->setContext($source->getContext());

        $target->nodes = $this->traverse($target->nodes, new Inject\InjectBlocks($blocks));
        $target->nodes = $this->traverse($target->nodes, new Inject\InjectPHP($blocks));
        $target->nodes = $this->traverse($target->nodes, new Inject\InjectAttributes($blocks));

        return $target;
    }

    public function isolateNodes(Template $node, string $path): Template
    {
        $node->nodes = $this->traverse(
            $node->nodes,
            new IsolateBlocks($path),
            new IsolatePHPBlocks($path)
        );

        return $node;
    }

    /**
     * @return array|NodeInterface[]
     */
    protected function traverse(array $nodes, VisitorInterface ...$visitors)
    {
        $traverser = new Traverser();
        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        return $traverser->traverse($nodes);
    }
}
